<script setup lang="ts">
import { computed } from 'vue'
import { PhLock, PhCheckCircle, PhPlay } from '@phosphor-icons/vue'
import type { CoursePlayerVideo } from '@/types/CoursePlayerTypes'

interface Props {
  video: CoursePlayerVideo
  isUnlocked: boolean
  isActive: boolean
}

interface Emits {
  (e: 'select'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isCompleted = computed(() => props.video.progress?.is_completed === true)

const formatDuration = (seconds: number) => {
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${minutes}:${secs.toString().padStart(2, '0')}`
}

const handleClick = () => {
  if (props.isUnlocked) {
    emit('select')
  }
}
</script>

<template>
  <button
    @click="handleClick"
    :disabled="!isUnlocked"
    :class="[
      'w-full text-left px-4 py-3 rounded-lg transition-all',
      'flex items-center gap-3',
      isActive
        ? 'bg-primary/10 border-2 border-primary'
        : 'hover:bg-muted/50 border-2 border-transparent',
      !isUnlocked && 'opacity-50 cursor-not-allowed',
    ]"
  >
    <!-- Status Icon -->
    <div class="flex-shrink-0">
      <PhCheckCircle
        v-if="isCompleted"
        :size="20"
        weight="fill"
        class="text-primary"
      />
      <PhPlay
        v-else-if="isActive"
        :size="20"
        weight="fill"
        class="text-primary"
      />
      <PhLock
        v-else-if="!isUnlocked"
        :size="20"
        class="text-muted-foreground"
      />
      <div
        v-else
        class="w-5 h-5 rounded-full border-2 border-muted-foreground"
      ></div>
    </div>

    <!-- Video Info -->
    <div class="flex-1 min-w-0">
      <p
        :class="[
          'font-medium truncate',
          isActive ? 'text-primary' : 'text-foreground',
        ]"
      >
        {{ video.title }}
      </p>
      <div class="flex items-center gap-2 text-xs text-muted-foreground mt-1">
        <span>{{ formatDuration(video.duration_seconds) }}</span>
        <span v-if="video.progress && !isCompleted">
          • {{ video.progress.watch_percentage.toFixed(0) }}%
        </span>
      </div>
    </div>
  </button>
</template>
