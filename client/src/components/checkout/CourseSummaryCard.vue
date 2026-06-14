<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { PhSpinner } from '@phosphor-icons/vue'

interface Course {
  uuid: string
  title: string
  level: string
  price: number | string
  thumbnail_url: string | null
}

defineProps<{
  course: Course
  loading?: boolean
}>()

const emit = defineEmits<{
  next: []
}>()

const getLevelColor = (level: string) => {
  switch (level.toLowerCase()) {
    case 'beginner':
      return 'text-green-500 border-green-500/30 bg-green-500/10'
    case 'intermediate':
      return 'text-yellow-500 border-yellow-500/30 bg-yellow-500/10'
    case 'advanced':
      return 'text-red-500 border-red-500/30 bg-red-500/10'
    default:
      return 'text-muted-foreground border-border/30 bg-secondary/50'
  }
}
</script>

<template>
  <div class="animate-fade-in-up liquid-glass rounded-2xl p-6 border border-border/20">
    <h2 class="text-xl font-bold text-foreground mb-6">Course Summary</h2>

    <div class="space-y-6">
      <!-- Course Thumbnail -->
      <div
        class="relative w-full h-48 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center"
      >
        <img
          v-if="course.thumbnail_url"
          :src="course.thumbnail_url"
          :alt="course.title"
          class="w-full h-full object-cover"
        />
        <svg
          v-else
          class="absolute inset-0 w-full h-full opacity-30"
          viewBox="0 0 100 100"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <circle
            cx="50"
            cy="50"
            r="40"
            stroke="rgba(34, 197, 94, 0.1)"
            stroke-width="0.5"
            stroke-dasharray="2 2"
          />
          <polygon
            points="50,10 90,90 10,90"
            stroke="var(--primary)"
            stroke-width="0.5"
            fill="none"
          />
          <circle cx="50" cy="63.3" r="26.7" stroke="var(--primary)" stroke-width="0.5" fill="none" />
        </svg>
      </div>

      <!-- Course Info -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold text-foreground">{{ course.title }}</h3>
          <div
            :class="['px-3 py-1 rounded-lg text-xs font-bold border capitalize', getLevelColor(course.level)]"
          >
            {{ course.level }}
          </div>
        </div>

        <!-- Price -->
        <div class="pt-4 border-t border-border/20">
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Course Price</span>
            <span class="text-2xl font-bold text-primary">${{ Number(course.price).toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <!-- Pay Button -->
      <Button
        @click="emit('next')"
        :disabled="loading"
        class="w-full text-sm font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground flex items-center justify-center gap-2"
      >
        <PhSpinner v-if="loading" class="h-4 w-4 animate-spin" />
        Pay with USDT (TRC20)
      </Button>
    </div>
  </div>
</template>
