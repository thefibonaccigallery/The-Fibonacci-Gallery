<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useChapters } from '@/composables/admin/useChapters'
import { useCourses } from '@/composables/admin/useCourses'
import { PhBookOpen, PhPlus, PhPencil, PhTrash, PhBook } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog.vue'

const router = useRouter()
const { fetchChapters, deleteChapter, isDeleting } = useChapters()
const { fetchCourses } = useCourses()

const page = ref(1)
const search = ref('')
const selectedCourseId = ref<number | undefined>(undefined)

// Delete confirmation dialog state
const deleteDialogOpen = ref(false)
const chapterToDelete = ref<{ id: number; title: string } | null>(null)

// Fetch courses for filter dropdown
const { data: coursesData } = useQuery({
  queryKey: ['courses-list'],
  queryFn: () => fetchCourses({ per_page: 100 }),
})

const courses = computed(() => coursesData.value?.data || [])

// Fetch chapters
const { data: chaptersData, isLoading, refetch } = useQuery({
  queryKey: ['chapters', page, search, selectedCourseId],
  queryFn: () =>
    fetchChapters({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
      course_id: selectedCourseId.value,
    }),
})

const chapters = computed(() => chaptersData.value?.data || [])
const pagination = computed(() => ({
  current: chaptersData.value?.current_page || 1,
  last: chaptersData.value?.last_page || 1,
  total: chaptersData.value?.total || 0,
}))

const handleDelete = (id: number, title: string) => {
  chapterToDelete.value = { id, title }
  deleteDialogOpen.value = true
}

const confirmDelete = () => {
  if (chapterToDelete.value) {
    deleteChapter(chapterToDelete.value.id, {
      onSuccess: () => {
        refetch()
        deleteDialogOpen.value = false
        chapterToDelete.value = null
      },
    })
  }
}

const formatDuration = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
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
        <h1 class="text-3xl font-bold text-foreground">Chapters</h1>
        <p class="text-muted-foreground mt-1">Manage course chapters</p>
      </div>
      <Button @click="router.push('/admin/chapters/create')" class="gap-2">
        <PhPlus :size="20" />
        Create Chapter
      </Button>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 flex flex-col sm:flex-row gap-4">
      <Input
        v-model="search"
        placeholder="Search chapters..."
        class="flex-1"
        @input="page = 1"
      />
      <select
        v-model="selectedCourseId"
        @change="page = 1"
        class="px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary min-w-[200px]"
      >
        <option :value="undefined">All Courses</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.title }}
        </option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading chapters...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="chapters.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhBookOpen :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">No chapters found</h3>
      <p class="text-muted-foreground mb-6">
        {{ selectedCourseId ? 'No chapters in this course yet' : 'Get started by creating your first chapter' }}
      </p>
      <Button @click="router.push('/admin/chapters/create')">
        <PhPlus :size="20" class="mr-2" />
        Create Chapter
      </Button>
    </div>

    <!-- Chapters List -->
    <div v-else class="space-y-4">
      <div
        v-for="chapter in chapters"
        :key="chapter.id"
        class="liquid-glass rounded-xl p-6 hover:shadow-lg transition-shadow animate-fade-in-up"
      >
        <div class="flex items-start justify-between gap-4">
          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <span
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-semibold text-sm"
              >
                {{ chapter.order }}
              </span>
              <h3 class="text-xl font-semibold text-foreground">{{ chapter.title }}</h3>
            </div>

            <!-- Course Info -->
            <div class="flex items-center gap-2 text-sm text-muted-foreground mb-3">
              <PhBook :size="16" />
              <span>{{ chapter.course?.title }}</span>
            </div>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <span
                v-if="chapter.level"
                :class="[
                  'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize',
                  getLevelColor(chapter.level),
                ]"
              >
                {{ chapter.level }}
              </span>
              <span class="text-muted-foreground">
                {{ chapter.videos?.length || 0 }} videos
              </span>
              <span class="text-muted-foreground">
                {{ formatDuration(chapter.length_seconds) }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 flex-shrink-0">
            <Button
              @click="router.push(`/admin/chapters/${chapter.id}/edit`)"
              variant="outline"
              size="sm"
            >
              <PhPencil :size="16" class="mr-1" />
              Edit
            </Button>
            <Button
              @click="handleDelete(chapter.id, chapter.title)"
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
        {{ Math.min(pagination.current * 15, pagination.total) }} of {{ pagination.total }} chapters
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
      :title="`Delete ${chapterToDelete?.title}?`"
      description="This will permanently delete this chapter along with all its videos. This action cannot be undone."
      confirm-text="Delete Chapter"
      cancel-text="Cancel"
      variant="destructive"
      :loading="isDeleting"
      @confirm="confirmDelete"
    />
  </div>
</template>

