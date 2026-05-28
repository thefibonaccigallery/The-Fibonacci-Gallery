<script setup lang="ts">
import { computed } from 'vue'
import { PhBook } from '@phosphor-icons/vue'
import ChapterItem from './ChapterItem.vue'
import type { CoursePlayerChapter } from '@/types/CoursePlayerTypes'

interface Props {
  chapters: CoursePlayerChapter[]
  activeVideoId: number | null
  isChapterUnlocked: (chapterIndex: number) => boolean
  isVideoUnlocked: (chapterIndex: number, videoIndex: number) => boolean
}

interface Emits {
  (e: 'selectVideo', payload: { videoId: number; chapterId: number }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const totalVideos = computed(() => {
  return props.chapters.reduce((sum, chapter) => sum + chapter.videos.length, 0)
})

const completedVideos = computed(() => {
  return props.chapters.reduce((sum, chapter) => {
    return sum + chapter.videos.filter((v) => v.progress?.is_completed === true).length
  }, 0)
})

const handleSelectVideo = (payload: { videoId: number; chapterId: number }) => {
  emit('selectVideo', payload)
}
</script>

<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="p-4 border-b border-border">
      <div class="flex items-center gap-2 mb-2">
        <PhBook :size="24" class="text-primary" />
        <h2 class="text-xl font-bold text-foreground">Course Content</h2>
      </div>
      <p class="text-sm text-muted-foreground">
        {{ completedVideos }} / {{ totalVideos }} videos completed
      </p>
    </div>

    <!-- Chapters List -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3">
      <ChapterItem
        v-for="(chapter, index) in chapters"
        :key="chapter.id"
        :chapter="chapter"
        :chapter-index="index"
        :is-unlocked="isChapterUnlocked(index)"
        :active-video-id="activeVideoId"
        :is-video-unlocked="isVideoUnlocked"
        @select-video="handleSelectVideo"
      />
    </div>
  </div>
</template>
