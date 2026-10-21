<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserCourses } from '@/composables/useUserCourses'
import { Button } from '@/components/ui/button'
import { PhSpinner } from '@phosphor-icons/vue'

const router = useRouter()
const { useExploreCourses, useEnrollCourse } = useUserCourses()

const { data, isLoading } = useExploreCourses()
const { mutate: enrollCourse, isPending: isEnrolling } = useEnrollCourse()

const courses = computed(() => data.value?.courses?.slice(0, 3) || [])

const handleEnroll = (courseUuid: string) => {
  router.push(`/checkout/${courseUuid}`)
}

const viewAllCourses = () => {
  router.push('/explore')
}
</script>

<template>
  <div class="animate-fade-in-up  mb-10 pb-20 md:pb-6" style="animation-delay: 750ms">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
        Explore More Courses
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
      <p class="text-muted-foreground">No courses available to explore at the moment</p>
    </div>

    <!-- Scrollable container on mobile, grids on larger screens -->
    <div
      v-else
      class="flex overflow-x-auto pb-4 gap-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:overflow-x-visible md:pb-0 scrollbar-none snap-x"
    >
      <div
        v-for="course in courses"
        :key="course.uuid"
        class="snap-start shrink-0 w-72 md:w-auto liquid-glass rounded-2xl p-5 border border-border/20 flex flex-col justify-between transition-transform duration-300 hover:scale-[1.01]"
      >
        <!-- Card content -->
        <div class="space-y-4">
          <!-- Thumbnail -->
          <div
            class="relative w-full h-32 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center"
          >
            <img
              v-if="course.thumbnail_url"
              :src="course.thumbnail_url"
              :alt="course.title"
              class="w-full h-full object-cover"
            />
            <svg
              v-else
              class="absolute inset-0 w-full h-full opacity-30"
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
              class="absolute top-3 right-3 bg-secondary/80 backdrop-blur-md px-2 py-0.5 rounded text-[10px] font-bold text-muted-foreground border border-border/20"
            >
              {{ course.totalLessons }} Lessons
            </div>
          </div>

          <!-- Titles -->
          <div>
            <h4 class="text-base font-bold text-foreground line-clamp-1">
              {{ course.title }}
            </h4>
            <p class="text-xs text-muted-foreground mt-2 line-clamp-2">
              {{ course.description || 'No description available' }}
            </p>
          </div>
        </div>

        <!-- Button -->
        <div class="mt-6">
          <Button
            @click="handleEnroll(course.uuid)"
            class="w-full text-xs font-semibold rounded-xl hover:bg-primary/90 text-primary-foreground bg-primary transition-colors"
          >
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
