<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCheckout } from '@/composables/useCheckout'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import CheckoutStepper from '@/components/checkout/CheckoutStepper.vue'
import CourseSummaryCard from '@/components/checkout/CourseSummaryCard.vue'
import PaymentDetails from '@/components/checkout/PaymentDetails.vue'
import { Button } from '@/components/ui/button'
import { PhSpinner, PhArrowLeft } from '@phosphor-icons/vue'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'

const route = useRoute()
const router = useRouter()
const courseUuid = computed(() => route.params.courseUuid as string)

const {
  currentStep,
  paymentDetails,
  timeLeft,
  qrDataUrl,
  selectedCurrency,
  useCurrencies,
  useCourseDetails,
  useInitiatePayment,
  usePaymentStatus,
  copyAddress,
} = useCheckout()

const { data: course, isLoading: courseLoading } = useCourseDetails(courseUuid.value)
const { data: currencies, isLoading: currenciesLoading } = useCurrencies()
const { mutate: initiatePayment, isPending: paymentInitiating } = useInitiatePayment()

const orderUuid = computed(() => paymentDetails.value?.order_uuid || null)
const { data: paymentStatus } = usePaymentStatus(orderUuid, courseUuid.value)

// Store coupon code for later use
let appliedCouponCode: string | null = null
const isEnrollingFree = ref(false)

const handleNextToPayment = async (couponCode: string | null, isFree: boolean) => {
  appliedCouponCode = couponCode

  if (isFree) {
    isEnrollingFree.value = true
    try {
      if (!couponCode) {
        // Natively free course — use enroll endpoint directly
        const response = await axiosInstance.post(`/enroll/${courseUuid.value}`)
        toast.success('You have been enrolled successfully!')
        router.push(`/courses/${response.data.course_uuid || courseUuid.value}/player`)
      } else {
        // Coupon makes it free — call initiatePayment; backend detects $0 + coupon and enrolls directly
        initiatePayment({
          courseUuid: courseUuid.value,
          payCurrency: 'usdttrc20', // default currency; backend skips payment when amount == 0
          couponCode,
        })
      }
    } catch (error: any) {
      if (error.response?.status === 409) {
        router.push(`/courses/${courseUuid.value}/player`)
      } else {
        toast.error(error.response?.data?.message || 'Enrollment failed. Please try again.')
      }
    } finally {
      isEnrollingFree.value = false
    }
    return
  }

  currentStep.value = 2
}

const handleBackToCourse = () => {
  currentStep.value = 1
  selectedCurrency.value = null
}

const isCancelling = ref(false)

const handleInitiatePayment = () => {
  if (!selectedCurrency.value) return
  
  initiatePayment({
    courseUuid: courseUuid.value,
    payCurrency: selectedCurrency.value,
    couponCode: appliedCouponCode ?? undefined,
  })
}

const handleCancel = async () => {
  if (!orderUuid.value) {
    router.push(`/payment/cancel?courseUuid=${courseUuid.value}`)
    return
  }

  isCancelling.value = true
  try {
    await axiosInstance.post(`/payments/cancel/${orderUuid.value}`)
    toast.success('Payment cancelled')
  } catch (error: any) {
    // If order was already completed, redirect to player
    if (error.response?.status === 422) {
      toast.info(error.response.data.message)
      router.push(`/courses/${courseUuid.value}/player`)
      return
    }
    
    toast.error(error.response?.data?.message || 'Failed to cancel payment')
  } finally {
    isCancelling.value = false
    router.push(`/payment/cancel?courseUuid=${courseUuid.value}`)
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background pb-24 md:pb-8">
      <!-- Header -->
      <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-6">
          <h1 class="text-3xl font-bold text-foreground">Checkout</h1>
          <p class="text-sm text-muted-foreground mt-1">Complete your purchase securely</p>
        </div>
      </div>

      <!-- Content -->
      <div class="container mx-auto px-4 py-8">
        <!-- Loading State -->
        <div v-if="courseLoading" class="flex items-center justify-center py-20">
          <PhSpinner class="h-8 w-8 animate-spin text-primary" />
        </div>

        <!-- Checkout Flow -->
        <div v-else-if="course" class="max-w-2xl mx-auto">
          <!-- Stepper -->
          <CheckoutStepper :current-step="currentStep" />

          <!-- Step 1: Course Summary -->
          <CourseSummaryCard
            v-if="currentStep === 1"
            :course="course"
            :loading="isEnrollingFree"
            @next="handleNextToPayment"
          />

          <!-- Step 2: Currency Selection -->
          <div
            v-else-if="currentStep === 2"
            class="animate-fade-in-up liquid-glass rounded-2xl p-6 border border-border/20"
          >
            <h2 class="text-xl font-bold text-foreground mb-6">Select Payment Currency</h2>

            <div v-if="currenciesLoading" class="flex items-center justify-center py-12">
              <PhSpinner class="h-8 w-8 animate-spin text-primary" />
            </div>

            <div v-else-if="currencies" class="space-y-6">
              <!-- Currency Grid -->
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div
                  v-for="currency in currencies"
                  :key="currency.id"
                  class="relative liquid-glass rounded-xl p-4 cursor-pointer transition-all duration-300 border-2"
                  :class="
                    selectedCurrency === currency.id
                      ? 'border-primary bg-primary/10 shadow-lg shadow-primary/20'
                      : 'border-transparent hover:border-primary/30 hover:bg-primary/5'
                  "
                  @click="selectedCurrency = currency.id"
                >
                  <!-- Selected Indicator -->
                  <div
                    v-if="selectedCurrency === currency.id"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-primary rounded-full flex items-center justify-center shadow-lg animate-scale-in"
                  >
                    <svg
                      class="w-4 h-4 text-primary-foreground"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="3"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                  </div>

                  <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                      <p
                        class="font-semibold text-sm transition-colors"
                        :class="
                          selectedCurrency === currency.id ? 'text-primary' : 'text-foreground'
                        "
                      >
                        {{ currency.name }}
                      </p>
                      <p class="text-xs text-muted-foreground mt-1">{{ currency.network }}</p>
                      <p
                        class="text-xs mt-1 font-medium"
                        :class="selectedCurrency === currency.id ? 'text-primary' : 'text-primary/70'"
                      >
                        {{ currency.fee }}
                      </p>
                    </div>

                    <!-- Radio Indicator -->
                    <div
                      class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all flex-shrink-0"
                      :class="
                        selectedCurrency === currency.id
                          ? 'border-primary bg-primary'
                          : 'border-border bg-background'
                      "
                    >
                      <div
                        v-if="selectedCurrency === currency.id"
                        class="w-2 h-2 rounded-full bg-primary-foreground animate-scale-in"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex gap-3 pt-4 border-t border-border/20">
                <Button
                  @click="handleBackToCourse"
                  variant="outline"
                  class="flex-1 text-sm font-semibold rounded-xl border-border/40 hover:bg-secondary/50 flex items-center justify-center gap-2"
                >
                  <PhArrowLeft class="h-4 w-4" weight="bold" />
                  Back
                </Button>
                <Button
                  @click="handleInitiatePayment"
                  :disabled="!selectedCurrency || paymentInitiating"
                  class="flex-1 text-sm font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground flex items-center justify-center gap-2"
                >
                  <PhSpinner v-if="paymentInitiating" class="h-4 w-4 animate-spin" />
                  <span v-else>Confirm and Pay</span>
                </Button>
              </div>
            </div>
          </div>

          <!-- Step 3: Payment Details -->
          <PaymentDetails
            v-else-if="currentStep === 3 && paymentDetails"
            :payment-details="paymentDetails"
            :payment-status="paymentStatus"
            :qr-data-url="qrDataUrl"
            :time-left="timeLeft"
            :on-copy-address="copyAddress"
            :on-cancel="handleCancel"
            :cancelling="isCancelling"
          />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
