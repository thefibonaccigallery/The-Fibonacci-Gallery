import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import { useLanguageStore } from '@/stores/languageStore'
import type {
  AnalyticsRange,
  AnalyticsResponse,
  AnalyticsData,
  AnalyticsRangeOption,
} from '@/types/analyticsType'

export function useAdminAnalytics() {
  const langStore = useLanguageStore()
  const currentRange = ref<AnalyticsRange>('30_days')

  const rangeOptions = computed<AnalyticsRangeOption[]>(() => [
    { value: 'today', label: langStore.translations.adminAnalytics.range.today },
    { value: '7_days', label: langStore.translations.adminAnalytics.range.sevenDays },
    { value: '30_days', label: langStore.translations.adminAnalytics.range.thirtyDays },
    { value: '90_days', label: langStore.translations.adminAnalytics.range.ninetyDays },
  ])

  const { data, isLoading, isError, error, refetch } = useQuery<AnalyticsResponse>({
    queryKey: ['admin-analytics', currentRange],
    queryFn: async () => {
      try {
        const response = await axiosInstance.get<AnalyticsResponse>('/admin/analytics', {
          params: { range: currentRange.value },
        })
        return response.data
      } catch (err: any) {
        const message =
          err.response?.data?.message || langStore.translations.adminAnalytics.toasts.fetchFailed
        toast.error(message)
        throw err
      }
    },
    staleTime: 5 * 60 * 1000,
    retry: 1,
  })

  const analyticsData = computed<AnalyticsData>(() => data.value?.data ?? {
    totalVisitors: 0,
    totalPageViews: 0,
    dailyMetrics: [],
    topPages: [],
    topCountries: [],
    topSources: [],
    deviceSplit: [],
    hasData: false,
    propertyUrl: undefined,
  })

  const hasData = computed<boolean>(() => !!analyticsData.value.hasData)

  const openGaDashboard = () => {
    const url = analyticsData.value.propertyUrl || 'https://analytics.google.com/'
    window.open(url, '_blank', 'noopener,noreferrer')
  }

  return {
    currentRange,
    rangeOptions,
    analyticsData,
    hasData,
    isLoading,
    isError,
    error,
    refetch,
    openGaDashboard,
  }
}
