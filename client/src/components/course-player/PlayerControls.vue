<script setup lang="ts">
import { computed } from 'vue'
import { PhSkipBack, PhSkipForward } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import type { CoursePlayerVideo, CoursePlayerChapter } from '@/types/CoursePlayerTypes'

interface Props {
  video: CoursePlayerVideo | null
  chapter: CoursePlayerChapter | null
  canGoNext: boolean
  canGoPrevious: boolean
  nextDisabledReason?: string
  currentWatchPercentage?: number
}

interface Emits {
  (e: 'next'): void
  (e: 'previous'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Use the highest of local (live) and server-side percentage so the bar
// updates immediately as the video plays, not only after a server refetch.
const watchPercentage = computed(() => {
  const serverPct = props.video?.progress?.watch_percentage || 0
  return Math.max(props.currentWatchPercentage ?? 0, serverPct)
})

const getLevelColor = (level: string | null) => {
  if (!level) return 'bg-muted text-muted-foreground'
  const colors: Record<string, string> = {
    beginner: 'bg-green-500/10 text-green-600 dark:text-green-400',
    intermediate: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    advanced: 'bg-red-500/10 text-red-600 dark:text-red-400',
  }
  return colors[level] || 'bg-muted text-muted-foreground'
}

const formatDuration = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60

  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${minutes}:${secs.toString().padStart(2, '0')}`
}
</script>

<template>
  <div v-if="video" class="space-y-4">
    <!-- Video Info -->
    <div>
      <div class="flex items-center gap-3 mb-2">
        <h2 class="text-2xl font-bold text-foreground">{{ video.title }}</h2>
        <span
          v-if="video.level"
          :class="[
            'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize',
            getLevelColor(video.level),
          ]"
        >
          {{ video.level }}
        </span>
        <span
          v-if="video.is_big_five || video.isBigFive"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20"
        >
          Big 5
        </span>
      </div>

      <div class="flex items-center gap-4 text-sm text-muted-foreground">
        <span v-if="chapter">{{ chapter.title }}</span>
        <span>{{ formatDuration(video.duration_seconds) }}</span>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="space-y-2">
      <div class="flex items-center justify-between text-sm">
        <span class="text-muted-foreground">Your Progress</span>
        <span class="font-medium text-foreground">{{ watchPercentage.toFixed(0) }}%</span>
      </div>
      <div class="h-2 bg-muted rounded-full overflow-hidden">
        <div
          class="h-full bg-primary transition-all duration-300"
          :style="{ width: `${watchPercentage}%` }"
        ></div>
      </div>
      <p v-if="video.progress?.is_completed || watchPercentage >= 100" class="text-xs text-primary">
        ✓ Video completed!
      </p>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex gap-3">
      <Button
        @click="emit('previous')"
        :disabled="!canGoPrevious"
        variant="outline"
        class="flex-1 gap-2"
      >
        <PhSkipBack :size="20" />
        Previous
      </Button>
      <Button
        @click="emit('next')"
        :disabled="!canGoNext"
        class="flex-1 gap-2"
        :title="nextDisabledReason"
      >
        Next
        <PhSkipForward :size="20" />
      </Button>
    </div>
  </div>
</template>
