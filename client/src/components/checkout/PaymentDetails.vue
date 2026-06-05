<script setup lang="ts">
import { Button } from '@/components/ui/button'
import PaymentStatusBadge from './PaymentStatusBadge.vue'
import { PhCopy, PhWarning, PhX } from '@phosphor-icons/vue'

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

defineProps<{
  paymentDetails: PaymentDetails
  paymentStatus?: PaymentStatus
  qrDataUrl: string
  timeLeft: string
  onCopyAddress: (address: string) => void
  onCancel: () => void
}>()
</script>

<template>
  <div class="animate-fade-in-up liquid-glass rounded-2xl p-6 border border-border/20">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-foreground">Payment Details</h2>
      <PaymentStatusBadge v-if="paymentStatus" :status="paymentStatus.status" />
    </div>

    <div class="space-y-6">
      <!-- Warning Notice -->
      <div
        class="flex items-start gap-3 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-600 dark:text-yellow-400"
      >
        <PhWarning class="h-5 w-5 shrink-0 mt-0.5" weight="fill" />
        <p class="text-sm">
          <strong class="font-semibold">Important:</strong> Do not close this page until your
          payment is confirmed. Closing may result in delays.
        </p>
      </div>

      <!-- QR Code -->
      <div class="flex flex-col items-center py-4">
        <div class="p-4 bg-white rounded-xl border-2 border-border">
          <img
            v-if="qrDataUrl"
            :src="qrDataUrl"
            alt="wallet address qr code"
            class="rounded-lg mx-auto w-48 h-48"
          />
          <div v-else class="w-48 h-48 bg-secondary rounded-lg animate-pulse" />
        </div>
        <p class="text-xs text-muted-foreground mt-3">Scan QR code to pay</p>
      </div>

      <!-- Payment Amount -->
      <div class="space-y-2">
        <label class="text-sm font-medium text-muted-foreground">Amount to Send</label>
        <div class="p-4 bg-secondary/50 rounded-xl border border-border/40">
          <div class="text-2xl font-bold text-foreground font-primary">
            {{ paymentDetails.pay_amount.toFixed(8) }} {{ paymentDetails.pay_currency }}
          </div>
        </div>
      </div>

      <!-- Wallet Address -->
      <div class="space-y-2">
        <label class="text-sm font-medium text-muted-foreground">Wallet Address</label>
        <div
          class="flex items-center gap-2 p-4 bg-secondary/50 rounded-xl border border-border/40"
        >
          <code class="flex-1 text-xs font-primary break-all text-foreground">
            {{ paymentDetails.pay_address }}
          </code>
          <Button
            @click="onCopyAddress(paymentDetails.pay_address)"
            size="sm"
            variant="ghost"
            class="shrink-0 hover:bg-primary/10 hover:text-primary"
          >
            <PhCopy class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <!-- Expiry Timer -->
      <div class="flex items-center justify-between p-4 bg-primary/10 rounded-xl border border-primary/20">
        <span class="text-sm font-medium text-foreground">Time Remaining</span>
        <span class="text-lg font-bold text-primary font-primary">{{ timeLeft }}</span>
      </div>

      <!-- Payment ID -->
      <div class="text-xs text-muted-foreground text-center pt-4 border-t border-border/20">
        Payment ID: <span class="font-primary">{{ paymentDetails.payment_id }}</span>
      </div>

      <!-- Cancel Button -->
      <Button
        @click="onCancel"
        variant="outline"
        class="w-full text-sm font-semibold rounded-xl border-destructive/40 text-destructive hover:bg-destructive/10 flex items-center justify-center gap-2"
      >
        <PhX class="h-4 w-4" weight="bold" />
        Cancel Payment
      </Button>
    </div>
  </div>
</template>
