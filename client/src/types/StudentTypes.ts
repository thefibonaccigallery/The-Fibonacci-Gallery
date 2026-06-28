import type { PaginatedResponse } from '@/types/CourseTypes'

export interface Student {
  uuid: string
  name: string
  email: string
  country: string | null
  enrollments_count: number
  is_banned: boolean
  banned_at: string | null
  ban_reason: string | null
  created_at: string
  updated_at: string
}

export type StudentsResponse = PaginatedResponse<Student>
