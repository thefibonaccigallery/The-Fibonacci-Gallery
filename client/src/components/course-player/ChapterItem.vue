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

interface ChapterTheme {
  titleColor: string
}

const CHAPTER_THEMES: ChapterTheme[] = [
  { titleColor: 'text-emerald-500 dark:text-emerald-400' },
  { titleColor: 'text-cyan-500 dark:text-cyan-400' },
  { titleColor: 'text-violet-500 dark:text-violet-400' },
  { titleColor: 'text-amber-500 dark:text-amber-400' },
  { titleColor: 'text-rose-500 dark:text-rose-400' },
  { titleColor: 'text-indigo-500 dark:text-indigo-400' },
  { titleColor: 'text-teal-500 dark:text-teal-400' },
  { titleColor: 'text-fuchsia-500 dark:text-fuchsia-400' },
]

const chapterTheme = computed<ChapterTheme>(() => {
  const index = Math.abs(props.chapterIndex) % CHAPTER_THEMES.length
  return CHAPTER_THEMES[index] ?? CHAPTER_THEMES[0]!
})

const hasActiveVideo = computed(() => {
  return props.chapter.videos.some((v) => v.uuid === props.activeVideoId)
})

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
  <div
    :class="[
      'border border-border rounded-lg overflow-hidden transition-all',
      hasActiveVideo && 'ring-1 ring-primary/30',
    ]"
  >
    <!-- Chapter Header -->
    <button
      @click="toggleExpanded"
      :disabled="!isUnlocked"
      :class="[
        'w-full px-3.5 py-3 flex items-start gap-3 transition-colors',
        hasActiveVideo ? 'bg-muted/50' : 'bg-muted/25 hover:bg-muted/45',
        !isUnlocked && 'opacity-50 cursor-not-allowed',
      ]"
    >
      <!-- Expand Icon -->
      <PhCaretDown
        :size="18"
        :class="[
          'shrink-0 mt-0.5 transition-transform text-muted-foreground',
          isExpanded ? 'rotate-0' : '-rotate-90',
        ]"
      />

      <!-- Chapter Info -->
      <div class="flex-1 text-left min-w-0">
        <div class="flex items-start justify-between gap-2 mb-1">
          <!-- Bold Chapter Title with distinct colors -->
          <h3
            :class="[
              'font-bold text-sm leading-snug wrap-break-word',
              isUnlocked ? chapterTheme.titleColor : 'text-muted-foreground',
            ]"
          >
            {{ chapter.title }}
          </h3>

          <span
            v-if="chapter.level"
            :class="[
              'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider shrink-0 mt-0.5',
              getLevelColor(chapter.level),
            ]"
          >
            {{ chapter.level }}
          </span>
        </div>

        <!-- Completed progress -->
        <p class="text-xs text-muted-foreground font-medium">
          {{ completedCount }} / {{ totalCount }} completed
        </p>
      </div>

      <!-- Lock Icon -->
      <PhLock v-if="!isUnlocked" :size="18" class="shrink-0 mt-0.5 text-muted-foreground" />
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
