<script setup lang="ts">
import type { ExploreCourse } from '@/types/DashboardType'
import { Button } from '@/components/ui/button'

defineProps<{
  courses: ExploreCourse[]
}>()
</script>

<template>
  <div class="animate-fade-in-up mb-10 pb-20 md:pb-6" style="animation-delay: 750ms">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
        Explore More Courses
      </h3>
    </div>

    <!-- Scrollable container on mobile, grids on larger screens -->
    <div class="flex overflow-x-auto pb-4 gap-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:overflow-x-visible md:pb-0 scrollbar-none snap-x">
      <div
        v-for="course in courses"
        :key="course.id"
        class="snap-start shrink-0 w-72 md:w-auto liquid-glass rounded-2xl p-5 border border-border/20 flex flex-col justify-between transition-transform duration-300 hover:scale-[1.01]"
      >
        <!-- Card content -->
        <div class="space-y-4">
          <!-- Thumbnail -->
          <div class="relative w-full h-32 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center">
            <svg class="absolute inset-0 w-full h-full opacity-30" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Geometric background -->
              <circle cx="50" cy="50" r="40" stroke="rgba(34, 197, 94, 0.1)" stroke-width="0.5" stroke-dasharray="2 2" />
              <polygon points="50,10 90,90 10,90" stroke="var(--primary)" stroke-width="0.5" fill="none" />
              <circle cx="50" cy="63.3" r="26.7" stroke="var(--primary)" stroke-width="0.5" fill="none" />
            </svg>
            <div class="absolute top-3 right-3 bg-secondary/80 backdrop-blur-md px-2 py-0.5 rounded text-[10px] font-bold text-muted-foreground border border-border/20">
              {{ course.totalLessons }} Lessons
            </div>
          </div>

          <!-- Titles -->
          <div>
            <h4 class="text-base font-bold text-foreground line-clamp-1">
              {{ course.title }}
            </h4>
            <p class="text-xs text-muted-foreground mt-2 line-clamp-2">
              {{ course.description }}
            </p>
          </div>
        </div>

        <!-- Button -->
        <div class="mt-6">
          <Button class="w-full text-xs font-semibold rounded-xl hover:bg-primary/90 text-primary-foreground bg-primary transition-colors">
            Enroll Now
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
