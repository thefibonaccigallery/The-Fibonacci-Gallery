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

const handleInitiatePayment = () => {
  if (!selectedCurrency.value) return
  
  initiatePayment({
    courseUuid: courseUuid.value,
    payCurrency: selectedCurrency.value,
    couponCode: appliedCouponCode ?? undefined,
  })
}

const handleCancel = () => {
  router.push(`/payment/cancel?courseUuid=${courseUuid.value}`)
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
                  class="liquid-glass rounded-xl p-4 cursor-pointer transition-all border-2"
                  :class="
                    selectedCurrency === currency.id
                      ? 'border-primary bg-primary/5'
                      : 'border-transparent hover:border-primary/30'
                  "
                  @click="selectedCurrency = currency.id"
                >
                  <p class="font-semibold text-sm text-foreground">{{ currency.name }}</p>
                  <p class="text-xs text-muted-foreground mt-1">{{ currency.network }}</p>
                  <p class="text-xs text-primary mt-1">{{ currency.fee }}</p>
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
          />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
