<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useChapters } from '@/composables/admin/useChapters'
import { useCourses } from '@/composables/admin/useCourses'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue'
import type { ChapterFormData, CourseLevel } from '@/types/CourseTypes'

const route = useRoute()
const router = useRouter()
const { fetchChapter, createChapter, updateChapter, isCreating, isUpdating } = useChapters()
const { fetchCourses } = useCourses()

const chapterId = computed(() => {
  const id = route.params.id
  return id ? Number(id) : null
})

const isEditMode = computed(() => !!chapterId.value)

// Form state
const form = ref<ChapterFormData>({
  course_id: 0,
  title: '',
  level: undefined,
  order: undefined,
})

const errors = ref<Record<string, string>>({})

// Fetch courses for dropdown
const { data: coursesData } = useQuery({
  queryKey: ['courses-list'],
  queryFn: () => fetchCourses({ per_page: 100 }),
})

const courses = computed(() => coursesData.value?.data || [])

// Fetch chapter data if editing
const { data: chapterData, isLoading } = useQuery({
  queryKey: ['chapter', chapterId],
  queryFn: () => fetchChapter(chapterId.value!),
  enabled: isEditMode,
})

// Populate form when chapter data is loaded
onMounted(() => {
  if (chapterData.value) {
    form.value = {
      course_id: chapterData.value.course_id,
      title: chapterData.value.title,
      level: chapterData.value.level || undefined,
      order: chapterData.value.order,
    }
  }
})

// Validate form
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.course_id) {
    errors.value.course_id = 'Please select a course'
  }

  if (!form.value.title.trim()) {
    errors.value.title = 'Title is required'
  }

  if (form.value.order !== undefined && form.value.order < 1) {
    errors.value.order = 'Order must be 1 or greater'
  }

  return Object.keys(errors.value).length === 0
}

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) return

  const formData = {
    ...form.value,
    level: form.value.level || undefined,
    order: form.value.order || undefined,
  }

  if (isEditMode.value) {
    // Update existing chapter
    updateChapter(
      { id: chapterId.value!, data: formData },
      {
        onSuccess: () => {
          router.push('/admin/chapters')
        },
        onError: (error: any) => {
          // Handle validation errors from backend
          if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
          }
        },
      },
    )
  } else {
    // Create new chapter
    createChapter(formData, {
      onSuccess: () => {
        router.push('/admin/chapters')
      },
      onError: (error: any) => {
        // Handle validation errors from backend
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
      },
    })
  }
}

const isSaving = computed(() => isCreating.value || isUpdating.value)
</script>

<template>
  <div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <Button @click="router.push('/admin/chapters')" variant="ghost" size="sm">
        <PhArrowLeft :size="20" class="mr-2" />
        Back
      </Button>
      <div>
        <h1 class="text-3xl font-bold text-foreground">
          {{ isEditMode ? 'Edit Chapter' : 'Create Chapter' }}
        </h1>
        <p class="text-muted-foreground mt-1">
          {{ isEditMode ? 'Update chapter details' : 'Add a new chapter to a course' }}
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="liquid-glass rounded-xl p-12 text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading chapter...</p>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Basic Information -->
      <div class="liquid-glass rounded-xl p-6 space-y-6">
        <h3 class="text-lg font-semibold text-foreground">Chapter Information</h3>

        <!-- Course Selection -->
        <div class="space-y-2">
          <label for="course_id" class="text-sm font-medium text-foreground">
            Course <span class="text-destructive">*</span>
          </label>
          <select
            id="course_id"
            v-model.number="form.course_id"
            :class="[
              'w-full px-3 py-2 rounded-lg border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary',
              errors.course_id ? 'border-destructive' : 'border-border',
            ]"
            :disabled="isEditMode"
          >
            <option :value="0" disabled>Select a course</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.title }}
            </option>
          </select>
          <p v-if="errors.course_id" class="text-sm text-destructive">{{ errors.course_id }}</p>
          <p v-if="isEditMode" class="text-xs text-muted-foreground">
            Course cannot be changed after creation
          </p>
        </div>

        <!-- Title -->
        <div class="space-y-2">
          <label for="title" class="text-sm font-medium text-foreground">
            Title <span class="text-destructive">*</span>
          </label>
          <Input
            id="title"
            v-model="form.title"
            placeholder="e.g., Introduction to Forex"
            :class="{ 'border-destructive': errors.title }"
          />
          <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>
        </div>

        <!-- Level -->
        <div class="space-y-2">
          <label for="level" class="text-sm font-medium text-foreground">Level (Optional)</label>
          <select
            id="level"
            v-model="form.level"
            class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
          >
            <option :value="undefined">Not specified</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
          <p class="text-xs text-muted-foreground">
            Override the course level for this specific chapter
          </p>
        </div>

        <!-- Order -->
        <div class="space-y-2">
          <label for="order" class="text-sm font-medium text-foreground"> Order (Optional) </label>
          <Input
            id="order"
            v-model.number="form.order"
            type="number"
            min="1"
            placeholder="Auto-assigned if left empty"
            :class="{ 'border-destructive': errors.order }"
          />
          <p class="text-xs text-muted-foreground">
            Position of this chapter in the course. Auto-assigned to the end if left empty.
          </p>
          <p v-if="errors.order" class="text-sm text-destructive">{{ errors.order }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-4">
        <Button type="submit" :disabled="isSaving" class="gap-2">
          <PhFloppyDisk :size="20" />
          {{ isSaving ? 'Saving...' : isEditMode ? 'Update Chapter' : 'Create Chapter' }}
        </Button>
        <Button
          type="button"
          @click="router.push('/admin/chapters')"
          variant="outline"
          :disabled="isSaving"
        >
          Cancel
        </Button>
      </div>
    </form>
  </div>
</template>
