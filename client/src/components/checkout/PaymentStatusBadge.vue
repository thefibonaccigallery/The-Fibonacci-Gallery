<script setup lang="ts">
import { computed } from 'vue'
import {
  PhClock,
  PhHourglass,
  PhCheckCircle,
  PhArrowsClockwise,
  PhWarning,
  PhXCircle,
} from '@phosphor-icons/vue'

type PaymentStatus =
  | 'waiting'
  | 'confirming'
  | 'confirmed'
  | 'sending'
  | 'partially_paid'
  | 'finished'
  | 'failed'
  | 'expired'
  | 'refunded'

const props = defineProps<{
  status: PaymentStatus
}>()

const statusConfig = computed(() => {
  const configs: Record<
    PaymentStatus,
    { label: string; color: string; icon: any; bgColor: string }
  > = {
    waiting: {
      label: 'Waiting for payment',
      color: 'text-muted-foreground',
      icon: PhClock,
      bgColor: 'bg-secondary',
    },
    confirming: {
      label: 'Confirming transaction',
      color: 'text-yellow-500',
      icon: PhHourglass,
      bgColor: 'bg-yellow-500/10',
    },
    confirmed: {
      label: 'Confirmed',
      color: 'text-green-500',
      icon: PhCheckCircle,
      bgColor: 'bg-green-500/10',
    },
    sending: {
      label: 'Processing',
      color: 'text-green-500',
      icon: PhArrowsClockwise,
      bgColor: 'bg-green-500/10',
    },
    partially_paid: {
      label: 'Partially paid',
      color: 'text-orange-500',
      icon: PhWarning,
      bgColor: 'bg-orange-500/10',
    },
    finished: {
      label: 'Payment complete',
      color: 'text-green-500',
      icon: PhCheckCircle,
      bgColor: 'bg-green-500/10',
    },
    failed: {
      label: 'Payment failed',
      color: 'text-red-500',
      icon: PhXCircle,
      bgColor: 'bg-red-500/10',
    },
    expired: {
      label: 'Payment expired',
      color: 'text-red-500',
      icon: PhXCircle,
      bgColor: 'bg-red-500/10',
    },
    refunded: {
      label: 'Payment refunded',
      color: 'text-blue-500',
      icon: PhArrowsClockwise,
      bgColor: 'bg-blue-500/10',
    },
  }

  return configs[props.status]
})
</script>

<template>
  <div
    :class="[
      'inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium border',
      statusConfig.color,
      statusConfig.bgColor,
      'border-current/20',
    ]"
  >
    <component :is="statusConfig.icon" class="h-4 w-4" weight="fill" />
    <span>{{ statusConfig.label }}</span>
  </div>
</template>
