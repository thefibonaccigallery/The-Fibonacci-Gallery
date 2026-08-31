import { ref, computed } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { useForm } from '@tanstack/vue-form'
import { toast } from 'vue-sonner'
import axiosInstance from '@/api/axiosInstance'
import {
  usePayoutRequestSchema,
  type PayoutRequestFormField,
  type PayoutRequestFormState,
} from '@/schemas/affiliateSchemas'
import { validateFormWithZod, normalizeErrors, type NormalizedErrors } from '@/utils/errorFormatter'
import type {
  AffiliateSummary,
  AffiliateCommission,
  AffiliatePayout,
  PaginatedAffiliateResponse,
} from '@/types/AffiliateTypes'
import { useLanguageStore } from '@/stores/languageStore'

export const useAffiliate = () => {
  const queryClient = useQueryClient()
  const languageStore = useLanguageStore()
  const lang = computed(() => languageStore.translations.affiliate)

  const errors = ref<NormalizedErrors<PayoutRequestFormField>>({})
  const isPayoutDialogOpen = ref(false)

  // ── TanStack useForm for Payout Request ──
  const payoutForm = useForm({
    defaultValues: {
      wallet_address: '',
      network: 'USDT BEP20',
    } as PayoutRequestFormState,
    validators: {
      onChange: usePayoutRequestSchema.value,
    },
  })

  function clearError(field: PayoutRequestFormField) {
    if (errors.value && errors.value[field]) {
      delete errors.value[field]
    }
  }

  function resetPayoutForm() {
    payoutForm.reset()
    errors.value = {}
  }

  // ── Query: Affiliate Dashboard Summary ──
  const {
    data: summary,
    isLoading: isSummaryLoading,
    error: summaryError,
    refetch: refetchSummary,
  } = useQuery({
    queryKey: ['affiliate-summary'],
    queryFn: async () => {
      const res = await axiosInstance.get<AffiliateSummary>('/affiliate/dashboard')
      return res.data
    },
  })

  // ── Query: Paginated Commissions ──
  const fetchCommissions = async (params: { page?: number; per_page?: number; status?: string }) => {
    const res = await axiosInstance.get<PaginatedAffiliateResponse<AffiliateCommission>>(
      '/affiliate/commissions',
      { params },
    )
    return res.data
  }

  // ── Query: Paginated Payouts ──
  const fetchPayouts = async (params: { page?: number; per_page?: number; status?: string }) => {
    const res = await axiosInstance.get<PaginatedAffiliateResponse<AffiliatePayout>>(
      '/affiliate/payouts',
      { params },
    )
    return res.data
  }

  // ── Mutation: Submit Payout Request ──
  const { mutate: submitPayoutRequest, isPending: isSubmittingPayout } = useMutation({
    mutationFn: async (payload: PayoutRequestFormState) => {
      const res = await axiosInstance.post('/affiliate/payouts', payload)
      return res.data
    },
    onSuccess: () => {
      toast.success(lang.value.toasts.requestSuccess)
      queryClient.invalidateQueries({ queryKey: ['affiliate-summary'] })
      queryClient.invalidateQueries({ queryKey: ['affiliate-commissions'] })
      queryClient.invalidateQueries({ queryKey: ['affiliate-payouts'] })
      isPayoutDialogOpen.value = false
      resetPayoutForm()
    },
    onError: (err: any) => {
      if (err.response?.data?.errors) {
        errors.value = normalizeErrors<PayoutRequestFormField>(err.response.data.errors)
      } else {
        toast.error(err.response?.data?.message || lang.value.toasts.requestFailed)
      }
    },
  })

  // ── Form Submit Handler ──
  const handlePayoutSubmit = () => {
    errors.value = {}
    const error = validateFormWithZod<PayoutRequestFormField>(
      usePayoutRequestSchema.value,
      payoutForm.state.values,
    )
    if (error) {
      errors.value = error
      return
    }

    submitPayoutRequest(payoutForm.state.values as PayoutRequestFormState)
  }

  return {
    // State & forms
    summary,
    isSummaryLoading,
    summaryError,
    payoutForm,
    errors,
    isPayoutDialogOpen,
    isSubmittingPayout,

    // Actions
    clearError,
    resetPayoutForm,
    handlePayoutSubmit,
    fetchCommissions,
    fetchPayouts,
    refetchSummary,
  }
}
