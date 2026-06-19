<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useOrders } from '@/composables/admin/useOrders'
import { useLanguageStore } from '@/stores/languageStore'
import type { Order, OrderStatus } from '@/types/OrderTypes'
import { PhShoppingCart, PhPencil } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const languageStore = useLanguageStore()
const lang = computed(() => languageStore.translations.adminOrders)

const { fetchOrders, updateOrderStatus, isUpdating } = useOrders()

const page = ref(1)
const search = ref('')
const filterStatus = ref<OrderStatus | ''>('')

const { data: ordersData, isLoading } = useQuery({
  queryKey: ['admin-orders', page, search, filterStatus],
  queryFn: () =>
    fetchOrders({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
      status: filterStatus.value || undefined,
    }),
})

const orders = computed(() => ordersData.value?.data || [])
const pagination = computed(() => ({
  current: ordersData.value?.current_page || 1,
  last: ordersData.value?.last_page || 1,
  total: ordersData.value?.total || 0,
  from: ordersData.value?.from || 0,
  to: ordersData.value?.to || 0,
}))

// Edit dialog state
const editDialogOpen = ref(false)
const orderToEdit = ref<Order | null>(null)
const selectedStatus = ref<OrderStatus>('waiting')

const openEditDialog = (order: Order) => {
  orderToEdit.value = order
  selectedStatus.value = order.status
  editDialogOpen.value = true
}

const confirmStatusUpdate = () => {
  if (!orderToEdit.value) return
  updateOrderStatus(
    { uuid: orderToEdit.value.uuid, status: selectedStatus.value },
    {
      onSuccess: () => {
        editDialogOpen.value = false
        orderToEdit.value = null
      },
    },
  )
}

const ALL_STATUSES: OrderStatus[] = [
  'waiting',
  'confirming',
  'confirmed',
  'sending',
  'partially_paid',
  'finished',
  'failed',
  'expired',
  'refunded',
]

const statusBadgeClass = (status: OrderStatus) => {
  const map: Record<OrderStatus, string> = {
    finished: 'bg-green-500/10 text-green-600',
    waiting: 'bg-yellow-500/10 text-yellow-600',
    confirming: 'bg-yellow-500/10 text-yellow-600',
    confirmed: 'bg-blue-500/10 text-blue-600',
    sending: 'bg-blue-500/10 text-blue-600',
    partially_paid: 'bg-orange-500/10 text-orange-600',
    failed: 'bg-red-500/10 text-red-600',
    expired: 'bg-red-500/10 text-red-600',
    refunded: 'bg-muted text-muted-foreground',
  }
  return map[status] ?? 'bg-muted text-muted-foreground'
}

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-foreground">{{ lang.title }}</h1>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 flex flex-col sm:flex-row gap-4">
      <Input
        v-model="search"
        :placeholder="lang.search"
        class="flex-1"
        @input="page = 1"
      />
      <div class="flex flex-wrap gap-2">
        <Button
          :variant="filterStatus === '' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = ''; page = 1"
        >
          {{ lang.filters.all }}
        </Button>
        <Button
          :variant="filterStatus === 'waiting' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'waiting'; page = 1"
        >
          {{ lang.filters.waiting }}
        </Button>
        <Button
          :variant="filterStatus === 'confirming' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'confirming'; page = 1"
        >
          {{ lang.filters.confirming }}
        </Button>
        <Button
          :variant="filterStatus === 'finished' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'finished'; page = 1"
        >
          {{ lang.filters.finished }}
        </Button>
        <Button
          :variant="filterStatus === 'failed' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'failed'; page = 1"
        >
          {{ lang.filters.failed }}
        </Button>
        <Button
          :variant="filterStatus === 'expired' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'expired'; page = 1"
        >
          {{ lang.filters.expired }}
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">{{ lang.states.loading }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="orders.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhShoppingCart :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">{{ lang.states.empty }}</h3>
    </div>

    <!-- Orders Table -->
    <div v-else class="liquid-glass rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="border-b border-border/40">
            <tr class="text-left">
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.user }}</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.course }}</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.amount }}</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.currency }}</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.status }}</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.date }}</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">{{ lang.columns.actions }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/20">
            <tr
              v-for="order in orders"
              :key="order.uuid"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3">
                <div class="font-medium text-foreground">{{ order.user?.name }}</div>
                <div class="text-xs text-muted-foreground">{{ order.user?.email }}</div>
              </td>
              <td class="px-4 py-3 text-foreground max-w-[180px]">
                <span class="line-clamp-2">{{ order.course?.title }}</span>
              </td>
              <td class="px-4 py-3 text-foreground font-medium">
                ${{ Number(order.amount).toFixed(2) }}
              </td>
              <td class="px-4 py-3 text-muted-foreground uppercase text-xs">
                {{ order.pay_currency ?? order.currency }}
              </td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize',
                    statusBadgeClass(order.status),
                  ]"
                >
                  {{ order.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                {{ formatDate(order.created_at) }}
              </td>
              <td class="px-4 py-3">
                <Button variant="outline" size="sm" @click="openEditDialog(order)">
                  <PhPencil :size="14" class="mr-1" />
                  {{ lang.columns.actions }}
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination.last > 1"
      class="flex items-center justify-between liquid-glass rounded-xl p-4"
    >
      <p class="text-sm text-muted-foreground">
        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} orders
      </p>
      <div class="flex gap-2">
        <Button
          @click="page--"
          :disabled="pagination.current === 1"
          variant="outline"
          size="sm"
        >
          Previous
        </Button>
        <Button
          @click="page++"
          :disabled="pagination.current === pagination.last"
          variant="outline"
          size="sm"
        >
          Next
        </Button>
      </div>
    </div>

    <!-- Edit Status Dialog -->
    <Dialog v-model:open="editDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ lang.dialog.title }}</DialogTitle>
          <DialogDescription>{{ lang.dialog.description }}</DialogDescription>
        </DialogHeader>

        <div class="py-4 space-y-2">
          <label class="text-sm font-medium text-foreground">{{ lang.dialog.statusLabel }}</label>
          <Select v-model="selectedStatus">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="s in ALL_STATUSES" :key="s" :value="s">
                {{ s.replace('_', ' ') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="editDialogOpen = false">
            {{ lang.dialog.cancel }}
          </Button>
          <Button @click="confirmStatusUpdate" :disabled="isUpdating">
            {{ isUpdating ? 'Updating...' : lang.dialog.confirm }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
