import { ref, computed, watch, onUnmounted } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'

interface Course {
  id: number
  title: string
  level: string
  price: number
  thumbnail_url: string | null
}

interface Currency {
  id: string
  name: string
  symbol: string
  icon?: string
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
  const selectedCurrency = ref<Currency | null>(null)
  const paymentDetails = ref<PaymentDetails | null>(null)
  const timeLeft = ref('')
  const qrDataUrl = ref('')

  // Fetch course details
  const useCourseDetails = (courseId: number) => {
    return useQuery<Course>({
      queryKey: ['course-checkout', courseId],
      queryFn: async () => {
        // TODO: Replace with actual API call
        // const response = await axiosInstance.get(`/courses/${courseId}`)
        // return response.data

        // Mock data for now
        return {
          id: courseId,
          title: 'The Complete Forex Trading Course',
          level: 'beginner',
          price: 99.0,
          thumbnail_url: null,
        }
      },
      enabled: computed(() => !!courseId),
    })
  }

  // Fetch available currencies
  const useCurrencies = () => {
    return useQuery<Currency[]>({
      queryKey: ['payment-currencies'],
      queryFn: async () => {
        // TODO: Replace with actual API call
        // const response = await axiosInstance.get('/api/payments/currencies')
        // return response.data

        // Mock data for now
        return [
          { id: 'btc', name: 'Bitcoin', symbol: 'BTC' },
          { id: 'eth', name: 'Ethereum', symbol: 'ETH' },
          { id: 'usdttrc20', name: 'Tether TRC20', symbol: 'USDT' },
          { id: 'usdterc20', name: 'Tether ERC20', symbol: 'USDT' },
          { id: 'bnb', name: 'BNB', symbol: 'BNB' },
          { id: 'ltc', name: 'Litecoin', symbol: 'LTC' },
        ]
      },
    })
  }

  // Initiate payment
  const useInitiatePayment = () => {
    return useMutation({
      mutationFn: async ({
        courseId,
        payCurrency,
      }: {
        courseId: number
        payCurrency: string
      }) => {
        // TODO: Replace with actual API call
        // const response = await axiosInstance.post(`/api/payments/initiate/${courseId}`, {
        //   pay_currency: payCurrency,
        // })
        // return response.data

        // Mock data for now
        await new Promise((resolve) => setTimeout(resolve, 1500))
        return {
          payment_id: '123456789',
          pay_address: '0xABCDEF1234567890ABCDEF1234567890ABCDEF12',
          pay_amount: 0.03241,
          pay_currency: payCurrency,
          order_uuid: 'abc-def-ghi-jkl-' + Date.now(),
          expiration_estimate_date: new Date(Date.now() + 60 * 60 * 1000).toISOString(),
        }
      },
      onSuccess: (data) => {
        paymentDetails.value = data
        currentStep.value = 3
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
        // TODO: Replace with actual API call
        // const response = await axiosInstance.get(`/api/payments/status/${orderUuid.value}`)
        // return response.data

        // Mock data for now - simulate payment flow
        await new Promise((resolve) => setTimeout(resolve, 500))
        const random = Math.random()
        if (random > 0.9) {
          return { status: 'finished' as const, payment_amount: 0.03241, actually_paid: 0.03241 }
        }
        return { status: 'waiting' as const, payment_amount: 0.03241, actually_paid: 0 }
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
    selectedCurrency.value = null
    paymentDetails.value = null
    timeLeft.value = ''
    qrDataUrl.value = ''
  }

  return {
    // State
    currentStep,
    selectedCurrency,
    paymentDetails,
    timeLeft,
    qrDataUrl,

    // Queries and Mutations
    useCourseDetails,
    useCurrencies,
    useInitiatePayment,
    usePaymentStatus,

    // Methods
    copyAddress,
    resetCheckout,
  }
}
