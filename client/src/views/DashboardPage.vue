<script setup lang="ts">
import { computed } from 'vue'
import { useDashboard } from '@/composables/useDashboard'
import { useUserCourses } from '@/composables/useUserCourses'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import WelcomeBar from '@/components/dashboard/WelcomeBar.vue'
import StatsRow from '@/components/dashboard/StatsRow.vue'
import ContinueLearning from '@/components/dashboard/ContinueLearning.vue'
import MyCourses from '@/components/dashboard/MyCourses.vue'
import ExploreCourses from '@/components/dashboard/ExploreCourses.vue'

const { student, currentDate } = useDashboard()

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
  <DashboardLayout>
    <div class="w-full px-6 lg:px-10 pt-10 pb-24 md:pb-12">
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
  </DashboardLayout>
</template>
