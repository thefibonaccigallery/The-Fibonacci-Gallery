<script setup lang="ts">
import { ref, computed } from 'vue'
import { PhCaretDown, PhLock } from '@phosphor-icons/vue'
import VideoItem from './VideoItem.vue'
import type { CoursePlayerChapter, CoursePlayerVideo } from '@/types/CoursePlayerTypes'

interface Props {
  chapter: CoursePlayerChapter
  chapterIndex: number
  isUnlocked: boolean
  activeVideoId: string | null
  isVideoUnlocked: (chapterIndex: number, videoIndex: number) => boolean
  isVideoInCooldown: (video: CoursePlayerVideo) => boolean
  getCooldownLabel: (video: CoursePlayerVideo) => string
}

interface Emits {
  (e: 'selectVideo', payload: { videoUuid: string; chapterUuid: string }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isExpanded = ref(true)

const completedCount = computed(() => {
  return props.chapter.videos.filter((v) => v.progress?.is_completed === true).length
})

const totalCount = computed(() => {
  return props.chapter.videos.length
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

const toggleExpanded = () => {
  if (props.isUnlocked) {
    isExpanded.value = !isExpanded.value
  }
}

const handleSelectVideo = (videoUuid: string) => {
  emit('selectVideo', { videoUuid, chapterUuid: props.chapter.uuid })
}
</script>

<template>
  <div class="border border-border rounded-lg overflow-hidden">
    <!-- Chapter Header -->
    <button
      @click="toggleExpanded"
      :disabled="!isUnlocked"
      :class="[
        'w-full px-4 py-4 flex items-center gap-3 transition-colors',
        'bg-muted/30 hover:bg-muted/50',
        !isUnlocked && 'opacity-50 cursor-not-allowed',
      ]"
    >
      <!-- Expand Icon -->
      <PhCaretDown
        :size="20"
        :class="[
          'flex-shrink-0 transition-transform text-muted-foreground',
          isExpanded ? 'rotate-0' : '-rotate-90',
        ]"
      />

      <!-- Chapter Info -->
      <div class="flex-1 text-left min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <h3 class="font-semibold text-foreground truncate">{{ chapter.title }}</h3>
          <span
            v-if="chapter.level"
            :class="[
              'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize flex-shrink-0',
              getLevelColor(chapter.level),
            ]"
          >
            {{ chapter.level }}
          </span>
        </div>
        <p class="text-sm text-muted-foreground">
          {{ completedCount }} / {{ totalCount }} completed
        </p>
      </div>

      <!-- Lock Icon -->
      <PhLock v-if="!isUnlocked" :size="20" class="flex-shrink-0 text-muted-foreground" />
    </button>

    <!-- Videos List -->
    <div v-if="isExpanded && isUnlocked" class="p-2 space-y-1 bg-background">
      <VideoItem
        v-for="(video, videoIndex) in chapter.videos"
        :key="video.uuid"
        :video="video"
        :is-unlocked="isVideoUnlocked(chapterIndex, videoIndex)"
        :is-active="video.uuid === activeVideoId"
        :is-in-cooldown="isVideoInCooldown(video)"
        :cooldown-label="getCooldownLabel(video)"
        @select="handleSelectVideo(video.uuid)"
      />
    </div>
  </div>
</template>
