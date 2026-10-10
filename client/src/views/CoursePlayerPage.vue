<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { PhArrowLeft, PhList, PhX } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { useCoursePlayer } from '@/composables/useCoursePlayer'
import VideoPlayer from '@/components/course-player/VideoPlayer.vue'
import PlayerControls from '@/components/course-player/PlayerControls.vue'
import CourseCurriculum from '@/components/course-player/CourseCurriculum.vue'

const route = useRoute()
const router = useRouter()

const courseUuid = computed(() => route.params.courseUuid as string)
const isCurriculumOpen = ref(false)

const {
  course,
  chapters,
  activeVideo,
  activeChapter,
  activeVideoId,
  isLoading,
  setActiveVideo,
  isVideoUnlocked,
  isChapterUnlocked,
  getNextVideo,
  getPreviousVideo,
  canGoNext,
  canGoPrevious,
  currentWatchPercentage,
  reportProgress,
  initializePlayer,
  isVideoInCooldown,
  getCooldownLabel,
} = useCoursePlayer(courseUuid.value)

// Navigation
const nextVideo = computed(() => getNextVideo())
const previousVideo = computed(() => getPreviousVideo())

const nextDisabledReason = computed(() => {
  if (!nextVideo.value) return 'No more videos'
  return ''
})

const handleNext = () => {
  if (nextVideo.value && canGoNext.value) {
    setActiveVideo(nextVideo.value.video.uuid, nextVideo.value.chapter.uuid)
    isCurriculumOpen.value = false
  }
}

const handlePrevious = () => {
  if (previousVideo.value) {
    setActiveVideo(previousVideo.value.video.uuid, previousVideo.value.chapter.uuid)
    isCurriculumOpen.value = false
  }
}

const handleSelectVideo = (payload: { videoUuid: string; chapterUuid: string }) => {
  setActiveVideo(payload.videoUuid, payload.chapterUuid)
  isCurriculumOpen.value = false
}

const handleProgress = (payload: { watchTime: number; watchPercentage: number }) => {
  if (!activeVideoId.value) return
  reportProgress(activeVideoId.value, payload.watchTime, payload.watchPercentage)
}

const handleVideoEnded = () => {
  // Video player overlay displays the completion state / notice and handles auto-advance
}

const goBack = () => {
  router.push('/dashboard')
}

// Initialize player when data is loaded
watch(
  () => chapters.value.length,
  (newLength) => {
    if (newLength > 0 && !activeVideoId.value) {
      initializePlayer()
    }
  },
)

onMounted(() => {
  if (chapters.value.length > 0) {
    initializePlayer()
  }
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"
        ></div>
        <p class="text-muted-foreground">Loading course...</p>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="flex flex-col lg:flex-row h-screen">
      <!-- Desktop Sidebar - Left Panel -->
      <aside class="hidden lg:block lg:w-96 xl:w-104 shrink-0 border-r border-border bg-background overflow-hidden">
        <CourseCurriculum
          :chapters="chapters"
          :active-video-id="activeVideoId"
          :is-chapter-unlocked="isChapterUnlocked"
          :is-video-unlocked="isVideoUnlocked"
          :is-video-in-cooldown="isVideoInCooldown"
          :get-cooldown-label="getCooldownLabel"
          @select-video="handleSelectVideo"
        />
      </aside>

      <!-- Main Player Area -->
      <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <header
          class="border-b border-border bg-background px-4 py-3 flex items-center justify-between"
        >
          <div class="flex items-center gap-3">
            <Button @click="goBack" variant="ghost" size="sm" class="gap-2">
              <PhArrowLeft :size="20" />
              <span class="hidden sm:inline">Back</span>
            </Button>
            <div class="min-w-0">
              <h1 class="font-bold text-foreground truncate">{{ course?.title }}</h1>
            </div>
          </div>

          <!-- Mobile Menu Toggle -->
          <Button
            @click="isCurriculumOpen = !isCurriculumOpen"
            variant="outline"
            size="sm"
            class="lg:hidden gap-2"
          >
            <PhList :size="20" />
            Curriculum
          </Button>
        </header>

        <!-- Video Player and Controls -->
        <div class="flex-1 overflow-y-auto">
          <div class="max-w-6xl mx-auto p-4 space-y-6">
            <!-- Video Player -->
            <div class="animate-fade-in">
              <VideoPlayer
                :video="activeVideo"
                :can-go-next="canGoNext"
                :has-next="!!nextVideo"
                @progress="handleProgress"
                @ended="handleVideoEnded"
                @next="handleNext"
              />
            </div>

            <!-- Player Controls -->
            <div class="liquid-glass rounded-xl p-6 animate-fade-in-up">
              <PlayerControls
                :video="activeVideo"
                :chapter="activeChapter"
                :can-go-next="canGoNext"
                :can-go-previous="canGoPrevious"
                :next-disabled-reason="nextDisabledReason"
                :current-watch-percentage="currentWatchPercentage"
                @next="handleNext"
                @previous="handlePrevious"
              />
            </div>
          </div>
        </div>
      </main>

      <!-- Mobile Curriculum Drawer -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition-opacity duration-300"
          leave-active-class="transition-opacity duration-300"
          enter-from-class="opacity-0"
          leave-to-class="opacity-0"
        >
          <div
            v-if="isCurriculumOpen"
            @click="isCurriculumOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
          ></div>
        </Transition>

        <Transition
          enter-active-class="transition-transform duration-300"
          leave-active-class="transition-transform duration-300"
          enter-from-class="translate-x-full"
          leave-to-class="translate-x-full"
        >
          <div
            v-if="isCurriculumOpen"
            class="fixed right-0 top-0 bottom-0 w-full sm:w-96 bg-background z-50 lg:hidden shadow-xl"
          >
            <!-- Mobile Drawer Header -->
            <div class="flex items-center justify-between p-4 border-b border-border">
              <h2 class="text-lg font-bold text-foreground">Course Content</h2>
              <Button @click="isCurriculumOpen = false" variant="ghost" size="sm">
                <PhX :size="24" />
              </Button>
            </div>

            <!-- Mobile Curriculum Content -->
            <div class="h-[calc(100%-4rem)] overflow-hidden">
              <CourseCurriculum
                :chapters="chapters"
                :active-video-id="activeVideoId"
                :is-chapter-unlocked="isChapterUnlocked"
                :is-video-unlocked="isVideoUnlocked"
                :is-video-in-cooldown="isVideoInCooldown"
                :get-cooldown-label="getCooldownLabel"
                @select-video="handleSelectVideo"
              />
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>
