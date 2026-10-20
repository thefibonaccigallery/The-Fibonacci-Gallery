<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserCourses } from '@/composables/useUserCourses'
import { useAuthStore } from '@/stores/auth'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  PhSpinner,
  PhMagnifyingGlass,
  PhBookOpen,
  PhClock,
  PhGraduationCap,
} from '@phosphor-icons/vue'

const router = useRouter()
const route = useRoute()
const { useExploreCourses } = useUserCourses()
const authStore = useAuthStore()

const handleEnroll = (courseUuid: string) => {
  if (authStore.isAuthenticated) {
    router.push(`/checkout/${courseUuid}`)
  } else {
    router.push(`/courses/${courseUuid}/details`)
  }
}

const searchQuery = ref('')
const selectedLevel = ref<string>('all')
const sortBy = ref('created_at')
const sortOrder = ref('desc')
const isLevelOpen = ref(false)
const isSortOpen = ref(false)

const params = computed(() => ({
  search: searchQuery.value || undefined,
  level: selectedLevel.value === 'all' ? undefined : selectedLevel.value,
  sort_by: sortBy.value,
  sort_order: sortOrder.value,
}))

watch(
  () => route.fullPath,
  () => {
    isLevelOpen.value = false
    isSortOpen.value = false
  },
)

const { data, isLoading, error } = useExploreCourses(params)

const courses = computed(() => data.value?.courses || [])

const formatDuration = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
}

const viewCourseDetails = (courseUuid: string) => {
  router.push(`/courses/${courseUuid}/details`)
}

const getLevelColor = (level: string) => {
  switch (level) {
    case 'beginner':
      return 'text-green-500 border-green-500/30 bg-green-500/10'
    case 'intermediate':
      return 'text-yellow-500 border-yellow-500/30 bg-yellow-500/10'
    case 'advanced':
      return 'text-red-500 border-red-500/30 bg-red-500/10'
    default:
      return 'text-muted-foreground border-border/30 bg-secondary/50'
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background">
      <!-- Header -->
      <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-3xl font-bold text-foreground">Explore Courses</h1>
              <p class="text-sm text-muted-foreground mt-1">
                Discover new courses to expand your knowledge
              </p>
            </div>
            <Button
              v-if="authStore.isAuthenticated"
              @click="router.push('/my-courses')"
              variant="outline"
              class="border-primary/30 text-primary hover:bg-primary/10"
            >
              <PhGraduationCap class="h-4 w-4 mr-2" />
              My Courses
            </Button>
          </div>

          <!-- Filters -->
          <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="relative flex-1">
              <PhMagnifyingGlass
                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
              />
              <Input
                v-model="searchQuery"
                placeholder="Search courses..."
                class="pl-10 bg-background/50 border-border/40"
              />
            </div>

            <!-- Level Filter -->
            <Select v-model="selectedLevel" :open="isLevelOpen" @update:open="isLevelOpen = $event">
              <SelectTrigger class="w-full md:w-48 bg-background/50 border-border/40">
                <SelectValue placeholder="All Levels" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Levels</SelectItem>
                <SelectItem value="beginner">Beginner</SelectItem>
                <SelectItem value="intermediate">Intermediate</SelectItem>
                <SelectItem value="advanced">Advanced</SelectItem>
              </SelectContent>
            </Select>

            <!-- Sort -->
            <Select v-model="sortBy" :open="isSortOpen" @update:open="isSortOpen = $event">
              <SelectTrigger class="w-full md:w-48 bg-background/50 border-border/40">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="created_at">Newest</SelectItem>
                <SelectItem value="title">Title</SelectItem>
                <SelectItem value="price">Price</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="container mx-auto px-4 py-8 pb-24 md:pb-8">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-20">
          <PhSpinner class="h-8 w-8 animate-spin text-primary" />
        </div>

        <!-- Error State -->
        <div
          v-else-if="error"
          class="text-center py-20 liquid-glass rounded-2xl border border-border/20 p-8"
        >
          <p class="text-destructive">Failed to load courses. Please try again.</p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="courses.length === 0"
          class="text-center py-20 liquid-glass rounded-2xl border border-border/20 p-8"
        >
          <PhBookOpen class="h-16 w-16 mx-auto text-muted-foreground mb-4" />
          <h3 class="text-xl font-semibold text-foreground mb-2">No Courses Found</h3>
          <p class="text-muted-foreground">
            Try adjusting your search or filters to find more courses
          </p>
        </div>

        <!-- Courses Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="course in courses"
            :key="course.uuid"
            class="liquid-glass rounded-2xl p-6 border border-border/20 flex flex-col justify-between transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-primary/5"
          >
            <!-- Card content -->
            <div class="space-y-4">
              <!-- Thumbnail -->
              <div
                class="relative w-full h-40 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center group cursor-pointer"
                @click="viewCourseDetails(course.uuid)"
              >
                <img
                  v-if="course.thumbnail_url"
                  :src="course.thumbnail_url"
                  :alt="course.title"
                  class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                />
                <svg
                  v-else
                  class="absolute inset-0 w-full h-full opacity-30 transition-opacity duration-300 group-hover:opacity-50"
                  viewBox="0 0 100 100"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <!-- Geometric background -->
                  <circle
                    cx="50"
                    cy="50"
                    r="40"
                    stroke="rgba(34, 197, 94, 0.1)"
                    stroke-width="0.5"
                    stroke-dasharray="2 2"
                  />
                  <polygon
                    points="50,10 90,90 10,90"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                    fill="none"
                  />
                  <circle
                    cx="50"
                    cy="63.3"
                    r="26.7"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                    fill="none"
                  />
                </svg>
                <div
                  v-if="course.level"
                  :class="[
                    'absolute top-3 right-3 backdrop-blur-md px-2 py-1 rounded text-xs font-bold border capitalize',
                    getLevelColor(course.level),
                  ]"
                >
                  {{ course.level }}
                </div>
              </div>

              <!-- Course Info -->
              <div>
                <h4
                  class="text-lg font-bold text-foreground line-clamp-2 mb-2 cursor-pointer hover:text-primary transition-colors"
                  @click="viewCourseDetails(course.uuid)"
                >
                  {{ course.title }}
                </h4>
                <p class="text-xs text-muted-foreground line-clamp-3 mb-3">
                  {{ course.description || 'No description available' }}
                </p>

                <!-- Course Stats -->
                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                  <div class="flex items-center gap-1">
                    <PhBookOpen class="h-3.5 w-3.5" />
                    <span>{{ course.totalLessons }} lessons</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <PhClock class="h-3.5 w-3.5" />
                    <span>{{ formatDuration(course.length_seconds) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Price and Button -->
            <div class="space-y-3 mt-6">
              <div class="flex items-center justify-between">
                <span class="text-2xl font-bold text-primary">
                  {{ course.price === 0 ? 'Free' : `$${course.price}` }}
                </span>
              </div>

              <Button
                @click="handleEnroll(course.uuid)"
                class="w-full text-sm font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground"
              >
                Enroll Now
              </Button>

              <Button
                @click="viewCourseDetails(course.uuid)"
                variant="outline"
                class="w-full text-sm font-semibold rounded-xl border-border/40 hover:bg-secondary/50"
              >
                View Details
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
  backdrop-filter: blur(10px);
}
</style>
