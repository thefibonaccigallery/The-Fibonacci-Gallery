<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

interface Props {
  text: string
  delay?: number
  charDelay?: number
  tag?: 'h1' | 'h2' | 'h3' | 'h4' | 'p' | 'span'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  delay: 200,
  charDelay: 30,
  tag: 'h1',
})

const isVisible = ref(false)

const lines = computed(() => props.text.split('\n'))

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true
  }, props.delay)
})
</script>

<template>
  <component :is="tag" :class="props.class">
    <span v-for="(line, lineIndex) in lines" :key="lineIndex" class="flex flex-wrap justify-center">
      <span
        v-for="(char, charIndex) in line.split('')"
        :key="`${lineIndex}-${charIndex}`"
        class="inline-block transition-all duration-500"
        :style="{
          opacity: isVisible ? 1 : 0,
          transform: isVisible ? 'translateX(0)' : 'translateX(-18px)',
          transitionDelay: `${lineIndex * line.length * charDelay + charIndex * charDelay}ms`,
        }"
        >{{ char === ' ' ? '\u00A0' : char }}</span
      >
    </span>
  </component>
</template>
