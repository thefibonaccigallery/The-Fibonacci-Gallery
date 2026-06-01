<script setup lang="ts">
import { computed } from 'vue'
import type { EnrolledCourse } from '@/types/DashboardType'
import { Button } from '@/components/ui/button'
import { PhPlay } from '@phosphor-icons/vue'

defineProps<{
  courses: EnrolledCourse[]
}>()

const getProgress = (completed: number, total: number) => {
  return Math.round((completed / total) * 100)
}
</script>

<template>
  <div class="animate-fade-in-up mb-10" style="animation-delay: 600ms">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
        My Enrolled Courses
      </h3>
    </div>

    <!-- Scrollable container on mobile, grids on larger screens -->
    <div
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
            <svg
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
