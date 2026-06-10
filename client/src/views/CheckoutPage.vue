<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCheckout } from '@/composables/useCheckout'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import CheckoutStepper from '@/components/checkout/CheckoutStepper.vue'
import CourseSummaryCard from '@/components/checkout/CourseSummaryCard.vue'
import CurrencySelector from '@/components/checkout/CurrencySelector.vue'
import PaymentDetails from '@/components/checkout/PaymentDetails.vue'
import { PhSpinner } from '@phosphor-icons/vue'

const route = useRoute()
const router = useRouter()
const courseUuid = computed(() => route.params.courseUuid as string)

const {
  currentStep,
  selectedCurrency,
  paymentDetails,
  timeLeft,
  qrDataUrl,
  useCourseDetails,
  useCurrencies,
  useInitiatePayment,
  usePaymentStatus,
  copyAddress,
} = useCheckout()

const { data: course, isLoading: courseLoading } = useCourseDetails(courseUuid.value)
const { data: currencies, isLoading: currenciesLoading } = useCurrencies()
const { mutate: initiatePayment, isPending: paymentInitiating } = useInitiatePayment()

const orderUuid = computed(() => paymentDetails.value?.order_uuid || null)
const { data: paymentStatus } = usePaymentStatus(orderUuid)

const handleSelectPaymentMethod = () => {
  currentStep.value = 2
}

const handleBack = () => {
  currentStep.value = 1
  selectedCurrency.value = null
}

const handleSelectCurrency = (currency: any) => {
  selectedCurrency.value = currency
  initiatePayment({
    courseUuid: courseUuid.value,
    payCurrency: currency.id,
  })
}

const handleCancel = () => {
  router.push('/payment/cancel')
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
            @next="handleSelectPaymentMethod"
          />

          <!-- Step 2: Currency Selection -->
          <CurrencySelector
            v-else-if="currentStep === 2"
            :currencies="currencies || []"
            :is-loading="paymentInitiating"
            @back="handleBack"
            @next="handleSelectCurrency"
          />

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
