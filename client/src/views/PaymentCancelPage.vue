<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { PhXCircle, PhArrowCounterClockwise, PhArrowLeft } from '@phosphor-icons/vue'
import { computed } from 'vue'

const router = useRouter()
const route = useRoute()

// Get course ID from query params or fallback
const courseId = computed(() => route.query.courseId || 1)

const handleTryAgain = () => {
  router.push(`/checkout/${courseId.value}`)
}

const handleBackToCourse = () => {
  router.push(`/courses/${courseId.value}/details`)
}

const handleBackToDashboard = () => {
  router.push('/dashboard')
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background flex items-center justify-center p-4">
      <div class="w-full max-w-lg">
        <div
          class="animate-fade-in-up liquid-glass rounded-2xl p-8 md:p-12 border border-border/20 text-center"
        >
          <!-- Cancel Icon -->
          <div class="flex justify-center mb-6">
            <div
              class="w-24 h-24 rounded-full bg-red-500/20 border-4 border-red-500 flex items-center justify-center animate-scale-in"
            >
              <PhXCircle class="h-16 w-16 text-red-500" weight="fill" />
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-3">Payment Cancelled</h1>

          <!-- Subtitle -->
          <p class="text-muted-foreground text-base mb-2">
            Your payment was not completed.
          </p>
          <p class="text-sm text-muted-foreground mb-8">
            You have not been charged. You can try again or return to browse courses.
          </p>

          <!-- Info Box -->
          <div class="mb-8 p-4 bg-secondary/50 rounded-xl border border-border/40 text-left">
            <p class="text-sm text-muted-foreground">
              <strong class="text-foreground">What happened?</strong><br />
              Your payment session was cancelled or expired. This could happen if:
            </p>
            <ul class="text-xs text-muted-foreground mt-2 space-y-1 list-disc list-inside">
              <li>Payment was not completed within the time limit</li>
              <li>You cancelled the transaction</li>
              <li>Payment failed or was rejected</li>
            </ul>
          </div>

          <!-- Actions -->
          <div class="space-y-3">
            <Button
              @click="handleTryAgain"
              class="w-full text-base font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground py-6 flex items-center justify-center gap-2"
            >
              <PhArrowCounterClockwise class="h-5 w-5" weight="bold" />
              Try Again
            </Button>

            <Button
              @click="handleBackToCourse"
              variant="outline"
              class="w-full text-sm font-semibold rounded-xl border-border/40 hover:bg-secondary/50 flex items-center justify-center gap-2"
            >
              <PhArrowLeft class="h-4 w-4" weight="bold" />
              Back to Course
            </Button>

            <Button
              @click="handleBackToDashboard"
              variant="ghost"
              class="w-full text-sm font-semibold rounded-xl hover:bg-secondary/50"
            >
              Back to Dashboard
            </Button>
          </div>

          <!-- Footer Note -->
          <p class="text-xs text-muted-foreground mt-8">
            Need help? Contact our support team for assistance.
          </p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
@keyframes scale-in {
  from {
    transform: scale(0);
  }
  to {
    transform: scale(1);
  }
}

.animate-scale-in {
  animation: scale-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
</style>
