<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import Plyr from 'plyr'
import 'plyr/dist/plyr.css'
import type { CoursePlayerVideo } from '@/types/CoursePlayerTypes'
import axiosInstance from '@/api/axiosInstance'
import { PhSpinner } from '@phosphor-icons/vue'

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

const activeYoutubeId = ref<string | null>(null)
const isLoadingToken = ref<boolean>(false)

// Accumulated play time tracking
let accumulatedSeconds = 0          
let sessionStartTime: number | null = null  
let lastSentWatchTime = -1          

const decodeStreamToken = (token: string): string => {
  try {
    const decoded = atob(token)
    return decoded.replace(/[a-zA-Z]/g, (c) => {
      return String.fromCharCode(
        c.charCodeAt(0) + (c.toLowerCase() <= 'm' ? 13 : -13)
      )
    })
  } catch (e) {
    return ''
  }
}

const loadVideoToken = async () => {
  cleanup()
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
    youtube: {
      noCookie: true,
      rel: 0,
      showinfo: 0,
      iv_load_policy: 3,
      modestbranding: 1,
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

  player.on('playing', handlePlaying)
  player.on('seeked',  handleSeeked)
  player.on('pause',   handlePause)
  player.on('ended',   handleEnded)

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
  sessionStartTime = Date.now()
}

const accumulateSession = () => {
  if (sessionStartTime !== null) {
    accumulatedSeconds += Math.floor((Date.now() - sessionStartTime) / 1000)
    sessionStartTime = null
  }
}

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
    <div v-if="isLoadingToken" class="aspect-video bg-muted rounded-lg flex items-center justify-center text-primary">
      <PhSpinner class="h-8 w-8 animate-spin" />
    </div>
    <div v-else-if="activeYoutubeId" class="aspect-video bg-black rounded-lg overflow-hidden">
      <div
        ref="playerContainer"
        :data-plyr-provider="'youtube'"
        :data-plyr-embed-id="activeYoutubeId"
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
