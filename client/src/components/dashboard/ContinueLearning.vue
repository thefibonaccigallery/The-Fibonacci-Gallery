<script setup lang="ts">
import { computed } from 'vue'
import type { EnrolledCourse } from '@/types/DashboardType'
import { Button } from '@/components/ui/button'
import { PhPlay } from '@phosphor-icons/vue'

const props = defineProps<{
  course: EnrolledCourse | null
}>()

const progressPercentage = computed(() => {
  if (!props.course) return 0
  return Math.round((props.course.completedLessons / props.course.totalLessons) * 100)
})
</script>

<template>
  <div v-if="course" class="animate-fade-in-up mb-8" style="animation-delay: 450ms">
    <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">
      Continue Learning
    </h3>
    <div class="liquid-glass rounded-2xl p-6 border border-border/20 flex flex-col lg:flex-row gap-6 items-center transition-all duration-300 hover:shadow-lg hover:shadow-primary/5">
      <!-- Premium Fibonacci spiral graphic placeholder -->
      <div class="relative w-full lg:w-48 h-32 rounded-xl overflow-hidden bg-zinc-950 border border-primary/20 flex items-center justify-center group shrink-0">
        <svg class="absolute inset-0 w-full h-full opacity-60 transition-transform duration-700 group-hover:scale-110" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Background grids -->
          <line x1="0" y1="50" x2="100" y2="50" stroke="rgba(34, 197, 94, 0.1)" stroke-width="0.5" />
          <line x1="50" y1="0" x2="50" y2="100" stroke="rgba(34, 197, 94, 0.1)" stroke-width="0.5" />
          <!-- Glowing Fibonacci Golden Spiral -->
          <path d="M50,50 A1,1 0 0,1 51,50 A1,1 0 0,1 51,51 A2,2 0 0,1 49,51 A3,3 0 0,1 49,48 A5,5 0 0,1 54,48 A8,8 0 0,1 54,56 A13,13 0 0,1 41,56 A21,21 0 0,1 41,35 A34,34 0 0,1 75,35" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" class="animate-pulse" />
        </svg>
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-3">
          <span class="text-[10px] uppercase font-bold tracking-widest text-primary bg-primary/10 px-2 py-0.5 rounded border border-primary/20">
            Active
          </span>
        </div>
      </div>

      <!-- Course Details -->
      <div class="flex-1 w-full space-y-4">
        <div>
          <h4 class="text-lg md:text-xl font-bold text-foreground">
            {{ course.title }}
          </h4>
          <p class="text-xs text-muted-foreground mt-1">
            {{ course.completedLessons }} of {{ course.totalLessons }} lessons completed ({{ progressPercentage }}%)
          </p>
        </div>

        <!-- Progress bar -->
        <div class="space-y-1">
          <div class="w-full bg-secondary/80 rounded-full h-2.5 overflow-hidden border border-border/20">
            <div
              class="bg-primary h-full rounded-full transition-all duration-1000 ease-out"
              :style="{ width: `${progressPercentage}%` }"
            />
          </div>
        </div>

        <!-- Button -->
        <div class="flex items-center justify-between gap-4 pt-2">
          <span class="text-xs text-muted-foreground font-medium">
            Next Lesson: Fibonacci Retracement Golden Ratios
          </span>
          <Button class="bg-primary hover:bg-primary/90 text-primary-foreground font-semibold rounded-xl px-5 flex items-center gap-2">
            <PhPlay class="h-4 w-4" weight="fill" />
            Resume
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
