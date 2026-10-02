<script setup lang="ts">
import { useRouter } from 'vue-router'
import type { Order, OrderStatus } from '@/types/OrderTypes'

interface Props {
  orders: Order[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const router = useRouter()

const getStatusColor = (status: OrderStatus) => {
  const colors: Record<string, string> = {
    waiting: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    confirming: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    confirmed: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    sending: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    finished: 'bg-green-500/10 text-green-600 dark:text-green-400',
    failed: 'bg-destructive/10 text-destructive',
    expired: 'bg-muted text-muted-foreground',
    partially_paid: 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
    refunded: 'bg-muted text-muted-foreground',
  }
  return colors[status] || 'bg-muted text-muted-foreground'
}

const getStatusLabel = (status: OrderStatus) => {
  const labels: Record<string, string> = {
    waiting: 'Waiting',
    confirming: 'Confirming',
    confirmed: 'Confirmed',
    sending: 'Sending',
    finished: 'Finished',
    failed: 'Failed',
    expired: 'Expired',
    partially_paid: 'Partially Paid',
    refunded: 'Refunded',
  }
  return labels[status] || status
}

const formatCurrency = (amount: number | string, currency: string = 'USD') => {
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
  }).format(numericAmount)
}

const formatDate = (dateString?: string | null) => {
  if (!dateString) return 'N/A'
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(dateString))
}
</script>

<template>
  <div class="liquid-glass rounded-xl p-6 animate-fade-in-up">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-foreground">Recent Orders</h2>
      <button
        @click="router.push('/admin/orders')"
        class="text-sm text-primary hover:underline font-medium transition-colors"
      >
        View All
      </button>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="animate-pulse flex items-center justify-between py-3 border-b border-border/20">
        <div class="space-y-2 flex-1">
          <div class="h-4 w-1/4 bg-muted rounded"></div>
          <div class="h-3 w-1/3 bg-muted/60 rounded"></div>
        </div>
        <div class="h-4 w-16 bg-muted rounded mx-4"></div>
        <div class="h-6 w-20 bg-muted rounded-full"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="orders.length === 0" class="py-12 text-center">
      <p class="text-muted-foreground text-sm">No recent orders yet</p>
    </div>

    <template v-else>
      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-border/50">
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Student</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Course</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Amount</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Crypto</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Status</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="order in orders"
              :key="order.uuid"
              class="border-b border-border/30 hover:bg-accent/30 transition-colors"
            >
              <td class="py-4 px-4 text-sm font-medium text-foreground">
                <div>{{ order.user?.name || 'N/A' }}</div>
                <div class="text-xs text-muted-foreground font-normal">{{ order.user?.email || '' }}</div>
              </td>
              <td class="py-4 px-4 text-sm text-foreground/80 max-w-[200px]">
                <span class="line-clamp-1">{{ order.course?.title || 'N/A' }}</span>
              </td>
              <td class="py-4 px-4 text-sm font-medium text-foreground">
                {{ formatCurrency(order.amount, order.currency) }}
              </td>
              <td class="py-4 px-4 text-sm text-foreground/80 uppercase">
                {{ order.pay_currency || order.currency }}
              </td>
              <td class="py-4 px-4">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize',
                    getStatusColor(order.status),
                  ]"
                >
                  {{ getStatusLabel(order.status) }}
                </span>
              </td>
              <td class="py-4 px-4 text-sm text-muted-foreground whitespace-nowrap">
                {{ formatDate(order.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="md:hidden space-y-3">
        <div
          v-for="order in orders"
          :key="order.uuid"
          class="p-4 rounded-lg bg-accent/20 border border-border/30"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <p class="font-medium text-foreground">{{ order.user?.name || 'N/A' }}</p>
              <p class="text-sm text-muted-foreground">{{ order.course?.title || 'N/A' }}</p>
            </div>
            <span
              :class="[
                'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize',
                getStatusColor(order.status),
              ]"
            >
              {{ getStatusLabel(order.status) }}
            </span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <div>
              <span class="text-foreground font-medium">
                {{ formatCurrency(order.amount, order.currency) }}
              </span>
              <span class="text-muted-foreground ml-2 uppercase text-xs">
                via {{ order.pay_currency || order.currency }}
              </span>
            </div>
            <span class="text-muted-foreground text-xs">{{ formatDate(order.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="flex items-center justify-between mt-6 pt-4 border-t border-border/50">
        <p class="text-sm text-muted-foreground">
          Showing latest {{ orders.length }} {{ orders.length === 1 ? 'order' : 'orders' }}
        </p>
        <button
          @click="router.push('/admin/orders')"
          class="px-3 py-1.5 text-sm rounded-lg border border-border/50 hover:bg-accent/50 transition-colors text-foreground"
        >
          View all orders
        </button>
      </div>
    </template>
  </div>
</template>
