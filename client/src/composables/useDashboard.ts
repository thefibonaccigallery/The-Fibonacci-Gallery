import { ref, computed } from 'vue'
import type { Student, EnrolledCourse, ExploreCourse, DashboardTab } from '@/types/DashboardType'

export function useDashboard() {
  // Static state
  const student = ref<Student>({
    name: 'John',
  })

  const enrolledCourses = ref<EnrolledCourse[]>([
    { id: 1, title: 'Forex Fundamentals', totalLessons: 39, completedLessons: 6, thumbnail: null },
    {
      id: 2,
      title: 'Technical Analysis Mastery',
      totalLessons: 24,
      completedLessons: 20,
      thumbnail: null,
    },
  ])

  const exploreCourses = ref<ExploreCourse[]>([
    {
      id: 3,
      title: 'Price Action Trading',
      totalLessons: 18,
      description: 'Learn to read charts without indicators',
      thumbnail: null,
    },
    {
      id: 4,
      title: 'Risk Management',
      totalLessons: 12,
      description: 'Protect your capital and grow consistently',
      thumbnail: null,
    },
    {
      id: 5,
      title: 'Trading Psychology',
      totalLessons: 15,
      description: 'Master the mental side of trading',
      thumbnail: null,
    },
  ])

  // Tab State
  const activeTab = ref<DashboardTab>('home')

  const setActiveTab = (tab: DashboardTab) => {
    activeTab.value = tab
  }

  // Date state
  const currentDate = computed(() => {
    const options: Intl.DateTimeFormatOptions = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }
    return new Date().toLocaleDateString('en-US', options)
  })

  // Most prominent course to continue learning (e.g. lowest progress or recently accessed)
  const continueLearningCourse = computed(() => {
    return enrolledCourses.value[0] || null
  })

  return {
    student,
    enrolledCourses,
    exploreCourses,
    activeTab,
    setActiveTab,
    currentDate,
    continueLearningCourse,
  }
}
