export type CommissionStatus = 'available' | 'paid_out'
export type PayoutStatus = 'requested' | 'approved' | 'rejected' | 'completed'

export interface AffiliateSummary {
  total_earned: number
  available_balance: number
  paid_out: number
  total_referrals: number
  referral_code: string
  referral_link: string
  commission_rate: number
}

export interface AffiliateReferredUser {
  id: number
  uuid?: string
  name: string
  email: string
  country?: string
}

export interface AffiliateOrderCourse {
  id: number
  uuid?: string
  title: string
}

export interface AffiliateOrder {
  id: number
  uuid: string
  amount: string | number
  course_id: number
  created_at: string
  course?: AffiliateOrderCourse
}

export interface AffiliateCommission {
  id: number
  uuid: string
  affiliate_id: number
  referred_user_id: number
  order_id: number
  order_amount: string | number
  commission_rate: string | number
  amount: string | number
  status: CommissionStatus
  created_at: string
  updated_at: string
  referred_user?: AffiliateReferredUser
  order?: AffiliateOrder
}

export interface AffiliatePayout {
  id: number
  uuid: string
  affiliate_id: number
  amount: string | number
  wallet_address: string
  network: string
  status: PayoutStatus
  admin_note: string | null
  processed_at: string | null
  created_at: string
  updated_at: string
  commissions_count?: number
  affiliate?: AffiliateReferredUser
  commissions?: AffiliateCommission[]
}

export interface PayoutRequestPayload {
  wallet_address: string
  network: string
}

export interface AdminPayoutActionPayload {
  uuid: string
  admin_note?: string
}

export interface PaginatedAffiliateResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string
  from: number | null
  last_page: number
  last_page_url: string
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number | null
  total: number
}
