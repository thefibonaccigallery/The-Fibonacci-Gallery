<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useVideos } from '@/composables/admin/useVideos'
import { useChapters } from '@/composables/admin/useChapters'
import { useCourses } from '@/composables/admin/useCourses'
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import type { VideoFormState } from '@/schemas/videoSchemas'

const route = useRoute()
const router = useRouter()

const videoId = computed(() => {
  const id = route.params.id
  return id ? Number(id) : null
})

const isEditMode = computed(() => videoId.value !== null)

const {
  errors,
  fetchVideo,
  createVideo,
  updateVideo,
  clearError,
  validateForm,
  isCreating,
  isUpdating,
} = useVideos()

const { fetchChapters } = useChapters()
const { fetchCourses } = useCourses()

const selectedCourseId = ref<number | undefined>(undefined)

const form = ref<VideoFormState>({
  chapter_id: 0,
  title: '',
  youtube_video_id: null,
  level: null,
  order: 1,
  duration_seconds: 0,
  is_published: false,
  is_preview: false,
})

// Fetch courses for dropdown
const { data: coursesData, isLoading: isLoadingCourses } = useQuery({
  queryKey: ['courses-list'],
  queryFn: () => fetchCourses({ per_page: 100 }),
})

const courses = computed(() => coursesData.value?.data || [])

// Fetch chapters for selected course
const { data: chaptersData, isLoading: isLoadingChapters } = useQuery({
  queryKey: ['chapters-by-course', selectedCourseId],
  queryFn: () => fetchChapters({ per_page: 100, course_id: selectedCourseId.value }),
  enabled: computed(() => selectedCourseId.value !== undefined),
})

const chapters = computed(() => chaptersData.value?.data || [])

// Reset chapter selection when course changes
watch(selectedCourseId, () => {
  if (!isEditMode.value) {
    form.value.chapter_id = 0
  }
})

// Fetch video data if editing
const { data: videoData, isLoading: isLoadingVideo } = useQuery({
  queryKey: ['video', videoId],
  queryFn: () => fetchVideo(videoId.value!),
  enabled: isEditMode.value,
})

// Populate form when video data is loaded
watch(videoData, (data) => {
  if (data) {
    form.value = {
      chapter_id: data.chapter_id,
      title: data.title,
      youtube_video_id: data.youtube_video_id,
      level: data.level,
      order: data.order,
      duration_seconds: data.duration_seconds,
      is_published: data.is_published,
      is_preview: data.is_preview,
    }
    // Set the course ID based on the chapter's course
    if (data.chapter?.course_id) {
      selectedCourseId.value = data.chapter.course_id
    }
  }
}, { immediate: true })

const isLoading = computed(() => isLoadingCourses.value || isLoadingChapters.value || isLoadingVideo.value)
const isSaving = computed(() => isCreating.value || isUpdating.value)

const handleSubmit = () => {
  if (!validateForm(form.value)) {
    return
  }

  if (isEditMode.value) {
    updateVideo(
      { id: videoId.value!, data: form.value },
      {
        onSuccess: () => {
          router.push('/admin/videos')
        },
      },
    )
  } else {
    createVideo(form.value, {
      onSuccess: () => {
        router.push('/admin/videos')
      },
    })
  }
}

const goBack = () => {
  router.push('/admin/videos')
}

// Convert duration from seconds to HH:MM:SS format for display
const durationDisplay = computed({
  get: () => {
    const hours = Math.floor(form.value.duration_seconds / 3600)
    const minutes = Math.floor((form.value.duration_seconds % 3600) / 60)
    const seconds = form.value.duration_seconds % 60
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
  },
  set: (value: string) => {
    const parts = value.split(':')
    if (parts.length === 3) {
      const hours = parseInt(parts[0]) || 0
      const minutes = parseInt(parts[1]) || 0
      const seconds = parseInt(parts[2]) || 0
      form.value.duration_seconds = hours * 3600 + minutes * 60 + seconds
    }
  },
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <Button @click="goBack" variant="ghost" size="sm">
        <PhArrowLeft :size="20" />
      </Button>
      <div>
        <h1 class="text-3xl font-bold text-foreground">
          {{ isEditMode ? 'Edit Video' : 'Create Video' }}
        </h1>
        <p class="text-muted-foreground mt-1">
          {{ isEditMode ? 'Update video details' : 'Add a new video to a chapter' }}
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading...</p>
    </div>

    <!-- Form -->
    <div v-else class="liquid-glass rounded-xl p-6 space-y-6 animate-fade-in-up">
      <!-- Course Selection -->
      <div class="space-y-2">
        <Label for="course_id">Course *</Label>
        <select
          id="course_id"
          v-model="selectedCourseId"
          class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
        >
          <option :value="undefined" disabled>Select a course first</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
        <p class="text-xs text-muted-foreground">
          Select a course to see its chapters
        </p>
      </div>

      <!-- Chapter Selection -->
      <div class="space-y-2">
        <Label for="chapter_id">Chapter *</Label>
        <select
          id="chapter_id"
          v-model="form.chapter_id"
          @change="clearError('chapter_id')"
          :disabled="!selectedCourseId"
          :class="[
            'w-full px-3 py-2 rounded-lg border bg-background text-foreground',
            'focus:outline-none focus:ring-2 focus:ring-primary',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            errors.chapter_id ? 'border-destructive' : 'border-border',
          ]"
        >
          <option :value="0" disabled>
            {{ selectedCourseId ? 'Select a chapter' : 'Select a course first' }}
          </option>
          <option v-for="chapter in chapters" :key="chapter.id" :value="chapter.id">
            {{ chapter.title }}
          </option>
        </select>
        <p v-if="selectedCourseId && chapters.length === 0 && !isLoadingChapters" class="text-xs text-muted-foreground">
          No chapters found in this course. Create a chapter first.
        </p>
        <p v-if="errors.chapter_id" class="text-sm text-destructive">
          {{ errors.chapter_id }}
        </p>
      </div>

      <!-- Title -->
      <div class="space-y-2">
        <Label for="title">Title *</Label>
        <Input
          id="title"
          v-model="form.title"
          @input="clearError('title')"
          placeholder="Enter video title"
          :class="errors.title ? 'border-destructive' : ''"
        />
        <p v-if="errors.title" class="text-sm text-destructive">
          {{ errors.title }}
        </p>
      </div>

      <!-- YouTube Video ID -->
      <div class="space-y-2">
        <Label for="youtube_video_id">YouTube Video ID</Label>
        <Input
          id="youtube_video_id"
          v-model="form.youtube_video_id"
          @input="clearError('youtube_video_id')"
          placeholder="e.g., dQw4w9WgXcQ"
          :class="errors.youtube_video_id ? 'border-destructive' : ''"
        />
        <p class="text-xs text-muted-foreground">
          The 11-character ID from the YouTube URL (e.g., youtube.com/watch?v=<strong>dQw4w9WgXcQ</strong>)
        </p>
        <p v-if="errors.youtube_video_id" class="text-sm text-destructive">
          {{ errors.youtube_video_id }}
        </p>
      </div>

      <!-- Level -->
      <div class="space-y-2">
        <Label for="level">Level</Label>
        <select
          id="level"
          v-model="form.level"
          @change="clearError('level')"
          class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
        >
          <option :value="null">No level</option>
          <option value="beginner">Beginner</option>
          <option value="intermediate">Intermediate</option>
          <option value="advanced">Advanced</option>
        </select>
        <p v-if="errors.level" class="text-sm text-destructive">
          {{ errors.level }}
        </p>
      </div>

      <!-- Order and Duration -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order -->
        <div class="space-y-2">
          <Label for="order">Order *</Label>
          <Input
            id="order"
            v-model.number="form.order"
            type="number"
            @input="clearError('order')"
            placeholder="1"
            :class="errors.order ? 'border-destructive' : ''"
          />
          <p v-if="errors.order" class="text-sm text-destructive">
            {{ errors.order }}
          </p>
        </div>

        <!-- Duration -->
        <div class="space-y-2">
          <Label for="duration">Duration (HH:MM:SS)</Label>
          <Input
            id="duration"
            v-model="durationDisplay"
            @input="clearError('duration_seconds')"
            placeholder="00:05:30"
            :class="errors.duration_seconds ? 'border-destructive' : ''"
          />
          <p class="text-xs text-muted-foreground">
            {{ form.duration_seconds }} seconds
          </p>
          <p v-if="errors.duration_seconds" class="text-sm text-destructive">
            {{ errors.duration_seconds }}
          </p>
        </div>
      </div>

      <!-- Switches -->
      <div class="space-y-4">
        <!-- Published -->
        <div class="flex items-center justify-between p-4 rounded-lg border border-border">
          <div class="space-y-0.5">
            <Label for="is_published">Published</Label>
            <p class="text-sm text-muted-foreground">
              Make this video visible to students
            </p>
          </div>
          <Switch
            id="is_published"
            v-model:checked="form.is_published"
            @update:checked="clearError('is_published')"
          />
        </div>

        <!-- Preview -->
        <div class="flex items-center justify-between p-4 rounded-lg border border-border">
          <div class="space-y-0.5">
            <Label for="is_preview">Preview</Label>
            <p class="text-sm text-muted-foreground">
              Allow non-enrolled students to watch this video
            </p>
          </div>
          <Switch
            id="is_preview"
            v-model:checked="form.is_preview"
            @update:checked="clearError('is_preview')"
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 pt-4">
        <Button @click="goBack" variant="outline" class="flex-1">
          Cancel
        </Button>
        <Button @click="handleSubmit" :disabled="isSaving" class="flex-1 gap-2">
          <PhFloppyDisk :size="20" />
          {{ isSaving ? 'Saving...' : isEditMode ? 'Update Video' : 'Create Video' }}
        </Button>
      </div>
    </div>
  </div>
</template>
