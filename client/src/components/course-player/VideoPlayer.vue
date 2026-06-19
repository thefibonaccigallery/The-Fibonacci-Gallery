<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import Plyr from 'plyr'
import 'plyr/dist/plyr.css'
import type { CoursePlayerVideo } from '@/types/CoursePlayerTypes'

interface Props {
  video: CoursePlayerVideo | null
}

interface Emits {
  (e: 'progress', payload: { watchTime: number; watchPercentage: number }): void
  (e: 'ended'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const playerContainer = ref<HTMLDivElement | null>(null)
let player: Plyr | null = null
let progressInterval: number | null = null

// Accumulated play time tracking — measures actual seconds the user spent watching,
// not the current playback position, so fast-forwarding doesn't inflate progress.
let accumulatedSeconds = 0          // total seconds genuinely watched
let sessionStartTime: number | null = null  // wall-clock ms when current play segment began
let lastSentWatchTime = -1          // dedup guard: avoid sending identical values

const initializePlayer = () => {
  if (!playerContainer.value || !props.video?.youtube_video_id) return

  if (player) {
    player.destroy()
  }

  player = new Plyr(playerContainer.value, {
    controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
    youtube: {
      noCookie: true,
      rel: 0,
      showinfo: 0,
      iv_load_policy: 3,
      modestbranding: 1,
    },
  })

  // Seed accumulated time from server so returning users keep their prior progress
  accumulatedSeconds = Math.floor(props.video?.progress?.watch_time_seconds || 0)
  sessionStartTime = null
  lastSentWatchTime = -1

  // Resume from last saved position (skip if the video was already completed — restart from beginning)
  const resumeAt = accumulatedSeconds
  const isCompleted = props.video?.progress?.is_completed === true
  if (resumeAt > 0 && !isCompleted) {
    const seekOnReady = () => {
      if (player) player.currentTime = resumeAt
      player?.off('ready', seekOnReady)
    }
    player.on('ready', seekOnReady)
  }

  player.on('playing', handlePlaying)
  player.on('seeked',  handleSeeked)
  player.on('pause',   handlePause)
  player.on('ended',   handleEnded)

  if (progressInterval) clearInterval(progressInterval)

  // Checkpoint every 10 s while playing: commit the current segment and send progress
  progressInterval = window.setInterval(() => {
    if (player && player.playing) {
      accumulateSession()
      sessionStartTime = Date.now()
      sendProgress()
    }
  }, 10000)
}

// Called when the video starts or resumes playing — begin timing this segment
const handlePlaying = () => {
  sessionStartTime = Date.now()
}

// Add the current segment's elapsed time to the accumulator (idempotent: safe to call twice)
const accumulateSession = () => {
  if (sessionStartTime !== null) {
    accumulatedSeconds += Math.floor((Date.now() - sessionStartTime) / 1000)
    sessionStartTime = null
  }
}

// Seek/scrub: close the current segment; if still playing, start a new one immediately
const handleSeeked = () => {
  accumulateSession()
  if (player?.playing) sessionStartTime = Date.now()
}

const handlePause = () => {
  accumulateSession()
  sendProgress()
}

const handleEnded = () => {
  accumulateSession()
  sendProgress()
  emit('ended')
}

// Emits actual accumulated watch time and a locally-computed percentage for immediate UI feedback.
// Uses the stored duration_seconds (same value the server uses) so the local percentage always
// matches the server-computed watch_percentage — prevents the controls and sidebar from diverging.
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
  if (progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
  }

  if (player) {
    player.destroy()
    player = null
  }
}

// Watch for video changes
watch(
  () => props.video?.uuid,
  () => {
    cleanup()
    if (props.video?.youtube_video_id) {
      // Small delay to ensure DOM is ready
      setTimeout(() => {
        initializePlayer()
      }, 100)
    }
  },
)

onMounted(() => {
  if (props.video?.youtube_video_id) {
    initializePlayer()
  }
})

onBeforeUnmount(() => {
  cleanup()
})
</script>

<template>
  <div class="video-player-wrapper">
    <div v-if="video?.youtube_video_id" class="aspect-video bg-black rounded-lg overflow-hidden">
      <div
        ref="playerContainer"
        :data-plyr-provider="'youtube'"
        :data-plyr-embed-id="video.youtube_video_id"
      ></div>
    </div>
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
}

.video-player-wrapper :deep(.plyr__video-embed iframe) {
  /* Prevent pointer events from opening YouTube pages or showing info overlays on hover */
  pointer-events: none !important;
  /* Scale height to 115% and offset top by -7.5% to crop the top title bar and bottom logo */
  /* This vertical-only crop preserves 100% of the horizontal width so no chart content is cut off */
  height: 115% !important;
  top: -7.5% !important;
}
</style>
