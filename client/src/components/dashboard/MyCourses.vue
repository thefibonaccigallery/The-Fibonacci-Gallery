<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserCourses } from '@/composables/useUserCourses'
import { Button } from '@/components/ui/button'
import { PhPlay, PhSpinner } from '@phosphor-icons/vue'

const router = useRouter()
const { useMyEnrolledCourses } = useUserCourses()

const { data, isLoading } = useMyEnrolledCourses()

const courses = computed(() => data.value?.courses?.slice(0, 3) || [])

const getProgress = (completed: number, total: number) => {
  return Math.round((completed / total) * 100)
}

const goToCoursePlayer = (courseId: number) => {
  router.push(`/courses/${courseId}/player`)
}

const viewAllCourses = () => {
  router.push('/my-courses')
}
</script>

<template>
  <div class="animate-fade-in-up mb-10" style="animation-delay: 600ms">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
        My Enrolled Courses
      </h3>
      <Button
        v-if="courses.length > 0"
        @click="viewAllCourses"
        variant="ghost"
        size="sm"
        class="text-xs text-primary hover:text-primary/80"
      >
        View All
      </Button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-10">
      <PhSpinner class="h-6 w-6 animate-spin text-primary" />
    </div>

    <!-- Empty State -->
    <div
      v-else-if="courses.length === 0"
      class="text-center py-10 liquid-glass rounded-2xl border border-border/20 p-6"
    >
      <p class="text-muted-foreground mb-4">You haven't enrolled in any courses yet</p>
      <Button @click="router.push('/explore')" class="bg-primary hover:bg-primary/90">
        Explore Courses
      </Button>
    </div>

    <!-- Scrollable container on mobile, grids on larger screens -->
    <div
      v-else
      class="flex overflow-x-auto pb-4 gap-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:overflow-x-visible md:pb-0 scrollbar-none snap-x"
    >
      <div
        v-for="course in courses"
        :key="course.id"
        class="snap-start shrink-0 w-72 md:w-auto liquid-glass rounded-2xl p-5 border border-border/20 flex flex-col justify-between transition-transform duration-300 hover:scale-[1.01]"
      >
        <!-- Card content -->
        <div class="space-y-4">
          <!-- Thumbnail -->
          <div
            class="relative w-full h-36 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center"
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
              <!-- Golden spiral or Fibonacci boxes -->
              <rect
                x="5"
                y="5"
                width="90"
                height="90"
                rx="4"
                stroke="rgba(34, 197, 94, 0.1)"
                stroke-width="0.5"
              />
              <!-- Draw squares of 1, 1, 2, 3, 5, 8, 13 etc. -->
              <rect x="50" y="50" width="5" height="5" stroke="var(--primary)" stroke-width="0.5" />
              <rect x="45" y="50" width="5" height="5" stroke="var(--primary)" stroke-width="0.5" />
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
              class="absolute top-3 left-3 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-[10px] font-bold text-primary border border-primary/20"
            >
              {{ getProgress(course.completedLessons, course.totalLessons) }}% Done
            </div>
          </div>

          <!-- Titles -->
          <div>
            <h4 class="text-base font-bold text-foreground line-clamp-1">
              {{ course.title }}
            </h4>
            <p class="text-xs text-muted-foreground mt-1">
              {{ course.completedLessons }} of {{ course.totalLessons }} lessons
            </p>
          </div>
        </div>

        <!-- Progress and Button -->
        <div class="space-y-4 mt-6">
          <div class="w-full bg-secondary/80 rounded-full h-1.5 overflow-hidden">
            <div
              class="bg-primary h-full rounded-full transition-all duration-700"
              :style="{ width: `${getProgress(course.completedLessons, course.totalLessons)}%` }"
            />
          </div>

          <Button
            @click="goToCoursePlayer(course.id)"
            variant="outline"
            class="w-full text-xs font-semibold rounded-xl flex items-center justify-center gap-2 hover:bg-primary hover:text-primary-foreground border-primary/30 text-primary"
          >
            <PhPlay class="h-3 w-3" weight="fill" />
            Resume Course
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
