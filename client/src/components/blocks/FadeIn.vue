<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Props {
  delay?: number
  duration?: number
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  delay: 800,
  duration: 800,
})

const isVisible = ref(false)

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true
  }, props.delay)
})
</script>

<template>
  <div
    :class="props.class"
    :style="{
      opacity: isVisible ? 1 : 0,
      transition: `opacity ${duration}ms ease-out`,
    }"
  >
    <slot />
  </div>
</template>
