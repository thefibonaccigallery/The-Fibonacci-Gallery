import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import type { Order, OrderStatus } from '@/types/OrderTypes'
import type { Student } from '@/types/StudentTypes'

export interface AdminDashboardStats {
  totalStudents: number
  totalCourses: number
  totalRevenue: number
  totalOrders: number
  studentsTrend?: string
  coursesTrend?: string
  revenueTrend?: string
  ordersTrend?: string
}

export interface AdminDashboardResponse {
  success: boolean
  stats: AdminDashboardStats
  recentOrders: Order[]
  recentStudents: Student[]
}

export type { Order, Student, OrderStatus }

export function useAdminDashboard() {
  const { data, isLoading, isError, error, refetch } = useQuery<AdminDashboardResponse>({
    queryKey: ['admin-dashboard'],
    queryFn: async () => {
      const response = await axiosInstance.get<AdminDashboardResponse>('/admin/dashboard')
      return response.data
    },
  })

  const stats = computed<AdminDashboardStats>(() => data.value?.stats ?? {
    totalStudents: 0,
    totalCourses: 0,
    totalRevenue: 0,
    totalOrders: 0,
  })

  const recentOrders = computed<Order[]>(() => data.value?.recentOrders ?? [])
  const recentStudents = computed<Student[]>(() => data.value?.recentStudents ?? [])

  const getStatusColor = (status: OrderStatus) => {
    const statusColors: Record<string, string> = {
      waiting: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
      confirming: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
      confirmed: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
      sending: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
      finished: 'bg-green-500/10 text-green-600 dark:text-green-400',
      failed: 'bg-destructive/10 text-destructive',
      expired: 'bg-muted text-muted-foreground',
      partially_paid: 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
      refunded: 'bg-muted text-muted-foreground',
    }
    return statusColors[status] || 'bg-muted text-muted-foreground'
  }

  const getStatusLabel = (status: OrderStatus) => {
    const labels: Record<string, string> = {
      waiting: 'Waiting',
      confirming: 'Confirming',
      confirmed: 'Confirmed',
      sending: 'Sending',
      finished: 'Finished',
      failed: 'Failed',
      expired: 'Expired',
      partially_paid: 'Partially Paid',
      refunded: 'Refunded',
    }
    return labels[status] || status
  }

  const formatCurrency = (amount: number | string, currency: string = 'USD') => {
    const numericAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency,
    }).format(numericAmount)
  }

  const formatDate = (dateString: string) => {
    if (!dateString) return ''
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(new Date(dateString))
  }

  return {
    data,
    stats,
    recentOrders,
    recentStudents,
    isLoading,
    isError,
    error,
    refetch,
    getStatusColor,
    getStatusLabel,
    formatCurrency,
    formatDate,
  }
}
