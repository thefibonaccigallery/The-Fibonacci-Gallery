import type { PaginatedResponse } from '@/types/CourseTypes'

export interface EnrollmentUser {
  uuid: string
  name: string
  email: string
}

export interface EnrollmentCourse {
  uuid: string
  title: string
  level: string
}

export type EnrollmentType = 'free' | 'paid' | 'coupon'

export interface Enrollment {
  uuid: string
  type: EnrollmentType
  progress_percentage: string
  enrolled_at: string
  completed_at: string | null
  user: EnrollmentUser
  course: EnrollmentCourse
}

export type EnrollmentsResponse = PaginatedResponse<Enrollment>
