<script setup lang="ts">
import { useColorMode } from '@vueuse/core'
import { computed } from 'vue'

interface Props {
  src: string
  overlayOpacity?: number
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  overlayOpacity: 0.55,
})

const mode = useColorMode()

const overlayStyle = computed(() => ({
  background:
    mode.value === 'dark'
      ? `rgba(0, 0, 0, ${props.overlayOpacity})`
      : `rgba(255, 255, 255, ${props.overlayOpacity + 0.15})`,
}))
</script>

<template>
  <div class="absolute inset-0 overflow-hidden" :class="props.class">
    <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
      <source :src="src" type="video/mp4" />
    </video>
    <div class="absolute inset-0 z-[1]" :style="overlayStyle" />
  </div>
</template>
