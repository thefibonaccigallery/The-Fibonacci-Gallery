<script setup lang="ts">
import { computed } from 'vue'
import type { Order } from '@/composables/admin/useAdminDashboard'

interface Props {
  orders: Order[]
}

const props = defineProps<Props>()

const getStatusColor = (status: Order['status']) => {
  const colors = {
    waiting: 'bg-muted text-muted-foreground',
    confirming: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    finished: 'bg-primary/10 text-primary',
    failed: 'bg-destructive/10 text-destructive',
    expired: 'bg-muted text-muted-foreground',
    partially_paid: 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
  }
  return colors[status]
}

const getStatusLabel = (status: Order['status']) => {
  const labels = {
    waiting: 'Waiting',
    confirming: 'Confirming',
    finished: 'Finished',
    failed: 'Failed',
    expired: 'Expired',
    partially_paid: 'Partially Paid',
  }
  return labels[status]
}

const formatCurrency = (amount: number, currency: string = 'USD') => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
  }).format(amount)
}

const formatDate = (dateString: string) => {
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
      <button class="text-sm text-primary hover:underline">View All</button>
    </div>

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
            :key="order.id"
            class="border-b border-border/30 hover:bg-accent/30 transition-colors"
          >
            <td class="py-4 px-4 text-sm font-medium text-foreground">{{ order.student }}</td>
            <td class="py-4 px-4 text-sm text-foreground/80">{{ order.course }}</td>
            <td class="py-4 px-4 text-sm font-medium text-foreground">
              {{ formatCurrency(order.amount, order.currency) }}
            </td>
            <td class="py-4 px-4 text-sm text-foreground/80">{{ order.payCurrency }}</td>
            <td class="py-4 px-4">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                  getStatusColor(order.status),
                ]"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
            <td class="py-4 px-4 text-sm text-muted-foreground">{{ formatDate(order.date) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
      <div
        v-for="order in orders"
        :key="order.id"
        class="p-4 rounded-lg bg-accent/20 border border-border/30"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <p class="font-medium text-foreground">{{ order.student }}</p>
            <p class="text-sm text-muted-foreground">{{ order.course }}</p>
          </div>
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
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
            <span class="text-muted-foreground ml-2">via {{ order.payCurrency }}</span>
          </div>
          <span class="text-muted-foreground">{{ formatDate(order.date) }}</span>
        </div>
      </div>
    </div>

    <!-- Pagination Placeholder -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-border/50">
      <p class="text-sm text-muted-foreground">Showing 1-5 of {{ orders.length }} orders</p>
      <div class="flex gap-2">
        <button
          class="px-3 py-1.5 text-sm rounded-lg border border-border/50 hover:bg-accent/50 transition-colors text-foreground disabled:opacity-50 disabled:cursor-not-allowed"
          disabled
        >
          Previous
        </button>
        <button
          class="px-3 py-1.5 text-sm rounded-lg border border-border/50 hover:bg-accent/50 transition-colors text-foreground"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
