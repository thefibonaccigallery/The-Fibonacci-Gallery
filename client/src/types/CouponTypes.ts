import type { PaginatedResponse } from '@/types/CourseTypes'

export interface CouponCourse {
  uuid: string
  title: string
}

export interface Coupon {
  uuid: string
  code: string
  discount_percentage: string
  max_uses: number | null
  expires_at: string | null
  is_active: boolean
  show_on_homepage: boolean
  created_at: string
  updated_at: string
  course: CouponCourse
  enrollments_count: number
}

export interface CouponUsageUser {
  uuid: string
  name: string
  email: string
}

export interface CouponUsageEnrollment {
  uuid: string
  enrolled_at: string
  user: CouponUsageUser
}

export interface CouponUsageResponse {
  coupon: Coupon
  enrollments: PaginatedResponse<CouponUsageEnrollment>
}

export interface CouponValidationResult {
  valid: boolean
  message?: string
  coupon_uuid?: string
  code?: string
  discount_percentage?: number
  discount_amount?: number
  final_price?: number
  is_free?: boolean
}

export type CouponsResponse = PaginatedResponse<Coupon>
