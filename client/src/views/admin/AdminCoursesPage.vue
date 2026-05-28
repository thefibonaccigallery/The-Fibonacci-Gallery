<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useCourses } from '@/composables/admin/useCourses'
import { PhBook, PhPlus, PhPencil, PhTrash, PhEye, PhEyeSlash } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog.vue'

const router = useRouter()
const { fetchCourses, deleteCourse, isDeleting } = useCourses()

const page = ref(1)
const search = ref('')
const filterPublished = ref<boolean | undefined>(undefined)

// Delete confirmation dialog state
const deleteDialogOpen = ref(false)
const courseToDelete = ref<{ id: number; title: string } | null>(null)

const { data: coursesData, isLoading, refetch } = useQuery({
  queryKey: ['courses', page, search, filterPublished],
  queryFn: () =>
    fetchCourses({
      page: page.value,
      per_page: 10,
      search: search.value || undefined,
      is_published: filterPublished.value,
    }),
})

const courses = computed(() => coursesData.value?.data || [])
const pagination = computed(() => ({
  current: coursesData.value?.current_page || 1,
  last: coursesData.value?.last_page || 1,
  total: coursesData.value?.total || 0,
}))

const handleDelete = (id: number, title: string) => {
  courseToDelete.value = { id, title }
  deleteDialogOpen.value = true
}

const confirmDelete = () => {
  if (courseToDelete.value) {
    deleteCourse(courseToDelete.value.id, {
      onSuccess: () => {
        refetch()
        deleteDialogOpen.value = false
        courseToDelete.value = null
      },
    })
  }
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(price)
}

const formatDuration = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-foreground">Courses</h1>
        <p class="text-muted-foreground mt-1">Manage your trading courses</p>
      </div>
      <Button @click="router.push('/admin/courses/create')" class="gap-2">
        <PhPlus :size="20" />
        Create Course
      </Button>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 flex flex-col sm:flex-row gap-4">
      <Input
        v-model="search"
        placeholder="Search courses..."
        class="flex-1"
        @input="page = 1"
      />
      <div class="flex gap-2">
        <Button
          :variant="filterPublished === undefined ? 'default' : 'outline'"
          @click="filterPublished = undefined; page = 1"
          size="sm"
        >
          All
        </Button>
        <Button
          :variant="filterPublished === true ? 'default' : 'outline'"
          @click="filterPublished = true; page = 1"
          size="sm"
        >
          Published
        </Button>
        <Button
          :variant="filterPublished === false ? 'default' : 'outline'"
          @click="filterPublished = false; page = 1"
          size="sm"
        >
          Draft
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading courses...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="courses.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhBook :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">No courses found</h3>
      <p class="text-muted-foreground mb-6">Get started by creating your first course</p>
      <Button @click="router.push('/admin/courses/create')">
        <PhPlus :size="20" class="mr-2" />
        Create Course
      </Button>
    </div>

    <!-- Courses Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="course in courses"
        :key="course.id"
        class="liquid-glass rounded-xl overflow-hidden hover:shadow-lg transition-shadow animate-fade-in-up"
      >
        <!-- Thumbnail -->
        <div class="aspect-video bg-muted relative">
          <img
            v-if="course.thumbnail_url"
            :src="course.thumbnail_url"
            :alt="course.title"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <PhBook :size="48" class="text-muted-foreground" />
          </div>
          <!-- Status Badge -->
          <div class="absolute top-2 right-2">
            <span
              :class="[
                'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                course.is_published
                  ? 'bg-primary/10 text-primary'
                  : 'bg-muted text-muted-foreground',
              ]"
            >
              <PhEye v-if="course.is_published" :size="14" />
              <PhEyeSlash v-else :size="14" />
              {{ course.is_published ? 'Published' : 'Draft' }}
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4">
          <div class="flex items-start justify-between mb-2">
            <h3 class="text-lg font-semibold text-foreground line-clamp-2">
              {{ course.title }}
            </h3>
          </div>

          <p v-if="course.description" class="text-sm text-muted-foreground line-clamp-2 mb-4">
            {{ course.description }}
          </p>

          <!-- Meta Info -->
          <div class="flex items-center gap-4 text-sm text-muted-foreground mb-4">
            <span class="capitalize">{{ course.level }}</span>
            <span>{{ course.chapters?.length || 0 }} chapters</span>
            <span>{{ formatDuration(course.length_seconds) }}</span>
          </div>

          <!-- Price -->
          <div class="text-2xl font-bold text-primary mb-4">
            {{ formatPrice(course.price) }}
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <Button
              @click="router.push(`/admin/courses/${course.id}/edit`)"
              variant="outline"
              size="sm"
              class="flex-1"
            >
              <PhPencil :size="16" class="mr-1" />
              Edit
            </Button>
            <Button
              @click="handleDelete(course.id, course.title)"
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
        Showing {{ (pagination.current - 1) * 10 + 1 }} to
        {{ Math.min(pagination.current * 10, pagination.total) }} of {{ pagination.total }} courses
      </p>
      <div class="flex gap-2">
        <Button
          @click="page--"
          :disabled="pagination.current === 1"
          variant="outline"
          size="sm"
        >
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
      :title="`Delete ${courseToDelete?.title}?`"
      description="This will permanently delete this course along with all its chapters and videos. This action cannot be undone."
      confirm-text="Delete Course"
      cancel-text="Cancel"
      variant="destructive"
      :loading="isDeleting"
      @confirm="confirmDelete"
    />
  </div>
</template>

