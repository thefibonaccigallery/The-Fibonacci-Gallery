<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import Plyr from 'plyr'
import 'plyr/dist/plyr.css'
import type { CoursePlayerVideo } from '@/types/CoursePlayerTypes'
import axiosInstance from '@/api/axiosInstance'
import {
  PhSpinner,
  PhArrowCounterClockwise,
  PhArrowRight,
  PhWarning,
  PhCheckCircle,
  PhCertificate,
} from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { useVideoSecurity } from '@/composables/useVideoSecurity'
import { useVideoWatermark } from '@/composables/useVideoWatermark'

interface Props {
  video: CoursePlayerVideo | null
  canGoNext?: boolean
  hasNext?: boolean
  isCourseCompleted?: boolean
  certificate?: {
    uuid: string
    certificate_number: string
    course_title?: string
    recipient_name?: string
    issued_at?: string
    verify_url?: string
  } | null
}

interface Emits {
  (e: 'progress', payload: { watchTime: number; watchPercentage: number }): void
  (e: 'ended'): void
  (e: 'next'): void
}

const props = withDefaults(defineProps<Props>(), {
  canGoNext: false,
  hasNext: false,
  isCourseCompleted: false,
  certificate: null,
})
const emit = defineEmits<Emits>()
const router = useRouter()

const playerContainer = ref<HTMLDivElement | null>(null)
const playerWrapper = ref<HTMLDivElement | null>(null)
let player: Plyr | null = null
let progressInterval: number | null = null

const activeYoutubeId = ref<string | null>(null)

// ── Security composables ────────────────────────────────────────────────────
useVideoWatermark(playerWrapper)
const { isObscured, dismissObscured } = useVideoSecurity({
  onDevToolsDetected: () => player?.pause(),
  onSecurityTriggered: () => player?.pause(),
})
// ───────────────────────────────────────────────────────────────────────────
const isLoadingToken = ref<boolean>(false)
const isBuffering = ref<boolean>(false)
const isEnded = ref<boolean>(false)

// Countdown / Auto-advance state
const countdownSeconds = ref(0)
const isCountingDown = ref(false)
const isPaused = ref(false)
let countdownTimer: number | null = null
let captionKillTimer: number | null = null

// Accumulated play time tracking
let accumulatedSeconds = 0
let sessionStartTime: number | null = null
let lastSentWatchTime = -1

const decodeStreamToken = (token: string): string => {
  try {
    const decoded = atob(token)
    return decoded.replace(/[a-zA-Z]/g, (c) => {
      return String.fromCharCode(c.charCodeAt(0) + (c.toLowerCase() <= 'm' ? 13 : -13))
    })
  } catch (e) {
    return ''
  }
}

const loadVideoToken = async () => {
  cleanup()
  isEnded.value = false
  isBuffering.value = true
  activeYoutubeId.value = null
  if (!props.video?.uuid) return

  isLoadingToken.value = true
  try {
    const response = await axiosInstance.get(`/videos/${props.video.uuid}/token`)
    if (response.data?.stream_token) {
      activeYoutubeId.value = decodeStreamToken(response.data.stream_token)
    }
  } catch (err) {
    console.error('Failed to load video token:', err)
  } finally {
    isLoadingToken.value = false
    if (activeYoutubeId.value) {
      setTimeout(() => {
        initializePlayer()
      }, 100)
    }
  }
}

const initializePlayer = () => {
  if (!playerContainer.value || !activeYoutubeId.value) return

  if (player) {
    player.destroy()
  }

  isBuffering.value = true

  player = new Plyr(playerContainer.value, {
    controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
    settings: [],
    // Keep controls visible for 5 s before hiding — same delay YouTube uses —
    // so Plyr's bar covers the YouTube watermark the whole time controls are shown.
    hideControls: true,
    captions: {
      active: false,
      update: false,
    },
    storage: {
      enabled: false,
    },
    youtube: {
      noCookie: true,
      rel: 0,
      iv_load_policy: 3,
      cc_load_policy: 0,
      cc: 0,
      hl: 'en',
      cc_lang_pref: 'none',
      modestbranding: 1,
      controls: 0,
      disablekb: 1,
      fs: 0,
      playsinline: 1,
    },
  })

  accumulatedSeconds = Math.floor(props.video?.progress?.watch_time_seconds || 0)
  sessionStartTime = null
  lastSentWatchTime = -1

  const isCompleted = props.video?.progress?.is_completed === true
  const duration = props.video?.duration_seconds || 0
  const resumeAt = isCompleted || (duration > 0 && accumulatedSeconds >= duration - 2) ? 0 : accumulatedSeconds

  if (resumeAt > 0) {
    const seekOnReady = () => {
      if (player) {
        player.currentTime = resumeAt
      }
      player?.off('ready', seekOnReady)
    }
    player.on('ready', seekOnReady)
  }

  // Programmatically disable YouTube captions via the IFrame API.
  // CSS cannot reach inside the iframe, so this is the only reliable way.
  // YouTube often re-enables captions from account prefs, so we poll aggressively.
  const disableYouTubeCaptions = () => {
    const yt = (player as any)?.embed
    if (!yt) return
    if (typeof yt.unloadModule === 'function') {
      yt.unloadModule('captions')
      yt.unloadModule('cc')
    }
    // Also try to set subtitle track to empty / off
    if (typeof yt.setOption === 'function') {
      try {
        yt.setOption('captions', 'track', {})
        yt.setOption('cc', 'track', {})
      } catch (_) {
        // silently ignore if the module isn't loaded
      }
    }
  }

  player.on('ready', () => {
    // Release buffer overlay once player is ready for interaction
    isBuffering.value = false

    // Attach native YouTube player state listener to accurately track buffering transitions
    const yt = (player as any)?.embed
    if (yt && typeof yt.addEventListener === 'function') {
      try {
        yt.addEventListener('onStateChange', (event: any) => {
          // -1: unstarted, 0: ended, 1: playing, 2: paused, 3: buffering, 5: cued
          if (event.data === 3) {
            isBuffering.value = true
          } else if (
            event.data === 1 ||
            event.data === 2 ||
            event.data === 0 ||
            event.data === 5 ||
            event.data === -1
          ) {
            isBuffering.value = false
          }
        })
      } catch (_) {
        // silently ignore if embed API is restricted
      }
    }

    // Fire immediately and keep retrying for 30 seconds (every 2s).
    // YouTube can lazily load caption modules well after the player is "ready".
    disableYouTubeCaptions()
    let attempts = 0
    captionKillTimer = window.setInterval(() => {
      disableYouTubeCaptions()
      attempts++
      if (attempts >= 15) {
        if (captionKillTimer !== null) {
          clearInterval(captionKillTimer)
          captionKillTimer = null
        }
      }
    }, 2000)
  })

  player.on('play', () => {
    isBuffering.value = true
  })
  player.on('waiting', () => {
    isBuffering.value = true
  })
  player.on('seeking', () => {
    isBuffering.value = true
  })
  player.on('seeked', () => {
    isBuffering.value = false
    handleSeeked()
  })
  player.on('playing', () => {
    isBuffering.value = false
    handlePlaying()
  })
  player.on('pause', () => {
    isBuffering.value = false
    handlePause()
  })
  player.on('ended', () => {
    isBuffering.value = false
    handleEnded()
  })

  if (progressInterval) clearInterval(progressInterval)

  progressInterval = window.setInterval(() => {
    if (player && player.playing) {
      accumulateSession()
      sessionStartTime = Date.now()
      sendProgress()
    }
  }, 10000)
}

const handlePlaying = () => {
  isEnded.value = false
  stopCountdown()
  sessionStartTime = Date.now()
}

const accumulateSession = () => {
  if (sessionStartTime !== null) {
    accumulatedSeconds += Math.floor((Date.now() - sessionStartTime) / 1000)
    sessionStartTime = null
  }
}

const handleSeeked = () => {
  isEnded.value = false
  stopCountdown()
  accumulateSession()
  if (player?.playing) sessionStartTime = Date.now()
}

const handlePause = () => {
  accumulateSession()
  sendProgress()
}

const startCountdown = () => {
  stopCountdown()
  const isBigFive = props.video?.is_big_five || props.video?.isBigFive
  countdownSeconds.value = isBigFive ? 10 : 5
  isCountingDown.value = true
  isPaused.value = false

  countdownTimer = window.setInterval(() => {
    if (isPaused.value) return
    countdownSeconds.value--
    if (countdownSeconds.value <= 0) {
      stopCountdown()
      emit('next')
    }
  }, 1000)
}

const stopCountdown = () => {
  if (countdownTimer !== null) {
    clearInterval(countdownTimer)
    countdownTimer = null
  }
  isCountingDown.value = false
}

const toggleCountdownPause = () => {
  isPaused.value = !isPaused.value
}

const handleNextClick = () => {
  stopCountdown()
  emit('next')
}

const handleEnded = () => {
  accumulateSession()
  sendProgress()
  isEnded.value = true
  emit('ended')

  if (props.hasNext && props.canGoNext) {
    startCountdown()
  }
}

const handleReplay = () => {
  stopCountdown()
  isEnded.value = false
  isBuffering.value = false
  if (player) {
    try {
      const yt = (player as any)?.embed
      if (yt && typeof yt.seekTo === 'function') {
        yt.seekTo(0, true)
      }
      player.currentTime = 0
      player.play()
    } catch (e) {
      console.error('Failed to replay video:', e)
    }
  }
}

const restart = () => {
  stopCountdown()
  isEnded.value = false
  isBuffering.value = false
  if (player) {
    try {
      const yt = (player as any)?.embed
      if (yt && typeof yt.seekTo === 'function') {
        yt.seekTo(0, true)
      }
      player.currentTime = 0
      player.play()
    } catch (e) {
      console.error('Failed to restart video:', e)
    }
  } else {
    loadVideoToken()
  }
}

const sendProgress = () => {
  if (!player || !props.video) return
  const duration = props.video.duration_seconds
  if (duration <= 0) return
  if (accumulatedSeconds === lastSentWatchTime) return
  lastSentWatchTime = accumulatedSeconds

  const watchPercentage = Math.min((accumulatedSeconds / duration) * 100, 100)
  emit('progress', {
    watchTime: accumulatedSeconds,
    watchPercentage: Math.round(watchPercentage * 100) / 100,
  })
}

const cleanup = () => {
  stopCountdown()
  isEnded.value = false
  isPaused.value = false
  isBuffering.value = false
  if (progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
  }

  // Clear caption-kill polling timer
  if (captionKillTimer !== null) {
    clearInterval(captionKillTimer)
    captionKillTimer = null
  }

  if (player) {
    player.destroy()
    player = null
  }
}

watch([() => props.canGoNext, () => props.hasNext], ([canGoNext, hasNext]) => {
  if (isEnded.value && canGoNext && hasNext && !isCountingDown.value && countdownTimer === null) {
    startCountdown()
  }
})

watch(
  () => props.video?.uuid,
  () => {
    loadVideoToken()
  },
)

onMounted(() => {
  if (props.video?.uuid) {
    loadVideoToken()
  }
})

onBeforeUnmount(() => {
  cleanup()
})

defineExpose({
  restart,
  loadVideoToken,
})
</script>

<template>
  <div class="video-player-wrapper" :class="{ 'is-buffering': isBuffering }">
    <!-- Loading state -->
    <div
      v-if="isLoadingToken"
      class="aspect-video bg-muted rounded-lg flex items-center justify-center text-primary"
    >
      <PhSpinner class="h-8 w-8 animate-spin" />
    </div>

    <!-- Active player: right-click disabled on the entire wrapper -->
    <div
      v-else-if="activeYoutubeId"
      ref="playerWrapper"
      class="aspect-video bg-black rounded-lg overflow-hidden relative"
      @contextmenu.prevent
    >
      <!-- Transparent overlay: blocks right-click directly on the iframe surface.
           pointer-events: none keeps Plyr's own controls fully clickable. -->
      <div class="absolute inset-0 z-10" style="pointer-events: none" @contextmenu.prevent />

      <!-- Plyr player container -->
      <!-- data-plyr-provider and data-plyr-embed-id let Plyr build and own the iframe -->
      <div
        ref="playerContainer"
        :class="{ 'opacity-0 pointer-events-none': isObscured }"
        data-plyr-provider="youtube"
        :data-plyr-embed-id="activeYoutubeId"
      ></div>



      <!-- Startup / Buffering Overlay (Hides YouTube native title, spinner, and bottom-right logo) -->
      <Transition
        enter-active-class="transition-opacity duration-150 ease-out"
        leave-active-class="transition-opacity duration-300 ease-in"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isBuffering"
          class="absolute inset-0 z-20 bg-black flex flex-col items-center justify-center pointer-events-none select-none"
        >
          <div class="flex flex-col items-center gap-3">
            <PhSpinner class="h-9 w-9 text-primary animate-spin" />
            <p
              v-if="video?.title"
              class="text-xs text-zinc-400 font-medium tracking-wide max-w-xs text-center truncate px-4"
            >
              {{ video.title }}
            </p>
          </div>
        </div>
      </Transition>

      <!-- Anti-Snipping / Window Blur shield (defends against Win+Shift+S, Snipping Tool, screen capture) -->
      <div
        v-show="isObscured"
        class="absolute inset-0 z-50 bg-black flex flex-col items-center justify-center p-4 text-center select-none cursor-pointer"
        @click="dismissObscured"
      >
        <div class="space-y-2 pointer-events-none">
          <div
            class="w-10 h-10 rounded-full bg-red-500/20 border border-red-500/40 flex items-center justify-center text-red-400 mx-auto"
          >
            <PhWarning :size="22" weight="bold" />
          </div>
          <p class="text-white font-semibold text-sm sm:text-base tracking-wide">
            Playback Protected
          </p>
          <p class="text-xs text-zinc-400 max-w-xs">
            Screen capture and shortcuts are prohibited. Click anywhere to resume.
          </p>
        </div>
      </div>

      <!-- Completion & Important Notice Overlay -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        leave-active-class="transition-all duration-200 ease-in"
        enter-from-class="opacity-0 scale-95"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="isEnded"
          class="absolute inset-0 z-30 bg-black/90 sm:bg-black/85 backdrop-blur-md flex flex-col items-center justify-center p-3 sm:p-5 md:p-6 text-foreground overflow-y-auto"
        >
          <div
            class="my-auto w-full max-w-xl mx-auto flex flex-col items-center text-center space-y-2.5 sm:space-y-3.5"
          >
            <!-- Big 5 Important Notice Variant -->
            <template v-if="video?.is_big_five || video?.isBigFive">
              <div
                class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/40 text-xs sm:text-sm font-bold tracking-wide shadow-lg shadow-amber-500/10"
              >
                <PhWarning :size="16" weight="fill" class="text-amber-400 shrink-0" />
                <span>⚠️ IMPORTANT NOTICE</span>
              </div>

              <div
                class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 sm:p-4 text-center space-y-1.5 sm:space-y-2 backdrop-blur-sm"
              >
                <h3
                  class="font-bold text-amber-500 dark:text-amber-400 text-xs sm:text-sm md:text-base leading-snug"
                >
                  Do not start live trading before completing all 5 parts of the Big 5 Forex Course.
                </h3>
                <p class="text-[11px] sm:text-xs md:text-sm text-foreground/90 leading-relaxed">
                  Each part covers essential knowledge you need to understand the Forex market,
                  analyze trades, manage risk, and develop a proper trading plan.
                </p>
                <p
                  class="text-[11px] sm:text-xs md:text-sm font-semibold text-foreground leading-relaxed pt-0.5"
                >
                  Finish the Big 5. Build your knowledge, backtest the strategy. Then trade
                  responsibly.
                </p>
              </div>
            </template>

            <!-- Course Completed & Certificate Variant -->
            <template v-else-if="!hasNext && (isCourseCompleted || certificate)">
              <div
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-amber-500/20 border-2 border-amber-500/50 flex items-center justify-center text-amber-400 shadow-[0_0_30px_rgba(245,158,11,0.3)] animate-pulse"
              >
                <PhCertificate :size="28" weight="fill" />
              </div>
              <div class="space-y-1.5 max-w-md">
                <h3 class="text-base sm:text-xl font-bold text-foreground">
                  🎓 Masterclass Completed!
                </h3>
                <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed">
                  Congratulations! You have completed all lessons. Your official Certificate of Completion has been generated and emailed to you.
                </p>
                <div
                  v-if="certificate?.certificate_number"
                  class="text-[11px] font-mono text-amber-400 bg-amber-500/10 border border-amber-500/30 rounded-md py-1 px-3 inline-block mt-1"
                >
                  Credential ID: {{ certificate.certificate_number }}
                </div>
              </div>
            </template>

            <!-- Standard video completion Variant -->
            <template v-else>
              <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary/20 border border-primary/40 flex items-center justify-center text-primary"
              >
                <PhCheckCircle :size="24" class="sm:hidden" weight="fill" />
                <PhCheckCircle :size="28" class="hidden sm:block" weight="fill" />
              </div>
              <div class="space-y-1">
                <h3 class="text-sm sm:text-base md:text-lg font-bold text-foreground">
                  Lesson Completed!
                </h3>
                <p class="text-xs sm:text-sm text-muted-foreground">
                  {{
                    hasNext && canGoNext
                      ? 'Great job! Ready for the next lesson.'
                      : 'You have completed this video.'
                  }}
                </p>
              </div>
            </template>

            <!-- Countdown / Auto-advance info -->
            <div
              v-if="hasNext && canGoNext && isCountingDown"
              class="flex items-center gap-2 text-xs sm:text-sm text-muted-foreground bg-muted/50 backdrop-blur-sm px-3 py-1 rounded-full border border-border/50"
            >
              <div class="w-2 h-2 rounded-full bg-primary animate-ping"></div>
              <span>
                Next video starting in
                <strong class="text-foreground font-semibold">{{ countdownSeconds }}s</strong>
              </span>
              <button
                type="button"
                @click="toggleCountdownPause"
                class="ml-1 text-xs text-amber-500 hover:text-amber-400 underline font-medium cursor-pointer"
              >
                {{ isPaused ? 'Resume' : 'Pause' }}
              </button>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-center gap-2 sm:gap-3 flex-wrap pt-1">
              <Button
                v-if="hasNext && canGoNext"
                @click="handleNextClick"
                class="gap-1.5 sm:gap-2 text-xs sm:text-sm h-8 sm:h-9 px-3 sm:px-4 font-semibold shadow-md"
                size="sm"
              >
                <span>Next Video</span>
                <PhArrowRight :size="15" weight="bold" />
              </Button>

              <Button
                v-if="certificate || (!hasNext && isCourseCompleted)"
                @click="router.push('/certificates')"
                class="gap-1.5 sm:gap-2 text-xs sm:text-sm h-8 sm:h-9 px-3 sm:px-4 font-bold bg-amber-500 hover:bg-amber-600 text-black shadow-md cursor-pointer"
                size="sm"
              >
                <PhCertificate :size="15" weight="bold" />
                <span>View Certificate</span>
              </Button>

              <Button
                @click="handleReplay"
                variant="outline"
                size="sm"
                class="gap-1.5 sm:gap-2 text-xs sm:text-sm h-8 sm:h-9 px-3 sm:px-4"
              >
                <PhArrowCounterClockwise :size="15" weight="bold" />
                <span>Replay</span>
              </Button>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- No video state -->
    <div
      v-else
      class="aspect-video bg-muted rounded-lg flex items-center justify-center text-muted-foreground"
    >
      <p>No video available</p>
    </div>
  </div>
</template>

<style scoped>
.video-player-wrapper :deep(.plyr) {
  --plyr-color-main: var(--color-primary);
}

.video-player-wrapper :deep(.plyr__control--overlaid) {
  background: var(--color-primary);
}

.video-player-wrapper :deep(.plyr__control:hover) {
  background: var(--color-primary);
}

.video-player-wrapper :deep(.plyr--video) {
  height: 100%;
}

/* Hide YouTube branding, title, and link elements as much as possible.
   The core trick: the iframe is made slightly wider than its container and
   shifted so the right edge (where the YouTube logo lives) is clipped away
   by the overflow:hidden on .plyr__video-embed. The video content stays
   fully visible because the logo only occupies the very bottom-right corner. */
.video-player-wrapper :deep(.plyr__video-embed) {
  overflow: hidden !important;
  position: relative !important;
}

.video-player-wrapper :deep(.plyr__video-embed > .plyr__video-embed__container) {
  height: 100% !important;
  padding-bottom: 0 !important;
  position: relative !important;
  transform: none !important;
  overflow: hidden !important;
}

/* Keep YouTube iframe hidden with opacity 0 until playback is actively streaming,
   preventing the YouTube title, spinner, and watermark/logo from being visible */
.video-player-wrapper.is-buffering :deep(.plyr__video-embed iframe),
.video-player-wrapper :deep(.plyr--loading .plyr__video-embed iframe) {
  opacity: 0 !important;
}

.video-player-wrapper :deep(.plyr__video-embed iframe) {
  /* Prevent pointer events from opening YouTube pages or showing info overlays on hover */
  pointer-events: none !important;
  /* Oversize iframe slightly: push the right edge (YouTube logo area) outside the
     overflow:hidden clip. The video itself is centered so no content is cropped. */
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  /* 105% width + a 2.5% right-side bleed; logo sits in the bottom ~2% of the right edge */
  width: 100% !important;
  height: 100% !important;
  transform: none !important;
  transition: opacity 0.25s ease-in-out;
}



/* Custom platform loader inside Plyr wrapper (works across both normal and fullscreen modes) */
.video-player-wrapper.is-buffering :deep(.plyr__video-wrapper::before),
.video-player-wrapper :deep(.plyr--loading .plyr__video-wrapper::before) {
  content: '';
  position: absolute;
  inset: 0;
  background-color: #000000;
  z-index: 2;
  pointer-events: none;
}

.video-player-wrapper.is-buffering :deep(.plyr__video-wrapper::after),
.video-player-wrapper :deep(.plyr--loading .plyr__video-wrapper::after) {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 40px;
  height: 40px;
  margin: -20px 0 0 -20px;
  border: 3px solid rgba(255, 255, 255, 0.15);
  border-top-color: var(--color-primary, #10b981);
  border-radius: 50%;
  animation: plyr-buffering-spin 0.8s linear infinite;
  z-index: 3;
  pointer-events: none;
}

@keyframes plyr-buffering-spin {
  to {
    transform: rotate(360deg);
  }
}

/* Solid black controls bar \u2014 fully opaque so it blocks the YouTube watermark
   that sits just above it when the bar is visible. */
.video-player-wrapper :deep(.plyr--video .plyr__controls) {
  background: rgba(0, 0, 0, 1) !important;
  /* Show instantly when mouse enters */
  transition: opacity 0.3s ease 0s, transform 0.3s ease 0s !important;
}

/* Delay the hide by 5 s after Plyr adds .plyr--hide-controls (mouse leaves),
   keeping the bar — and its black background — covering the YouTube logo. */
.video-player-wrapper :deep(.plyr--video.plyr--hide-controls .plyr__controls) {
  transition: opacity 0.3s ease 5s, transform 0.3s ease 5s !important;
}

/* Hide YouTube captions / subtitles that may render over the embed */
.video-player-wrapper :deep(.plyr__captions),
.video-player-wrapper :deep(.plyr__caption) {
  display: none !important;
}

@media print {
  .video-player-wrapper {
    display: none !important;
  }
}
</style>
