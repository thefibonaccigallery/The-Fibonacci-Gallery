<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserCourses } from '@/composables/useUserCourses'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { PhPlay, PhSpinner, PhBookOpen } from '@phosphor-icons/vue'

const router = useRouter()
const { useMyEnrolledCourses } = useUserCourses()

const { data, isLoading, error } = useMyEnrolledCourses()

const courses = computed(() => data.value?.courses || [])

const getProgress = (completed: number, total: number) => {
  return Math.round((completed / total) * 100)
}

const goToCoursePlayer = (courseUuid: string) => {
  router.push(`/courses/${courseUuid}/player`)
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background">
      <!-- Header -->
      <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-foreground">My Courses</h1>
              <p class="text-sm text-muted-foreground mt-1">Continue your learning journey</p>
            </div>
            <Button
              @click="router.push('/explore')"
              class="bg-primary hover:bg-primary/90 text-primary-foreground"
            >
              <PhBookOpen class="h-4 w-4 mr-2" />
              Explore Courses
            </Button>
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
          <h3 class="text-xl font-semibold text-foreground mb-2">No Enrolled Courses</h3>
          <p class="text-muted-foreground mb-6">
            Start your learning journey by exploring available courses
          </p>
          <Button @click="router.push('/explore')" class="bg-primary hover:bg-primary/90">
            Explore Courses
          </Button>
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
                class="relative w-full h-40 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center group"
              >
                <img
                  v-if="course.thumbnail_url"
                  :src="course.thumbnail_url"
                  :alt="course.title"
                  class="w-full h-full object-cover"
                />
                <svg
                  v-else
                  class="absolute inset-0 w-full h-full opacity-40"
                  viewBox="0 0 100 100"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <!-- Fibonacci pattern -->
                  <rect
                    x="5"
                    y="5"
                    width="90"
                    height="90"
                    rx="4"
                    stroke="rgba(34, 197, 94, 0.1)"
                    stroke-width="0.5"
                  />
                  <rect
                    x="50"
                    y="50"
                    width="5"
                    height="5"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                  />
                  <rect
                    x="45"
                    y="50"
                    width="5"
                    height="5"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                  />
                  <rect
                    x="45"
                    y="40"
                    width="10"
                    height="10"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                  />
                  <rect
                    x="35"
                    y="40"
                    width="10"
                    height="10"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                  />
                  <rect
                    x="35"
                    y="50"
                    width="20"
                    height="20"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                  />
                  <rect
                    x="55"
                    y="30"
                    width="30"
                    height="30"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                  />
                </svg>
                <div
                  class="absolute top-3 left-3 bg-black/60 backdrop-blur-md px-2 py-1 rounded text-xs font-bold text-primary border border-primary/20"
                >
                  {{ getProgress(course.completedLessons, course.totalLessons) }}% Complete
                </div>
                <div
                  v-if="course.level"
                  class="absolute top-3 right-3 bg-black/60 backdrop-blur-md px-2 py-1 rounded text-xs font-medium text-muted-foreground border border-border/20 capitalize"
                >
                  {{ course.level }}
                </div>
              </div>

              <!-- Course Info -->
              <div>
                <h4 class="text-lg font-bold text-foreground line-clamp-2 mb-2">
                  {{ course.title }}
                </h4>
                <p class="text-xs text-muted-foreground line-clamp-2 mb-3">
                  {{ course.description || 'No description available' }}
                </p>
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ course.completedLessons }} / {{ course.totalLessons }} lessons</span>
                  <span>Enrolled {{ formatDate(course.enrolled_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Progress and Button -->
            <div class="space-y-4 mt-6">
              <div class="w-full bg-secondary/80 rounded-full h-2 overflow-hidden">
                <div
                  class="bg-primary h-full rounded-full transition-all duration-700"
                  :style="{
                    width: `${getProgress(course.completedLessons, course.totalLessons)}%`,
                  }"
                />
              </div>

              <Button
                @click="goToCoursePlayer(course.uuid)"
                class="w-full text-sm font-semibold rounded-xl flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground"
              >
                <PhPlay class="h-4 w-4" weight="fill" />
                {{
                  course.completedLessons === 0
                    ? 'Start Course'
                    : course.completedLessons === course.totalLessons
                      ? 'Review Course'
                      : 'Continue Learning'
                }}
              </Button>

              <div
                v-if="course.last_watched_video"
                class="text-xs text-muted-foreground text-center"
              >
                Last watched: {{ course.last_watched_video.title }}
              </div>
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
