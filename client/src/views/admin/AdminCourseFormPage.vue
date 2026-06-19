<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useCourses } from '@/composables/admin/useCourses'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ImageUpload from '@/components/admin/shared/ImageUpload.vue'
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue'
import type { CourseFormData, CourseLevel } from '@/types/CourseTypes'

const route = useRoute()
const router = useRouter()
const {
  fetchCourse,
  createCourse,
  updateCourse,
  uploadThumbnail,
  deleteThumbnail,
  isCreating,
  isUpdating,
  isUploadingThumbnail,
  isDeletingThumbnail,
} = useCourses()

const courseUuid = computed(() => {
  const uuid = route.params.uuid
  return uuid ? String(uuid) : null
})

const isEditMode = computed(() => !!courseUuid.value)

// Form state
const form = ref<CourseFormData>({
  title: '',
  slug: '',
  description: '',
  level: 'beginner',
  price: 0,
  is_published: false,
})

const thumbnailFile = ref<File | null>(null)
const currentThumbnailUrl = ref<string | null>(null)
const errors = ref<Record<string, string>>({})

// Fetch course data if editing
const { data: courseData, isLoading } = useQuery({
  queryKey: ['course', courseUuid],
  queryFn: () => fetchCourse(courseUuid.value!),
  enabled: isEditMode,
})

// Populate form when course data arrives (query is async — onMounted fires before data resolves)
watch(courseData, (data) => {
  if (data) {
    form.value = {
      title: data.title,
      slug: data.slug,
      description: data.description || '',
      level: data.level,
      price: data.price,
      is_published: data.is_published,
    }
    currentThumbnailUrl.value = data.thumbnail_url
  }
})

// Auto-generate slug from title
const handleTitleChange = () => {
  if (!isEditMode.value || !form.value.slug) {
    form.value.slug = form.value.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
  }
}

// Validate form
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.value.title.trim()) {
    errors.value.title = 'Title is required'
  }

  if (!form.value.slug?.trim()) {
    errors.value.slug = 'Slug is required'
  }

  if (form.value.price < 0) {
    errors.value.price = 'Price must be 0 or greater'
  }

  return Object.keys(errors.value).length === 0
}

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) return

  const formData = {
    ...form.value,
    description: form.value.description || undefined,
  }

  if (isEditMode.value) {
    // Update existing course
    updateCourse(
      { uuid: courseUuid.value!, data: formData },
      {
        onSuccess: async (response) => {
          // Upload thumbnail if changed
          if (thumbnailFile.value) {
            await uploadThumbnail({
              uuid: courseUuid.value!,
              file: thumbnailFile.value,
            })
          }
          router.push('/admin/courses')
        },
      },
    )
  } else {
    // Create new course
    createCourse(formData, {
      onSuccess: async (response) => {
        const newCourseUuid = response.course.uuid
        // Upload thumbnail if provided
        if (thumbnailFile.value) {
          await uploadThumbnail({
            uuid: newCourseUuid,
            file: thumbnailFile.value,
          })
        }
        router.push('/admin/courses')
      },
    })
  }
}

const handleDeleteThumbnail = () => {
  if (courseUuid.value) {
    deleteThumbnail(courseUuid.value)
    currentThumbnailUrl.value = null
  }
}

const isSaving = computed(() => isCreating.value || isUpdating.value || isUploadingThumbnail.value)
</script>

<template>
  <div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <Button @click="router.push('/admin/courses')" variant="ghost" size="sm">
        <PhArrowLeft :size="20" class="mr-2" />
        Back
      </Button>
      <div>
        <h1 class="text-3xl font-bold text-foreground">
          {{ isEditMode ? 'Edit Course' : 'Create Course' }}
        </h1>
        <p class="text-muted-foreground mt-1">
          {{ isEditMode ? 'Update course details' : 'Add a new course to your platform' }}
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="liquid-glass rounded-xl p-12 text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading course...</p>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Thumbnail Upload -->
      <div class="liquid-glass rounded-xl p-6">
        <h3 class="text-lg font-semibold text-foreground mb-4">Course Thumbnail</h3>
        <ImageUpload
          v-model="thumbnailFile"
          :current-image-url="currentThumbnailUrl"
          @delete="handleDeleteThumbnail"
        />
      </div>

      <!-- Basic Information -->
      <div class="liquid-glass rounded-xl p-6 space-y-6">
        <h3 class="text-lg font-semibold text-foreground">Basic Information</h3>

        <!-- Title -->
        <div class="space-y-2">
          <label for="title" class="text-sm font-medium text-foreground">
            Title <span class="text-destructive">*</span>
          </label>
          <Input
            id="title"
            v-model="form.title"
            @input="handleTitleChange"
            placeholder="e.g., Forex Fundamentals"
            :class="{ 'border-destructive': errors.title }"
          />
          <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>
        </div>

        <!-- Slug -->
        <div class="space-y-2">
          <label for="slug" class="text-sm font-medium text-foreground">
            Slug <span class="text-destructive">*</span>
          </label>
          <Input
            id="slug"
            v-model="form.slug"
            placeholder="e.g., forex-fundamentals"
            :class="{ 'border-destructive': errors.slug }"
          />
          <p class="text-xs text-muted-foreground">
            URL-friendly version of the title. Auto-generated if left empty.
          </p>
          <p v-if="errors.slug" class="text-sm text-destructive">{{ errors.slug }}</p>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <label for="description" class="text-sm font-medium text-foreground"> Description </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
            placeholder="Describe what students will learn in this course..."
            class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
          ></textarea>
        </div>

        <!-- Level -->
        <div class="space-y-2">
          <label for="level" class="text-sm font-medium text-foreground">
            Level <span class="text-destructive">*</span>
          </label>
          <select
            id="level"
            v-model="form.level"
            class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
          >
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
        </div>

        <!-- Price -->
        <div class="space-y-2">
          <label for="price" class="text-sm font-medium text-foreground">
            Price (USD) <span class="text-destructive">*</span>
          </label>
          <Input
            id="price"
            v-model.number="form.price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            :class="{ 'border-destructive': errors.price }"
          />
          <p v-if="errors.price" class="text-sm text-destructive">{{ errors.price }}</p>
        </div>

        <!-- Published Status -->
        <div class="flex items-center gap-3">
          <input
            id="is_published"
            v-model="form.is_published"
            type="checkbox"
            class="w-4 h-4 rounded border-border text-primary focus:ring-primary"
          />
          <label for="is_published" class="text-sm font-medium text-foreground cursor-pointer">
            Publish this course
          </label>
        </div>
        <p class="text-xs text-muted-foreground">
          Published courses are visible to students. Unpublished courses are only visible to admins.
        </p>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-4">
        <Button type="submit" :disabled="isSaving" class="gap-2">
          <PhFloppyDisk :size="20" />
          {{ isSaving ? 'Saving...' : isEditMode ? 'Update Course' : 'Create Course' }}
        </Button>
        <Button
          type="button"
          @click="router.push('/admin/courses')"
          variant="outline"
          :disabled="isSaving"
        >
          Cancel
        </Button>
      </div>
    </form>
  </div>
</template>
