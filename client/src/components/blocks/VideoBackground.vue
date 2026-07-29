<script setup lang="ts">
import { useColorMode } from '@vueuse/core'
import { computed, ref, onMounted, onUnmounted } from 'vue'

interface Props {
  src: string
  poster?: string
  overlayOpacity?: number
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  overlayOpacity: 0.55,
  poster: undefined,
})

const mode = useColorMode()
const videoRef = ref<HTMLVideoElement | null>(null)
const isVisible = ref(false)
const shouldLoadVideo = ref(false)

const overlayStyle = computed(() => ({
  background:
    mode.value === 'dark'
      ? `rgba(0, 0, 0, ${props.overlayOpacity})`
      : `rgba(255, 255, 255, ${props.overlayOpacity + 0.15})`,
}))

// Check if connection is slow
const isSlowConnection = computed(() => {
  const connection = (navigator as any).connection
  return connection?.effectiveType === '2g' || connection?.effectiveType === '3g'
})

let observer: IntersectionObserver | null = null

function tryPlay() {
  if (!videoRef.value) return
  const playPromise = videoRef.value.play()
  if (playPromise !== undefined) {
    playPromise.catch(() => {
      // Autoplay was prevented — browser policy; silently ignore
    })
  }
}

onMounted(() => {
  if (isSlowConnection.value) return

  // When the video has enough data to play, trigger play if visible
  if (videoRef.value) {
    videoRef.value.addEventListener('canplay', () => {
      if (isVisible.value) tryPlay()
    })
  }

  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value = true
          shouldLoadVideo.value = true
          // Try to play; if video isn't ready yet, the canplay listener will handle it
          tryPlay()
        } else {
          isVisible.value = false
          videoRef.value?.pause()
        }
      })
    },
    { threshold: 0.1 },
  )

  if (videoRef.value) {
    observer.observe(videoRef.value)
  }
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<template>
  <div class="absolute inset-0 overflow-hidden" :class="props.class">
    <!-- Poster fallback shown on slow connections or before video loads -->
    <div
      v-if="isSlowConnection || !shouldLoadVideo"
      class="absolute inset-0 w-full h-full bg-cover bg-center"
      :style="poster ? { backgroundImage: `url(${poster})` } : {}"
    />

    <video
      v-if="!isSlowConnection"
      ref="videoRef"
      autoplay
      loop
      muted
      playsinline
      :poster="poster"
      :src="shouldLoadVideo ? src : undefined"
      class="absolute inset-0 w-full h-full object-cover"
    />

    <div class="absolute inset-0 z-[1]" :style="overlayStyle" />
  </div>
</template>
