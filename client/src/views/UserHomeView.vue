<script setup lang="ts">
import { computed } from 'vue'
import { useDashboard } from '@/composables/useDashboard'
import { useUserCourses } from '@/composables/useUserCourses'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import BottomNav from '@/components/dashboard/BottomNav.vue'
import WelcomeBar from '@/components/dashboard/WelcomeBar.vue'
import StatsRow from '@/components/dashboard/StatsRow.vue'
import ContinueLearning from '@/components/dashboard/ContinueLearning.vue'
import MyCourses from '@/components/dashboard/MyCourses.vue'
import ExploreCourses from '@/components/dashboard/ExploreCourses.vue'

const { student, activeTab, setActiveTab, currentDate } = useDashboard()

// Fetch enrolled courses once here — MyCourses uses the same queryKey
// so TanStack Query deduplicates the request automatically
const { useMyEnrolledCourses } = useUserCourses()
const { data: enrolledData, isLoading: statsLoading } = useMyEnrolledCourses()

const courses = computed(() => enrolledData.value?.courses ?? [])

const coursesEnrolled = computed(() => courses.value.length)
const lessonsCompleted = computed(() =>
  courses.value.reduce((sum, c) => sum + (c.completedLessons ?? 0), 0),
)
const totalLessons = computed(() =>
  courses.value.reduce((sum, c) => sum + (c.totalLessons ?? 0), 0),
)
const overallProgress = computed(() =>
  totalLessons.value > 0 ? Math.round((lessonsCompleted.value / totalLessons.value) * 100) : 0,
)
</script>

<template>
  <div class="h-screen overflow-hidden bg-background flex flex-col md:flex-row font-primary">
    <!-- Desktop Sidebar -->
    <AppSidebar :activeTab="activeTab" @update:activeTab="setActiveTab" />

    <!-- Main Content Area -->
    <main class="flex-1 h-screen overflow-y-auto w-full relative">
      <div class="w-full px-6 lg:px-10 pt-10 pb-24 md:pb-12">
        <!-- Dashboard Content (only visible when 'home' tab is active) -->
        <div v-if="activeTab === 'home'" class="w-full">
          <WelcomeBar :studentName="student.name" :currentDate="currentDate" />
          <StatsRow
            :isLoading="statsLoading"
            :coursesEnrolled="coursesEnrolled"
            :lessonsCompleted="lessonsCompleted"
            :totalLessons="totalLessons"
            :overallProgress="overallProgress"
          />
          <ContinueLearning />
          <MyCourses />
          <ExploreCourses />
        </div>

        <!-- Placeholder for other tabs -->
        <div
          v-else
          class="animate-fade-in-up flex flex-col items-center justify-center py-32 text-center"
        >
          <h2 class="text-2xl font-bold tracking-tight text-foreground mb-2">
            {{ activeTab.charAt(0).toUpperCase() + activeTab.slice(1) }} Section
          </h2>
          <p class="text-muted-foreground text-sm">This section is under construction.</p>
        </div>
      </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <BottomNav :activeTab="activeTab" @update:activeTab="setActiveTab" />
  </div>
</template>

