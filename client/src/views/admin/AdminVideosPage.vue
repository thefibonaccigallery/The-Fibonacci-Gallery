<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useVideos } from '@/composables/admin/useVideos'
import { useChapters } from '@/composables/admin/useChapters'
import { useCourses } from '@/composables/admin/useCourses'
import {
  PhVideo,
  PhPlus,
  PhPencil,
  PhTrash,
  PhEye,
  PhEyeSlash,
  PhLock,
  PhLockOpen,
  PhPlay,
} from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog.vue'
import AdminVideoPreviewModal from '@/components/admin/shared/AdminVideoPreviewModal.vue'
import type { VideoType } from '@/types/VideoTypes'

const router = useRouter()
const { fetchVideos, deleteVideo, isDeleting } = useVideos()
const { fetchChapters } = useChapters()
const { fetchCourses } = useCourses()

const page = ref(1)
const search = ref('')
const selectedCourseId = ref<number | undefined>(undefined)
const selectedChapterId = ref<number | undefined>(undefined)
const filterPublished = ref<boolean | undefined>(undefined)

// Delete confirmation dialog state
const deleteDialogOpen = ref(false)
const videoToDelete = ref<{ id: number; title: string } | null>(null)

// Preview modal state
const previewOpen = ref(false)
const previewVideo = ref<VideoType | null>(null)

const handlePreview = (video: VideoType) => {
  previewVideo.value = video
  previewOpen.value = true
}

// Fetch courses for filter dropdown
const { data: coursesData } = useQuery({
  queryKey: ['courses-list'],
  queryFn: () => fetchCourses({ per_page: 100 }),
})

const courses = computed(() => coursesData.value?.data || [])

// Fetch chapters for filter dropdown
const { data: chaptersData } = useQuery({
  queryKey: ['chapters-list', selectedCourseId],
  queryFn: () => fetchChapters({ per_page: 100, course_id: selectedCourseId.value }),
})

const chapters = computed(() => chaptersData.value?.data || [])

// Fetch videos
const {
  data: videosData,
  isLoading,
  refetch,
} = useQuery({
  queryKey: ['videos', page, search, selectedCourseId, selectedChapterId, filterPublished],
  queryFn: () =>
    fetchVideos({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
      course_id: selectedCourseId.value,
      chapter_id: selectedChapterId.value,
      is_published: filterPublished.value,
    }),
})

const videos = computed(() => videosData.value?.data || [])
const pagination = computed(() => ({
  current: videosData.value?.current_page || 1,
  last: videosData.value?.last_page || 1,
  total: videosData.value?.total || 0,
}))

const handleDelete = (id: number, title: string) => {
  videoToDelete.value = { id, title }
  deleteDialogOpen.value = true
}

const confirmDelete = () => {
  if (videoToDelete.value) {
    deleteVideo(videoToDelete.value.id, {
      onSuccess: () => {
        refetch()
        deleteDialogOpen.value = false
        videoToDelete.value = null
      },
    })
  }
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

const getLevelColor = (level: string | null) => {
  if (!level) return 'bg-muted text-muted-foreground'
  const colors: Record<string, string> = {
    beginner: 'bg-green-500/10 text-green-600 dark:text-green-400',
    intermediate: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    advanced: 'bg-red-500/10 text-red-600 dark:text-red-400',
  }
  return colors[level] || 'bg-muted text-muted-foreground'
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-foreground">Videos</h1>
        <p class="text-muted-foreground mt-1">Manage course videos</p>
      </div>
      <Button @click="router.push('/admin/videos/create')" class="gap-2">
        <PhPlus :size="20" />
        Create Video
      </Button>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 space-y-4">
      <div class="flex flex-col sm:flex-row gap-4">
        <Input v-model="search" placeholder="Search videos..." class="flex-1" @input="page = 1" />
        <select
          v-model="selectedCourseId"
          @change="page = 1; selectedChapterId = undefined"
          class="px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary min-w-50"
        >
          <option :value="undefined">All Courses</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
        <select
          v-model="selectedChapterId"
          @change="page = 1"
          class="px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary min-w-50"
        >
          <option :value="undefined">All Chapters</option>
          <option v-for="chapter in chapters" :key="chapter.id" :value="chapter.id">
            {{ chapter.course?.title }} - {{ chapter.title }}
          </option>
        </select>
      </div>

      <div class="flex gap-2">
        <Button
          :variant="filterPublished === undefined ? 'default' : 'outline'"
          @click="((filterPublished = undefined), (page = 1))"
          size="sm"
        >
          All
        </Button>
        <Button
          :variant="filterPublished === true ? 'default' : 'outline'"
          @click="((filterPublished = true), (page = 1))"
          size="sm"
        >
          Published
        </Button>
        <Button
          :variant="filterPublished === false ? 'default' : 'outline'"
          @click="((filterPublished = false), (page = 1))"
          size="sm"
        >
          Draft
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading videos...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="videos.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhVideo :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">No videos found</h3>
      <p class="text-muted-foreground mb-6">
        {{
          selectedChapterId || selectedCourseId
            ? 'No videos found with the selected filters'
            : 'Get started by creating your first video'
        }}
      </p>
      <Button @click="router.push('/admin/videos/create')">
        <PhPlus :size="20" class="mr-2" />
        Create Video
      </Button>
    </div>

    <!-- Videos List -->
    <div v-else class="space-y-4">
      <div
        v-for="video in videos"
        :key="video.id"
        class="liquid-glass rounded-xl p-6 hover:shadow-lg transition-shadow animate-fade-in-up"
      >
        <div class="flex items-start justify-between gap-4">
          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <span
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-semibold text-sm shrink-0"
              >
                {{ video.order }}
              </span>
              <h3 class="text-xl font-semibold text-foreground">{{ video.title }}</h3>
            </div>

            <!-- Course and Chapter Info -->
            <div class="flex items-center gap-2 text-sm text-muted-foreground mb-3">
              <PhVideo :size="16" />
              <span>{{ video.chapter?.course?.title }} → {{ video.chapter?.title }}</span>
            </div>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <span
                v-if="video.level"
                :class="[
                  'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize',
                  getLevelColor(video.level),
                ]"
              >
                {{ video.level }}
              </span>

              <span class="text-muted-foreground">
                {{ formatDuration(video.duration_seconds) }}
              </span>

              <span
                :class="[
                  'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                  video.is_published
                    ? 'bg-primary/10 text-primary'
                    : 'bg-muted text-muted-foreground',
                ]"
              >
                <PhEye v-if="video.is_published" :size="14" />
                <PhEyeSlash v-else :size="14" />
                {{ video.is_published ? 'Published' : 'Draft' }}
              </span>

              <span
                :class="[
                  'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                  video.is_preview
                    ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                    : 'bg-muted text-muted-foreground',
                ]"
              >
                <PhLockOpen v-if="video.is_preview" :size="14" />
                <PhLock v-else :size="14" />
                {{ video.is_preview ? 'Preview' : 'Locked' }}
              </span>

              <span v-if="video.youtube_video_id" class="text-muted-foreground">
                YouTube: {{ video.youtube_video_id }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 flex-shrink-0">
            <Button
              v-if="video.youtube_video_id"
              @click="handlePreview(video)"
              variant="outline"
              size="sm"
              class="gap-1"
            >
              <PhPlay :size="16" />
              Preview
            </Button>
            <Button
              @click="router.push(`/admin/videos/${video.id}/edit`)"
              variant="outline"
              size="sm"
            >
              <PhPencil :size="16" class="mr-1" />
              Edit
            </Button>
            <Button
              @click="handleDelete(video.id, video.title)"
              variant="outline"
              size="sm"
              :disabled="isDeleting"
              class="text-destructive hover:text-destructive"
            >
              <PhTrash :size="16" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination.last > 1"
      class="flex items-center justify-between liquid-glass rounded-xl p-4"
    >
      <p class="text-sm text-muted-foreground">
        Showing {{ (pagination.current - 1) * 15 + 1 }} to
        {{ Math.min(pagination.current * 15, pagination.total) }} of {{ pagination.total }} videos
      </p>
      <div class="flex gap-2">
        <Button @click="page--" :disabled="pagination.current === 1" variant="outline" size="sm">
          Previous
        </Button>
        <Button
          @click="page++"
          :disabled="pagination.current === pagination.last"
          variant="outline"
          size="sm"
        >
          Next
        </Button>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model:open="deleteDialogOpen"
      :title="`Delete ${videoToDelete?.title}?`"
      description="This will permanently delete this video. This action cannot be undone."
      confirm-text="Delete Video"
      cancel-text="Cancel"
      variant="destructive"
      :loading="isDeleting"
      @confirm="confirmDelete"
    />

    <!-- Video Preview Modal -->
    <AdminVideoPreviewModal v-model:open="previewOpen" :video="previewVideo" />
  </div>
</template>
