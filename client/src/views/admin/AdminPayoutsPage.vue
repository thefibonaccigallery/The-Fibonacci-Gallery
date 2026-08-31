<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useClipboard } from '@vueuse/core'
import { toast } from 'vue-sonner'
import { useAdminPayouts } from '@/composables/admin/useAdminPayouts'
import { useLanguageStore } from '@/stores/languageStore'
import type { AffiliatePayout, PayoutStatus } from '@/types/AffiliateTypes'
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
  PhHandCoins,
  PhCheckCircle,
  PhXCircle,
  PhCheck,
  PhClock,
  PhCopy,
  PhPaperPlaneTilt,
} from '@phosphor-icons/vue'

const languageStore = useLanguageStore()
const lang = computed(() => languageStore.translations.adminPayouts)

const {
  fetchPayouts,
  approvePayout,
  completePayout,
  rejectPayout,
  isApproving,
  isCompleting,
  isRejecting,
} = useAdminPayouts()

const page = ref(1)
const search = ref('')
const filterStatus = ref<PayoutStatus | ''>('')

const { data: payoutsData, isLoading } = useQuery({
  queryKey: ['admin-affiliate-payouts', page, search, filterStatus],
  queryFn: () =>
    fetchPayouts({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
      status: filterStatus.value || undefined,
    }),
})

const payouts = computed(() => payoutsData.value?.data || [])
const pagination = computed(() => ({
  current: payoutsData.value?.current_page || 1,
  last: payoutsData.value?.last_page || 1,
  total: payoutsData.value?.total || 0,
  from: payoutsData.value?.from || 0,
  to: payoutsData.value?.to || 0,
}))

// ── Clipboard ──
const { copy } = useClipboard()
const copyWallet = (address: string) => {
  copy(address)
  toast.success('Wallet address copied!')
}

// ── Action Dialog States ──
const selectedPayout = ref<AffiliatePayout | null>(null)
const approveDialogOpen = ref(false)
const completeDialogOpen = ref(false)
const rejectDialogOpen = ref(false)
const adminNote = ref('')
const rejectReason = ref('')
const rejectError = ref('')

const openApproveDialog = (payout: AffiliatePayout) => {
  selectedPayout.value = payout
  approveDialogOpen.value = true
}

const confirmApprove = () => {
  if (!selectedPayout.value) return
  approvePayout(selectedPayout.value.uuid, {
    onSuccess: () => {
      approveDialogOpen.value = false
      selectedPayout.value = null
    },
  })
}

const openCompleteDialog = (payout: AffiliatePayout) => {
  selectedPayout.value = payout
  adminNote.value = ''
  completeDialogOpen.value = true
}

const confirmComplete = () => {
  if (!selectedPayout.value) return
  completePayout(
    { uuid: selectedPayout.value.uuid, admin_note: adminNote.value || undefined },
    {
      onSuccess: () => {
        completeDialogOpen.value = false
        selectedPayout.value = null
        adminNote.value = ''
      },
    },
  )
}

const openRejectDialog = (payout: AffiliatePayout) => {
  selectedPayout.value = payout
  rejectReason.value = ''
  rejectError.value = ''
  rejectDialogOpen.value = true
}

const confirmReject = () => {
  if (!selectedPayout.value) return
  if (!rejectReason.value.trim()) {
    rejectError.value = 'Please provide a reason for rejection.'
    return
  }

  rejectPayout(
    { uuid: selectedPayout.value.uuid, admin_note: rejectReason.value.trim() },
    {
      onSuccess: () => {
        rejectDialogOpen.value = false
        selectedPayout.value = null
        rejectReason.value = ''
        rejectError.value = ''
      },
    },
  )
}

// ── Helpers ──
const statusBadgeClass = (status: PayoutStatus) => {
  const map: Record<PayoutStatus, string> = {
    requested: 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20',
    approved: 'bg-blue-500/10 text-blue-500 border border-blue-500/20',
    completed: 'bg-green-500/10 text-green-500 border border-green-500/20',
    rejected: 'bg-red-500/10 text-red-500 border border-red-500/20',
  }
  return map[status] ?? 'bg-muted text-muted-foreground'
}

const formatDate = (date: string | null) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="space-y-6 font-primary">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-foreground">{{ lang.title }}</h1>
        <p class="text-sm text-muted-foreground mt-1">
          Review, approve, manually send USDT BEP20 funds, and complete affiliate payout requests.
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 flex flex-col sm:flex-row gap-4 border border-border/20">
      <Input
        v-model="search"
        :placeholder="lang.search"
        class="flex-1 bg-background/50"
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
          :variant="filterStatus === 'requested' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'requested'; page = 1"
        >
          {{ lang.filters.requested }}
        </Button>
        <Button
          :variant="filterStatus === 'approved' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'approved'; page = 1"
        >
          {{ lang.filters.approved }}
        </Button>
        <Button
          :variant="filterStatus === 'completed' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'completed'; page = 1"
        >
          {{ lang.filters.completed }}
        </Button>
        <Button
          :variant="filterStatus === 'rejected' ? 'default' : 'outline'"
          size="sm"
          @click="filterStatus = 'rejected'; page = 1"
        >
          {{ lang.filters.rejected }}
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">{{ lang.states.loading }}</p>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="payouts.length === 0"
      class="liquid-glass rounded-xl p-12 text-center border border-border/20"
    >
      <PhHandCoins :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">{{ lang.states.empty }}</h3>
    </div>

    <!-- Payouts Table -->
    <div v-else class="liquid-glass rounded-xl overflow-hidden border border-border/20">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="border-b border-border/40 bg-muted/20">
            <tr class="text-left text-xs uppercase tracking-wider text-muted-foreground font-medium">
              <th class="px-4 py-3.5">{{ lang.columns.affiliate }}</th>
              <th class="px-4 py-3.5">{{ lang.columns.amount }}</th>
              <th class="px-4 py-3.5">{{ lang.columns.wallet }}</th>
              <th class="px-4 py-3.5">{{ lang.columns.network }}</th>
              <th class="px-4 py-3.5">{{ lang.columns.status }}</th>
              <th class="px-4 py-3.5">{{ lang.columns.date }}</th>
              <th class="px-4 py-3.5">Note</th>
              <th class="px-4 py-3.5 text-right">{{ lang.columns.actions }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/20">
            <tr
              v-for="payout in payouts"
              :key="payout.uuid"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3.5">
                <div class="font-medium text-foreground">{{ payout.affiliate?.name ?? 'Affiliate' }}</div>
                <div class="text-xs text-muted-foreground">{{ payout.affiliate?.email }}</div>
              </td>
              <td class="px-4 py-3.5 font-bold text-foreground whitespace-nowrap">
                ${{ Number(payout.amount).toFixed(2) }}
              </td>
              <td class="px-4 py-3.5 max-w-50">
                <div class="flex items-center gap-1.5">
                  <span class="font-mono text-xs text-foreground truncate select-all">
                    {{ payout.wallet_address }}
                  </span>
                  <button
                    @click="copyWallet(payout.wallet_address)"
                    class="text-muted-foreground hover:text-foreground shrink-0 p-1"
                    title="Copy wallet address"
                  >
                    <PhCopy :size="14" />
                  </button>
                </div>
              </td>
              <td class="px-4 py-3.5 text-xs text-muted-foreground whitespace-nowrap">
                {{ payout.network }}
              </td>
              <td class="px-4 py-3.5">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                    statusBadgeClass(payout.status),
                  ]"
                >
                  {{ payout.status }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-xs text-muted-foreground whitespace-nowrap">
                {{ formatDate(payout.created_at) }}
              </td>
              <td class="px-4 py-3.5 text-xs text-muted-foreground max-w-40">
                <span class="line-clamp-2">{{ payout.admin_note ?? '—' }}</span>
              </td>
              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <!-- If Requested -->
                  <template v-if="payout.status === 'requested'">
                    <Button
                      variant="outline"
                      size="sm"
                      class="h-8 border-blue-500/30 text-blue-500 hover:bg-blue-500/10 hover:text-blue-500"
                      @click="openApproveDialog(payout)"
                    >
                      <PhPaperPlaneTilt :size="14" class="mr-1" />
                      {{ lang.actions.approve }}
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      class="h-8 border-destructive/30 text-destructive hover:bg-destructive/10 hover:text-destructive"
                      @click="openRejectDialog(payout)"
                    >
                      <PhXCircle :size="14" class="mr-1" />
                      {{ lang.actions.reject }}
                    </Button>
                  </template>

                  <!-- If Approved -->
                  <template v-else-if="payout.status === 'approved'">
                    <Button
                      variant="outline"
                      size="sm"
                      class="h-8 border-primary/30 text-primary hover:bg-primary/10 hover:text-primary"
                      @click="openCompleteDialog(payout)"
                    >
                      <PhCheckCircle :size="14" class="mr-1" />
                      {{ lang.actions.complete }}
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      class="h-8 border-destructive/30 text-destructive hover:bg-destructive/10 hover:text-destructive"
                      @click="openRejectDialog(payout)"
                    >
                      <PhXCircle :size="14" class="mr-1" />
                      {{ lang.actions.reject }}
                    </Button>
                  </template>

                  <!-- If Completed / Rejected -->
                  <template v-else>
                    <span class="text-xs text-muted-foreground italic">
                      {{ payout.status === 'completed' ? 'Completed' : 'Rejected' }}
                    </span>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination.last > 1"
      class="flex items-center justify-between liquid-glass rounded-xl p-4 border border-border/20"
    >
      <p class="text-sm text-muted-foreground">
        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} requests
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

    <!-- Approve Confirmation Dialog -->
    <Dialog v-model:open="approveDialogOpen">
      <DialogContent class="sm:max-w-md font-primary">
        <DialogHeader>
          <DialogTitle>{{ lang.dialogs.approveTitle }}</DialogTitle>
          <DialogDescription>
            {{ lang.dialogs.approveDescription }}
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedPayout" class="py-3 space-y-2 text-sm">
          <div class="p-3 rounded-lg bg-muted/40 flex justify-between">
            <span class="text-muted-foreground">Affiliate:</span>
            <span class="font-medium text-foreground">{{ selectedPayout.affiliate?.name }}</span>
          </div>
          <div class="p-3 rounded-lg bg-muted/40 flex justify-between">
            <span class="text-muted-foreground">Amount:</span>
            <span class="font-bold text-primary">${{ Number(selectedPayout.amount).toFixed(2) }}</span>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="approveDialogOpen = false" :disabled="isApproving">
            {{ lang.dialogs.cancel }}
          </Button>
          <Button @click="confirmApprove" :disabled="isApproving">
            {{ isApproving ? 'Approving...' : lang.dialogs.confirm }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Complete Confirmation Dialog -->
    <Dialog v-model:open="completeDialogOpen">
      <DialogContent class="sm:max-w-md font-primary">
        <DialogHeader>
          <DialogTitle>{{ lang.dialogs.completeTitle }}</DialogTitle>
          <DialogDescription>
            {{ lang.dialogs.completeDescription }}
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedPayout" class="py-3 space-y-3 text-sm">
          <div class="p-3 rounded-lg bg-muted/40 space-y-1">
            <div class="flex justify-between">
              <span class="text-muted-foreground">Affiliate:</span>
              <span class="font-medium text-foreground">{{ selectedPayout.affiliate?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">Amount:</span>
              <span class="font-bold text-primary">${{ Number(selectedPayout.amount).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-xs pt-1 border-t border-border/30">
              <span class="text-muted-foreground">Wallet:</span>
              <span class="font-mono text-foreground">{{ selectedPayout.wallet_address }}</span>
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-medium text-foreground">
              {{ lang.dialogs.adminNoteOptional }}
            </label>
            <Input
              v-model="adminNote"
              :placeholder="lang.dialogs.adminNoteOptionalPlaceholder"
              class="bg-background/50 text-xs"
            />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="completeDialogOpen = false" :disabled="isCompleting">
            {{ lang.dialogs.cancel }}
          </Button>
          <Button @click="confirmComplete" :disabled="isCompleting">
            {{ isCompleting ? 'Completing...' : lang.dialogs.confirm }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Reject Confirmation Dialog -->
    <Dialog v-model:open="rejectDialogOpen">
      <DialogContent class="sm:max-w-md font-primary">
        <DialogHeader>
          <DialogTitle class="text-destructive">{{ lang.dialogs.rejectTitle }}</DialogTitle>
          <DialogDescription>
            {{ lang.dialogs.rejectDescription }}
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedPayout" class="py-3 space-y-3 text-sm">
          <div class="p-3 rounded-lg bg-destructive/10 border border-destructive/20 text-xs text-destructive">
            Warning: Covered commissions totaling ${{ Number(selectedPayout.amount).toFixed(2) }} will be unlinked and returned to available status.
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-medium text-foreground">
              {{ lang.dialogs.rejectReasonLabel }}
            </label>
            <Input
              v-model="rejectReason"
              :placeholder="lang.dialogs.rejectReasonPlaceholder"
              :class="{ 'border-destructive': rejectError }"
              class="bg-background/50 text-xs"
              @input="rejectError = ''"
            />
            <p v-if="rejectError" class="text-xs text-destructive mt-1">
              {{ rejectError }}
            </p>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="rejectDialogOpen = false" :disabled="isRejecting">
            {{ lang.dialogs.cancel }}
          </Button>
          <Button variant="destructive" @click="confirmReject" :disabled="isRejecting">
            {{ isRejecting ? 'Rejecting...' : lang.dialogs.confirm }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
