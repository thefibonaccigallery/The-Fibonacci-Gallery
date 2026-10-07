<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
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
} from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { useVideoSecurity } from '@/composables/useVideoSecurity'
import { useVideoWatermark } from '@/composables/useVideoWatermark'

interface Props {
  video: CoursePlayerVideo | null
  canGoNext?: boolean
  hasNext?: boolean
}

interface Emits {
  (e: 'progress', payload: { watchTime: number; watchPercentage: number }): void
  (e: 'ended'): void
  (e: 'next'): void
}

const props = withDefaults(defineProps<Props>(), {
  canGoNext: false,
  hasNext: false,
})
const emit = defineEmits<Emits>()

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

  player = new Plyr(playerContainer.value, {
    controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
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
      showinfo: 0,
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

  const resumeAt = accumulatedSeconds
  const isCompleted = props.video?.progress?.is_completed === true
  if (resumeAt > 0 && !isCompleted) {
    const seekOnReady = () => {
      if (player) player.currentTime = resumeAt
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

  player.on('playing', handlePlaying)
  player.on('seeked', handleSeeked)
  player.on('pause', handlePause)
  player.on('ended', handleEnded)

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
  if (player) {
    player.currentTime = 0
    player.play()
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
</script>

<template>
  <div class="video-player-wrapper">
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
      <div
        ref="playerContainer"
        :class="{ 'opacity-0 pointer-events-none': isObscured }"
        :data-plyr-provider="'youtube'"
        :data-plyr-embed-id="activeYoutubeId"
      ></div>

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

/* Hide YouTube branding, title, and link elements as much as possible */
.video-player-wrapper :deep(.plyr__video-embed) {
  overflow: hidden !important;
  position: relative !important;
}

.video-player-wrapper :deep(.plyr__video-embed > .plyr__video-embed__container) {
  height: 100% !important;
  padding-bottom: 0 !important;
  position: relative !important;
  transform: none !important;
}

.video-player-wrapper :deep(.plyr__video-embed iframe) {
  /* Prevent pointer events from opening YouTube pages or showing info overlays on hover */
  pointer-events: none !important;
  /* Keep the embedded video aligned with the full player area in normal and fullscreen modes. */
  position: absolute !important;
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
  transform: none !important;
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
