import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { DashboardTab } from '@/types/DashboardType'

export function useDashboard() {
  const authStore = useAuthStore()

  // Real user name from auth store
  const student = computed(() => ({
    name: authStore.user?.name ?? '',
  }))

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

  return {
    student,
    activeTab,
    setActiveTab,
    currentDate,
  }
}
