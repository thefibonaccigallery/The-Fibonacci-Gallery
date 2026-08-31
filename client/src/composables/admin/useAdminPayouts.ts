import { computed } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'
import axiosInstance from '@/api/axiosInstance'
import type {
  AffiliatePayout,
  PaginatedAffiliateResponse,
} from '@/types/AffiliateTypes'
import { useLanguageStore } from '@/stores/languageStore'

export const useAdminPayouts = () => {
  const queryClient = useQueryClient()
  const languageStore = useLanguageStore()
  const lang = computed(() => languageStore.translations.adminPayouts)

  // ── Fetch paginated payout requests ──
  const fetchPayouts = async (params: {
    page?: number
    per_page?: number
    search?: string
    status?: string
  }) => {
    const res = await axiosInstance.get<PaginatedAffiliateResponse<AffiliatePayout>>(
      '/admin/affiliate/payouts',
      { params },
    )
    return res.data
  }

  // ── Approve Payout Mutation ──
  const { mutate: approvePayout, isPending: isApproving } = useMutation({
    mutationFn: async (uuid: string) => {
      const res = await axiosInstance.post(`/admin/affiliate/payouts/${uuid}/approve`)
      return res.data
    },
    onSuccess: () => {
      toast.success(lang.value.toasts.approved)
      queryClient.invalidateQueries({ queryKey: ['admin-affiliate-payouts'] })
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || lang.value.toasts.approveFailed)
    },
  })

  // ── Mark Complete Mutation ──
  const { mutate: completePayout, isPending: isCompleting } = useMutation({
    mutationFn: async ({ uuid, admin_note }: { uuid: string; admin_note?: string }) => {
      const res = await axiosInstance.post(`/admin/affiliate/payouts/${uuid}/complete`, {
        admin_note,
      })
      return res.data
    },
    onSuccess: () => {
      toast.success(lang.value.toasts.completed)
      queryClient.invalidateQueries({ queryKey: ['admin-affiliate-payouts'] })
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || lang.value.toasts.completeFailed)
    },
  })

  // ── Reject Payout Mutation ──
  const { mutate: rejectPayout, isPending: isRejecting } = useMutation({
    mutationFn: async ({ uuid, admin_note }: { uuid: string; admin_note?: string }) => {
      const res = await axiosInstance.post(`/admin/affiliate/payouts/${uuid}/reject`, {
        admin_note,
      })
      return res.data
    },
    onSuccess: () => {
      toast.success(lang.value.toasts.rejected)
      queryClient.invalidateQueries({ queryKey: ['admin-affiliate-payouts'] })
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || lang.value.toasts.rejectFailed)
    },
  })

  return {
    fetchPayouts,
    approvePayout,
    completePayout,
    rejectPayout,
    isApproving,
    isCompleting,
    isRejecting,
  }
}
