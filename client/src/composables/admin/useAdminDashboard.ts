import { ref, computed } from 'vue'

export interface DashboardStats {
  totalStudents: number
  totalCourses: number
  totalRevenue: number
  totalOrders: number
}

export interface Order {
  id: number
  student: string
  course: string
  amount: number
  currency: string
  payCurrency: string
  status: 'waiting' | 'confirming' | 'finished' | 'failed' | 'expired' | 'partially_paid'
  date: string
}

export interface Student {
  id: number
  name: string
  email: string
  country: string
  enrolledCourses: number
  joinedAt: string
}

export function useAdminDashboard() {
  const stats = ref<DashboardStats>({
    totalStudents: 124,
    totalCourses: 5,
    totalRevenue: 8450.0,
    totalOrders: 198,
  })

  const recentOrders = ref<Order[]>([
    {
      id: 1,
      student: 'John Doe',
      course: 'Forex Fundamentals',
      amount: 99.0,
      currency: 'USD',
      payCurrency: 'BTC',
      status: 'finished',
      date: '2026-05-01',
    },
    {
      id: 2,
      student: 'Sara Ali',
      course: 'Technical Analysis',
      amount: 79.0,
      currency: 'USD',
      payCurrency: 'ETH',
      status: 'confirming',
      date: '2026-05-03',
    },
    {
      id: 3,
      student: 'Mike Chen',
      course: 'Price Action',
      amount: 59.0,
      currency: 'USD',
      payCurrency: 'USDT',
      status: 'waiting',
      date: '2026-05-05',
    },
    {
      id: 4,
      student: 'Aisha Bekele',
      course: 'Risk Management',
      amount: 49.0,
      currency: 'USD',
      payCurrency: 'BTC',
      status: 'failed',
      date: '2026-05-06',
    },
    {
      id: 5,
      student: 'Luca Ferrari',
      course: 'Trading Psychology',
      amount: 69.0,
      currency: 'USD',
      payCurrency: 'ETH',
      status: 'partially_paid',
      date: '2026-05-07',
    },
  ])

  const recentStudents = ref<Student[]>([
    {
      id: 1,
      name: 'John Doe',
      email: 'john@example.com',
      country: 'Ethiopia',
      enrolledCourses: 2,
      joinedAt: '2026-04-10',
    },
    {
      id: 2,
      name: 'Sara Ali',
      email: 'sara@example.com',
      country: 'Kenya',
      enrolledCourses: 1,
      joinedAt: '2026-04-18',
    },
    {
      id: 3,
      name: 'Mike Chen',
      email: 'mike@example.com',
      country: 'China',
      enrolledCourses: 3,
      joinedAt: '2026-04-22',
    },
    {
      id: 4,
      name: 'Aisha Bekele',
      email: 'aisha@example.com',
      country: 'Ethiopia',
      enrolledCourses: 1,
      joinedAt: '2026-05-01',
    },
    {
      id: 5,
      name: 'Luca Ferrari',
      email: 'luca@example.com',
      country: 'Italy',
      enrolledCourses: 2,
      joinedAt: '2026-05-04',
    },
  ])

  const getStatusColor = (status: Order['status']) => {
    const statusColors = {
      waiting: 'muted',
      confirming: 'yellow',
      finished: 'primary',
      failed: 'destructive',
      expired: 'muted',
      partially_paid: 'orange',
    }
    return statusColors[status]
  }

  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency,
    }).format(amount)
  }

  const formatDate = (dateString: string) => {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(new Date(dateString))
  }

  return {
    stats,
    recentOrders,
    recentStudents,
    getStatusColor,
    formatCurrency,
    formatDate,
  }
}
