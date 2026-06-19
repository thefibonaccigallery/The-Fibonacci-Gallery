import type { PaginatedResponse } from '@/types/CourseTypes'

export type OrderStatus =
  | 'waiting'
  | 'confirming'
  | 'confirmed'
  | 'sending'
  | 'partially_paid'
  | 'finished'
  | 'failed'
  | 'expired'
  | 'refunded'

export interface OrderUser {
  uuid: string
  name: string
  email: string
}

export interface OrderCourse {
  uuid: string
  title: string
}

export interface Order {
  uuid: string
  amount: string
  currency: string
  pay_currency: string | null
  pay_address: string | null
  pay_amount: string | null
  actually_paid: string
  status: OrderStatus
  paid_at: string | null
  created_at: string
  updated_at: string
  user: OrderUser
  course: OrderCourse
}

export type OrdersResponse = PaginatedResponse<Order>
