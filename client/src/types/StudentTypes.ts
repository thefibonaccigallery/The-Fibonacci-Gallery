import type { PaginatedResponse } from '@/types/CourseTypes'

export interface Student {
  uuid: string
  name: string
  email: string
  country: string | null
  enrollments_count: number
  created_at: string
  updated_at: string
}

export type StudentsResponse = PaginatedResponse<Student>
