import { ref } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type { CouponValidationResult } from '@/types/CouponTypes'

export function useCoupon() {
  const queryClient = useQueryClient()
  const validationResult = ref<CouponValidationResult | null>(null)
  const isValidating = ref(false)
  const validationError = ref<string | null>(null)

  const validateCoupon = async (code: string, courseUuid: string) => {
    isValidating.value = true
    validationError.value = null
    validationResult.value = null

    try {
      const response = await axiosInstance.post('/coupons/validate', {
        code,
        course_uuid: courseUuid,
      })
      validationResult.value = response.data
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Invalid coupon code'
      validationError.value = msg
      validationResult.value = { valid: false, message: msg }
    } finally {
      isValidating.value = false
    }
  }

  const clearValidation = () => {
    validationResult.value = null
    validationError.value = null
  }

  const { mutate: enrollWithCoupon, isPending: isEnrolling } = useMutation({
    mutationFn: async ({ courseUuid, couponCode }: { courseUuid: string; couponCode: string }) => {
      const response = await axiosInstance.post(`/courses/${courseUuid}/enroll`, {
        coupon_code: couponCode,
      })
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['my-courses'] })
      queryClient.invalidateQueries({ queryKey: ['course-detail'] })
      toast.success('Successfully enrolled in the course!')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to enroll with coupon')
    },
  })

  return {
    validationResult,
    isValidating,
    validationError,
    validateCoupon,
    clearValidation,
    enrollWithCoupon,
    isEnrolling,
  }
}
