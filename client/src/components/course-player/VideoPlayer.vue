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
let lastProgressUpdate = 0

const initializePlayer = () => {
  if (!playerContainer.value || !props.video?.youtube_video_id) return

  // Clean up existing player
  if (player) {
    player.destroy()
  }

  // Create new player
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

  // Set up event listeners
  player.on('timeupdate', handleTimeUpdate)
  player.on('pause', handlePause)
  player.on('ended', handleEnded)

  // Start progress tracking interval (every 10 seconds)
  if (progressInterval) {
    clearInterval(progressInterval)
  }

  progressInterval = window.setInterval(() => {
    if (player && player.playing) {
      sendProgress()
    }
  }, 10000)
}

const handleTimeUpdate = () => {
  // This fires frequently, we'll use the interval for actual updates
}

const handlePause = () => {
  sendProgress()
}

const handleEnded = () => {
  sendProgress()
  emit('ended')
}

const sendProgress = () => {
  if (!player || !props.video) return

  const currentTime = Math.floor(player.currentTime)
  const duration = player.duration

  // Avoid sending duplicate updates
  if (currentTime === lastProgressUpdate) return
  lastProgressUpdate = currentTime

  if (duration > 0) {
    const watchPercentage = Math.min((currentTime / duration) * 100, 100)

    emit('progress', {
      watchTime: currentTime,
      watchPercentage: Math.round(watchPercentage * 100) / 100, // Round to 2 decimals
    })
  }
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
  () => props.video?.id,
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
