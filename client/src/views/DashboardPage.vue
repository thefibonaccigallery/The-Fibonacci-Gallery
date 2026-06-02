<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import WelcomeBar from '@/components/dashboard/WelcomeBar.vue'
import StatsRow from '@/components/dashboard/StatsRow.vue'
import ContinueLearning from '@/components/dashboard/ContinueLearning.vue'
import MyCourses from '@/components/dashboard/MyCourses.vue'
import ExploreCourses from '@/components/dashboard/ExploreCourses.vue'

const { student, currentDate } = useDashboard()
</script>

<template>
  <DashboardLayout>
    <div class="w-full px-6 lg:px-10 pt-10 pb-24 md:pb-12">
      <WelcomeBar :studentName="student.name" :currentDate="currentDate" />
      <StatsRow />
      <ContinueLearning />
      <MyCourses />
      <ExploreCourses />
    </div>
  </DashboardLayout>
</template>
