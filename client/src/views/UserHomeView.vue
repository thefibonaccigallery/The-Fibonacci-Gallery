<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import BottomNav from '@/components/dashboard/BottomNav.vue'
import WelcomeBar from '@/components/dashboard/WelcomeBar.vue'
import StatsRow from '@/components/dashboard/StatsRow.vue'
import ContinueLearning from '@/components/dashboard/ContinueLearning.vue'
import MyCourses from '@/components/dashboard/MyCourses.vue'
import ExploreCourses from '@/components/dashboard/ExploreCourses.vue'

const {
  student,
  enrolledCourses,
  exploreCourses,
  activeTab,
  setActiveTab,
  currentDate,
  continueLearningCourse,
} = useDashboard()
</script>

<template>
  <div class="min-h-screen bg-background flex flex-col md:flex-row font-primary">
    <!-- Desktop Sidebar -->
    <AppSidebar
      :activeTab="activeTab"
      @update:activeTab="setActiveTab"
    />

    <!-- Main Content Area -->
    <main class="flex-1 md:ml-64 w-full relative">
      <div class="max-w-6xl mx-auto px-6 pt-10 pb-24 md:pb-12">
        <!-- Dashboard Content (only visible when 'home' tab is active, as requested for static demo) -->
        <div v-if="activeTab === 'home'" class="w-full">
          <WelcomeBar :studentName="student.name" :currentDate="currentDate" />
          <StatsRow />
          <ContinueLearning :course="continueLearningCourse" />
          <MyCourses :courses="enrolledCourses" />
          <ExploreCourses :courses="exploreCourses" />
        </div>

        <!-- Placeholder for other tabs -->
        <div v-else class="animate-fade-in-up flex flex-col items-center justify-center py-32 text-center">
          <h2 class="text-2xl font-bold tracking-tight text-foreground mb-2">
            {{ activeTab.charAt(0).toUpperCase() + activeTab.slice(1) }} Section
          </h2>
          <p class="text-muted-foreground text-sm">
            This section is under construction.
          </p>
        </div>
      </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <BottomNav
      :activeTab="activeTab"
      @update:activeTab="setActiveTab"
    />
  </div>
</template>
