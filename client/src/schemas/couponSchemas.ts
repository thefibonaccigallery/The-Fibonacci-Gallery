import { z } from 'zod'

export const couponSchema = z.object({
  course_uuid: z.string().min(1, 'Please select a course'),
  code: z
    .string()
    .min(1, 'Coupon code is required')
    .max(50, 'Code must be less than 50 characters'),
  discount_percentage: z
    .number({ invalid_type_error: 'Must be a number' })
    .min(1, 'Discount must be at least 1%')
    .max(100, 'Discount cannot exceed 100%'),
  max_uses: z
    .number({ invalid_type_error: 'Must be a number' })
    .int('Must be a whole number')
    .min(1, 'Must allow at least 1 use')
    .nullable()
    .optional(),
  expires_at: z.string().nullable().optional(),
  is_active: z.boolean().default(true),
  show_on_homepage: z.boolean().default(false).optional(),
})

export type CouponFormState = z.infer<typeof couponSchema>
export type CouponFormField = keyof CouponFormState
