import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type { CouponsResponse, CouponUsageResponse, Coupon } from '@/types/CouponTypes'

export function useAdminCoupons() {
  const queryClient = useQueryClient()

  const fetchCoupons = async (params?: {
    page?: number
    per_page?: number
    search?: string
    course_uuid?: string
    is_active?: boolean
  }): Promise<CouponsResponse> => {
    const response = await axiosInstance.get('/admin/coupons', { params })
    return response.data
  }

  const fetchCoupon = async (uuid: string): Promise<{ coupon: Coupon }> => {
    const response = await axiosInstance.get(`/admin/coupons/${uuid}`)
    return response.data
  }

  const fetchCouponUsage = async (
    uuid: string,
    params?: { page?: number; per_page?: number },
  ): Promise<CouponUsageResponse> => {
    const response = await axiosInstance.get(`/admin/coupons/${uuid}/usage`, { params })
    return response.data
  }

  const { mutate: createCoupon, isPending: isCreating, error: createError } = useMutation({
    mutationFn: async (data: Record<string, unknown>) => {
      const response = await axiosInstance.post('/admin/coupons', data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-coupons'] })
      toast.success('Coupon created successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to create coupon')
    },
  })

  const { mutate: updateCoupon, isPending: isUpdating, error: updateError } = useMutation({
    mutationFn: async ({ uuid, data }: { uuid: string; data: Record<string, unknown> }) => {
      const response = await axiosInstance.put(`/admin/coupons/${uuid}`, data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-coupons'] })
      toast.success('Coupon updated successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update coupon')
    },
  })

  const { mutate: deleteCoupon, isPending: isDeleting } = useMutation({
    mutationFn: async (uuid: string) => {
      const response = await axiosInstance.delete(`/admin/coupons/${uuid}`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-coupons'] })
      toast.success('Coupon deleted successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to delete coupon')
    },
  })

  const { mutate: toggleCoupon, isPending: isToggling } = useMutation({
    mutationFn: async (uuid: string) => {
      const response = await axiosInstance.patch(`/admin/coupons/${uuid}/toggle`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-coupons'] })
      toast.success('Coupon status updated')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update coupon status')
    },
  })

  return {
    fetchCoupons,
    fetchCoupon,
    fetchCouponUsage,
    createCoupon,
    updateCoupon,
    deleteCoupon,
    toggleCoupon,
    isCreating,
    isUpdating,
    isDeleting,
    isToggling,
    createError,
    updateError,
  }
}
