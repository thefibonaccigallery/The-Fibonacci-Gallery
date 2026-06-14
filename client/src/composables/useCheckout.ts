import { ref, computed, watch, onUnmounted } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'

interface Course {
  uuid: string
  title: string
  level: string
  price: number
  thumbnail_url: string | null
}

interface PaymentDetails {
  payment_id: string
  pay_address: string
  pay_amount: number
  pay_currency: string
  order_uuid: string
  expiration_estimate_date: string
}

interface PaymentStatus {
  status:
    | 'waiting'
    | 'confirming'
    | 'confirmed'
    | 'sending'
    | 'partially_paid'
    | 'finished'
    | 'failed'
    | 'expired'
    | 'refunded'
  payment_amount: number
  actually_paid: number
}

const terminalStatuses = ['finished', 'failed', 'expired', 'refunded']

export function useCheckout() {
  const router = useRouter()
  const queryClient = useQueryClient()

  const currentStep = ref(1)
  const paymentDetails = ref<PaymentDetails | null>(null)
  const timeLeft = ref('')
  const qrDataUrl = ref('')

  // Fetch course details
  const useCourseDetails = (courseUuid: string) => {
    return useQuery<Course>({
      queryKey: ['course-checkout', courseUuid],
      queryFn: async () => {
        const response = await axiosInstance.get(`/courses/${courseUuid}`)
        return response.data.course
      },
      enabled: computed(() => !!courseUuid),
    })
  }

  // Initiate payment
  const useInitiatePayment = () => {
    return useMutation({
      mutationFn: async ({
        courseUuid,
        payCurrency,
      }: {
        courseUuid: string
        payCurrency: string
      }) => {
        const response = await axiosInstance.post(`/payments/initiate/${courseUuid}`, {
          pay_currency: payCurrency,
        })
        return response.data
      },
      onSuccess: (data) => {
        paymentDetails.value = data
        currentStep.value = 2
        startCountdown(data.expiration_estimate_date)
        generateQr(data.pay_address)
      },
      onError: (error: any) => {
        toast.error(error.response?.data?.message || 'Failed to initiate payment')
      },
    })
  }

  // Poll payment status
  const usePaymentStatus = (orderUuid: ReturnType<typeof ref<string | null>>) => {
    const query = useQuery<PaymentStatus>({
      queryKey: ['payment-status', orderUuid],
      queryFn: async () => {
        const response = await axiosInstance.get(`/payments/status/${orderUuid.value}`)
        return response.data
      },
      refetchInterval: (data) => {
        if (data && terminalStatuses.includes(data.status)) return false
        return 15000
      },
      enabled: computed(() => !!orderUuid.value),
    })

    // Watch for status changes
    watch(
      () => query.data.value?.status,
      (status) => {
        if (status === 'finished') {
          router.push('/payment/success')
        }
        if (status === 'failed' || status === 'expired') {
          router.push('/payment/cancel')
        }
      },
    )

    return query
  }

  // Countdown timer
  const startCountdown = (expiryDate: string) => {
    const interval = setInterval(() => {
      const diff = new Date(expiryDate).getTime() - Date.now()
      if (diff <= 0) {
        timeLeft.value = 'Expired'
        clearInterval(interval)
        return
      }
      const minutes = Math.floor(diff / 1000 / 60)
      const seconds = Math.floor((diff / 1000) % 60)
      timeLeft.value = `${minutes}m ${seconds}s`
    }, 1000)

    onUnmounted(() => clearInterval(interval))
  }

  // Generate QR code
  const generateQr = async (address: string) => {
    try {
      const QRCode = (await import('qrcode')).default
      qrDataUrl.value = await QRCode.toDataURL(address, { width: 200, margin: 1 })
    } catch (error) {
      console.error('Failed to generate QR code:', error)
    }
  }

  // Copy to clipboard
  const copyAddress = async (address: string) => {
    try {
      await navigator.clipboard.writeText(address)
      toast.success('Address copied to clipboard')
    } catch (error) {
      toast.error('Failed to copy address')
    }
  }

  // Reset checkout state
  const resetCheckout = () => {
    currentStep.value = 1
    paymentDetails.value = null
    timeLeft.value = ''
    qrDataUrl.value = ''
  }

  return {
    // State
    currentStep,
    paymentDetails,
    timeLeft,
    qrDataUrl,

    // Queries and Mutations
    useCourseDetails,
    useInitiatePayment,
    usePaymentStatus,

    // Methods
    copyAddress,
    resetCheckout,
  }
}
