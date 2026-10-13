<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useClipboard } from '@vueuse/core'
import QRCode from 'qrcode'
import { toast } from 'vue-sonner'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAffiliate } from '@/composables/useAffiliate'
import { useLanguageStore } from '@/stores/languageStore'
import type { CommissionStatus, PayoutStatus } from '@/types/AffiliateTypes'
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
  PhWallet,
  PhCoins,
  PhCheckCircle,
  PhUsersThree,
  PhCopy,
  PhCheck,
  PhQrCode,
  PhArrowUpRight,
  PhClock,
  PhXCircle,
  PhHandCoins,
} from '@phosphor-icons/vue'

const languageStore = useLanguageStore()
const lang = computed(() => languageStore.translations.affiliate)

const {
  summary,
  isSummaryLoading,
  payoutForm,
  errors,
  isPayoutDialogOpen,
  isSubmittingPayout,
  clearError,
  resetPayoutForm,
  handlePayoutSubmit,
  fetchCommissions,
  fetchPayouts,
} = useAffiliate()

// ── Tab Management ──
const activeTab = ref<'commissions' | 'payouts'>('commissions')

// ── Commissions Query ──
const commissionsPage = ref(1)
const { data: commissionsData, isLoading: isCommissionsLoading } = useQuery({
  queryKey: ['affiliate-commissions', commissionsPage],
  queryFn: () => fetchCommissions({ page: commissionsPage.value, per_page: 10 }),
})

const commissions = computed(() => commissionsData.value?.data || [])
const commissionsPagination = computed(() => ({
  current: commissionsData.value?.current_page || 1,
  last: commissionsData.value?.last_page || 1,
  total: commissionsData.value?.total || 0,
  from: commissionsData.value?.from || 0,
  to: commissionsData.value?.to || 0,
}))

// ── Payouts Query ──
const payoutsPage = ref(1)
const { data: payoutsData, isLoading: isPayoutsLoading } = useQuery({
  queryKey: ['affiliate-payouts', payoutsPage],
  queryFn: () => fetchPayouts({ page: payoutsPage.value, per_page: 10 }),
})

const payouts = computed(() => payoutsData.value?.data || [])
const payoutsPagination = computed(() => ({
  current: payoutsData.value?.current_page || 1,
  last: payoutsData.value?.last_page || 1,
  total: payoutsData.value?.total || 0,
  from: payoutsData.value?.from || 0,
  to: payoutsData.value?.to || 0,
}))

// ── Referral Link & Clipboard ──
const PROD_FRONTEND_URL =
  (import.meta.env.VITE_FRONTEND_URL as string) || 'https://the-fibonacci-gallery.com'

const isLocalHost = computed(() => {
  if (typeof window === 'undefined') return false
  return (
    window.location.hostname === 'localhost' ||
    window.location.hostname === '127.0.0.1' ||
    window.location.hostname.endsWith('.localhost')
  )
})

const referralLink = computed(() => {
  const code = summary.value?.referral_code
  if (!code) return summary.value?.referral_link || ''

  if (typeof window !== 'undefined') {
    // In development or when running on localhost, use the current active origin (e.g. http://localhost:5173)
    if (import.meta.env.DEV || isLocalHost.value) {
      return `${window.location.origin}/register?ref=${code}`
    }

    // In production, use backend referral_link if available and not localhost
    if (
      summary.value?.referral_link &&
      !summary.value.referral_link.includes('localhost') &&
      !summary.value.referral_link.includes('127.0.0.1')
    ) {
      return summary.value.referral_link
    }

    // Fallback to active non-localhost window origin or configured production frontend URL
    const prodOrigin = !isLocalHost.value ? window.location.origin : PROD_FRONTEND_URL
    return `${prodOrigin.replace(/\/+$/, '')}/register?ref=${code}`
  }

  return summary.value?.referral_link || `${PROD_FRONTEND_URL}/register?ref=${code}`
})

const { copy, copied } = useClipboard()
const handleCopyLink = () => {
  if (referralLink.value) {
    copy(referralLink.value)
    toast.success(lang.value.toasts.copySuccess)
  }
}

// ── QR Code ──
const qrCodeDataUrl = ref<string>('')
const showQrModal = ref(false)

const generateQrCode = async (text: string) => {
  if (!text) return
  try {
    qrCodeDataUrl.value = await QRCode.toDataURL(text, {
      width: 280,
      margin: 2,
      color: {
        dark: '#000000',
        light: '#ffffff',
      },
    })
  } catch (err) {
    console.error('Failed to generate QR code:', err)
  }
}

watch(
  referralLink,
  (newLink) => {
    if (newLink) {
      generateQrCode(newLink)
    }
  },
  { immediate: true },
)

// ── Helper formatters ──
const formatDate = (dateStr: string | null) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const commissionStatusBadge = (status: CommissionStatus) => {
  if (status === 'available') {
    return 'bg-primary/10 text-primary border border-primary/20'
  }
  return 'bg-blue-500/10 text-blue-500 border border-blue-500/20'
}

const payoutStatusBadge = (status: PayoutStatus) => {
  const map: Record<PayoutStatus, string> = {
    requested: 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20',
    approved: 'bg-blue-500/10 text-blue-500 border border-blue-500/20',
    completed: 'bg-primary/10 text-primary border border-primary/20',
    rejected: 'bg-destructive/10 text-destructive border border-destructive/20',
  }
  return map[status] ?? 'bg-muted text-muted-foreground'
}

const openPayoutDialog = () => {
  resetPayoutForm()
  isPayoutDialogOpen.value = true
}
</script>

<template>
  <DashboardLayout>
    <div class="w-full px-4 sm:px-6 lg:px-10 pt-8 pb-24 md:pb-12 space-y-8 font-primary">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">
              {{ lang.title }}
            </h1>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/15 text-primary border border-primary/30"
            >
              20% Flat Commission
            </span>
          </div>
          <p class="text-sm text-muted-foreground mt-1">
            {{ lang.subtitle }}
          </p>
        </div>

        <Button
          @click="openPayoutDialog"
          :disabled="!summary || summary.available_balance <= 0"
          class="shrink-0 font-medium"
        >
          <PhHandCoins :size="18" class="mr-2" />
          {{ lang.payout.requestButton }}
        </Button>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Earned -->
        <div class="liquid-glass rounded-2xl p-5 border border-border/20 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-muted-foreground">{{
              lang.stats.totalEarned
            }}</span>
            <div class="p-2 rounded-xl bg-primary/10 text-primary">
              <PhWallet :size="20" />
            </div>
          </div>
          <div>
            <div v-if="isSummaryLoading" class="h-8 w-24 bg-muted/30 rounded animate-pulse" />
            <div v-else class="text-2xl sm:text-3xl font-bold text-foreground">
              ${{ Number(summary?.total_earned ?? 0).toFixed(2) }}
            </div>
            <p class="text-[11px] text-muted-foreground mt-1">Lifetime affiliate earnings</p>
          </div>
        </div>

        <!-- Available Balance -->
        <div
          class="liquid-glass rounded-2xl p-5 border border-primary/30 bg-primary/5 space-y-3 relative overflow-hidden"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-primary">{{ lang.stats.availableBalance }}</span>
            <div class="p-2 rounded-xl bg-primary/20 text-primary">
              <PhCoins :size="20" />
            </div>
          </div>
          <div>
            <div v-if="isSummaryLoading" class="h-8 w-24 bg-muted/30 rounded animate-pulse" />
            <div v-else class="text-2xl sm:text-3xl font-bold text-primary">
              ${{ Number(summary?.available_balance ?? 0).toFixed(2) }}
            </div>
            <p class="text-[11px] text-muted-foreground mt-1">Ready for manual payout</p>
          </div>
        </div>

        <!-- Paid Out -->
        <div class="liquid-glass rounded-2xl p-5 border border-border/20 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-muted-foreground">{{ lang.stats.paidOut }}</span>
            <div class="p-2 rounded-xl bg-blue-500/10 text-blue-500">
              <PhCheckCircle :size="20" />
            </div>
          </div>
          <div>
            <div v-if="isSummaryLoading" class="h-8 w-24 bg-muted/30 rounded animate-pulse" />
            <div v-else class="text-2xl sm:text-3xl font-bold text-foreground">
              ${{ Number(summary?.paid_out ?? 0).toFixed(2) }}
            </div>
            <p class="text-[11px] text-muted-foreground mt-1">Completed withdrawals</p>
          </div>
        </div>

        <!-- Total Referrals -->
        <div class="liquid-glass rounded-2xl p-5 border border-border/20 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-muted-foreground">{{
              lang.stats.totalReferrals
            }}</span>
            <div class="p-2 rounded-xl bg-secondary/50 text-foreground">
              <PhUsersThree :size="20" />
            </div>
          </div>
          <div>
            <div v-if="isSummaryLoading" class="h-8 w-16 bg-muted/30 rounded animate-pulse" />
            <div v-else class="text-2xl sm:text-3xl font-bold text-foreground">
              {{ summary?.total_referrals ?? 0 }}
            </div>
            <p class="text-[11px] text-muted-foreground mt-1">Students registered via link</p>
          </div>
        </div>
      </div>

      <!-- Referral Link & QR Code Banner -->
      <div class="liquid-glass rounded-2xl p-6 border border-border/20 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div class="space-y-2 flex-1">
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-semibold text-foreground">
                {{ lang.referralCard.title }}
              </h2>
              <span
                v-if="summary?.referral_code"
                class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-secondary text-primary border border-border/40"
              >
                CODE: {{ summary.referral_code }}
              </span>
            </div>
            <p class="text-sm text-muted-foreground max-w-2xl">
              {{ lang.referralCard.description }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <Button variant="outline" size="sm" @click="showQrModal = true" class="h-10">
              <PhQrCode :size="18" class="mr-2 text-primary" />
              {{ lang.referralCard.qrCode }}
            </Button>
          </div>
        </div>

        <!-- Link Input Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div
            class="flex-1 flex items-center px-4 py-2.5 rounded-xl bg-background/50 border border-border/40 text-sm font-mono text-foreground overflow-x-auto select-all"
          >
            <span class="truncate">{{ referralLink || 'Loading link...' }}</span>
          </div>

          <Button @click="handleCopyLink" size="default" class="h-10 shrink-0">
            <PhCheck v-if="copied" :size="18" class="mr-2 text-primary-foreground" />
            <PhCopy v-else :size="18" class="mr-2" />
            <span>{{ copied ? lang.referralCard.copied : lang.referralCard.copy }}</span>
          </Button>
        </div>
      </div>

      <!-- Tables Section with Tabs -->
      <div class="space-y-4">
        <!-- Tab Headers -->
        <div class="flex items-center gap-2 border-b border-border/20 pb-2">
          <button
            @click="activeTab = 'commissions'"
            :class="[
              'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
              activeTab === 'commissions'
                ? 'bg-primary/10 text-primary border border-primary/30'
                : 'text-muted-foreground hover:text-foreground hover:bg-secondary/40',
            ]"
          >
            {{ lang.tabs.commissions }}
          </button>
          <button
            @click="activeTab = 'payouts'"
            :class="[
              'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
              activeTab === 'payouts'
                ? 'bg-primary/10 text-primary border border-primary/30'
                : 'text-muted-foreground hover:text-foreground hover:bg-secondary/40',
            ]"
          >
            {{ lang.tabs.payouts }}
          </button>
        </div>

        <!-- Commissions Tab Content -->
        <div v-if="activeTab === 'commissions'" class="space-y-4">
          <!-- Loading -->
          <div v-if="isCommissionsLoading" class="text-center py-12">
            <div
              class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mx-auto"
            ></div>
            <p class="text-muted-foreground text-sm mt-3">Loading commissions...</p>
          </div>

          <!-- Empty -->
          <div
            v-else-if="commissions.length === 0"
            class="liquid-glass rounded-2xl p-12 text-center border border-border/20"
          >
            <PhCoins :size="48" class="mx-auto text-muted-foreground mb-3" />
            <p class="text-base font-semibold text-foreground">
              {{ lang.commissionsTable.empty }}
            </p>
          </div>

          <!-- Table -->
          <div v-else class="liquid-glass rounded-2xl overflow-hidden border border-border/20">
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="border-b border-border/30 bg-muted/20">
                  <tr
                    class="text-left text-xs uppercase tracking-wider text-muted-foreground font-medium"
                  >
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.student }}</th>
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.course }}</th>
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.orderAmount }}</th>
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.rate }}</th>
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.commission }}</th>
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.status }}</th>
                    <th class="px-4 py-3.5">{{ lang.commissionsTable.date }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border/20">
                  <tr
                    v-for="item in commissions"
                    :key="item.uuid"
                    class="hover:bg-muted/20 transition-colors"
                  >
                    <td class="px-4 py-3.5">
                      <div class="font-medium text-foreground">
                        {{ item.referred_user?.name ?? 'Student' }}
                      </div>
                      <div class="text-xs text-muted-foreground">
                        {{ item.referred_user?.email }}
                      </div>
                    </td>
                    <td class="px-4 py-3.5 max-w-50">
                      <span class="line-clamp-1 text-foreground font-medium">
                        {{ item.order?.course?.title ?? 'Trading Course' }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5 text-muted-foreground">
                      ${{ Number(item.order_amount).toFixed(2) }}
                    </td>
                    <td class="px-4 py-3.5 text-muted-foreground">
                      {{ Number(item.commission_rate).toFixed(0) }}%
                    </td>
                    <td class="px-4 py-3.5 font-semibold text-primary">
                      +${{ Number(item.amount).toFixed(2) }}
                    </td>
                    <td class="px-4 py-3.5">
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                          commissionStatusBadge(item.status),
                        ]"
                      >
                        {{ item.status.replace('_', ' ') }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted-foreground whitespace-nowrap">
                      {{ formatDate(item.created_at) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div
              v-if="commissionsPagination.last > 1"
              class="flex items-center justify-between p-4 border-t border-border/20"
            >
              <p class="text-xs text-muted-foreground">
                Showing {{ commissionsPagination.from }} to {{ commissionsPagination.to }} of
                {{ commissionsPagination.total }}
              </p>
              <div class="flex gap-2">
                <Button
                  @click="commissionsPage--"
                  :disabled="commissionsPagination.current === 1"
                  variant="outline"
                  size="sm"
                >
                  Previous
                </Button>
                <Button
                  @click="commissionsPage++"
                  :disabled="commissionsPagination.current === commissionsPagination.last"
                  variant="outline"
                  size="sm"
                >
                  Next
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Payouts Tab Content -->
        <div v-if="activeTab === 'payouts'" class="space-y-4">
          <!-- Loading -->
          <div v-if="isPayoutsLoading" class="text-center py-12">
            <div
              class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mx-auto"
            ></div>
            <p class="text-muted-foreground text-sm mt-3">Loading payout history...</p>
          </div>

          <!-- Empty -->
          <div
            v-else-if="payouts.length === 0"
            class="liquid-glass rounded-2xl p-12 text-center border border-border/20"
          >
            <PhHandCoins :size="48" class="mx-auto text-muted-foreground mb-3" />
            <p class="text-base font-semibold text-foreground">
              {{ lang.payoutsTable.empty }}
            </p>
          </div>

          <!-- Table -->
          <div v-else class="liquid-glass rounded-2xl overflow-hidden border border-border/20">
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="border-b border-border/30 bg-muted/20">
                  <tr
                    class="text-left text-xs uppercase tracking-wider text-muted-foreground font-medium"
                  >
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.amount }}</th>
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.wallet }}</th>
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.network }}</th>
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.status }}</th>
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.note }}</th>
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.requested }}</th>
                    <th class="px-4 py-3.5">{{ lang.payoutsTable.processed }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border/20">
                  <tr
                    v-for="payout in payouts"
                    :key="payout.uuid"
                    class="hover:bg-muted/20 transition-colors"
                  >
                    <td class="px-4 py-3.5 font-bold text-foreground whitespace-nowrap">
                      ${{ Number(payout.amount).toFixed(2) }}
                    </td>
                    <td class="px-4 py-3.5 max-w-50">
                      <span
                        class="font-mono text-xs text-muted-foreground truncate block select-all"
                      >
                        {{ payout.wallet_address }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-foreground whitespace-nowrap">
                      {{ payout.network }}
                    </td>
                    <td class="px-4 py-3.5">
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                          payoutStatusBadge(payout.status),
                        ]"
                      >
                        {{ payout.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted-foreground max-w-45">
                      <span class="line-clamp-1">{{ payout.admin_note ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted-foreground whitespace-nowrap">
                      {{ formatDate(payout.created_at) }}
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted-foreground whitespace-nowrap">
                      {{ formatDate(payout.processed_at) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div
              v-if="payoutsPagination.last > 1"
              class="flex items-center justify-between p-4 border-t border-border/20"
            >
              <p class="text-xs text-muted-foreground">
                Showing {{ payoutsPagination.from }} to {{ payoutsPagination.to }} of
                {{ payoutsPagination.total }}
              </p>
              <div class="flex gap-2">
                <Button
                  @click="payoutsPage--"
                  :disabled="payoutsPagination.current === 1"
                  variant="outline"
                  size="sm"
                >
                  Previous
                </Button>
                <Button
                  @click="payoutsPage++"
                  :disabled="payoutsPagination.current === payoutsPagination.last"
                  variant="outline"
                  size="sm"
                >
                  Next
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Request Payout Modal Dialog -->
    <Dialog v-model:open="isPayoutDialogOpen">
      <DialogContent class="sm:max-w-md font-primary">
        <DialogHeader>
          <DialogTitle>{{ lang.payout.dialogTitle }}</DialogTitle>
          <DialogDescription>
            {{ lang.payout.dialogDescription }}
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handlePayoutSubmit" class="space-y-4 py-2">
          <!-- Available Amount Display -->
          <div
            class="p-4 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-between"
          >
            <span class="text-sm font-medium text-foreground">{{
              lang.payout.availableLabel
            }}</span>
            <span class="text-xl font-bold text-primary">
              ${{ Number(summary?.available_balance ?? 0).toFixed(2) }}
            </span>
          </div>

          <!-- Network (USDT BEP20 Only) -->
          <div class="space-y-1.5">
            <label class="text-xs font-medium text-foreground">{{
              lang.payout.networkLabel
            }}</label>
            <Input
              value="USDT (BEP20) - BNB Smart Chain"
              disabled
              class="bg-muted/40 text-muted-foreground font-medium cursor-not-allowed"
            />
          </div>

          <!-- Wallet Address -->
          <div class="space-y-1.5">
            <label for="payout-wallet" class="text-xs font-medium text-foreground">
              {{ lang.payout.walletLabel }}
            </label>
            <Input
              id="payout-wallet"
              v-model="payoutForm.state.values.wallet_address"
              :placeholder="lang.payout.walletPlaceholder"
              :class="{ 'border-destructive': errors.wallet_address }"
              class="bg-background/50 font-mono text-xs"
              @input="clearError('wallet_address')"
            />
            <p v-if="errors.wallet_address" class="text-xs text-destructive mt-1">
              {{ errors.wallet_address }}
            </p>
          </div>

          <!-- Warning Notice -->
          <div
            class="p-3 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-xs text-yellow-500 leading-relaxed"
          >
            {{ lang.payout.networkNote }}
          </div>

          <DialogFooter class="pt-3">
            <Button
              type="button"
              variant="outline"
              @click="isPayoutDialogOpen = false"
              :disabled="isSubmittingPayout"
            >
              {{ lang.payout.cancel }}
            </Button>
            <Button type="submit" :disabled="isSubmittingPayout">
              {{ isSubmittingPayout ? lang.payout.submitting : lang.payout.submit }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- QR Code Modal Dialog -->
    <Dialog v-model:open="showQrModal">
      <DialogContent class="sm:max-w-xs text-center font-primary">
        <DialogHeader>
          <DialogTitle>{{ lang.referralCard.qrCode }}</DialogTitle>
          <DialogDescription class="text-xs">
            Scan to register with referral code {{ summary?.referral_code }}
          </DialogDescription>
        </DialogHeader>

        <div class="py-4 flex flex-col items-center justify-center">
          <div class="p-3 rounded-2xl bg-white shadow-md inline-block">
            <img
              v-if="qrCodeDataUrl"
              :src="qrCodeDataUrl"
              alt="Affiliate QR Code"
              class="w-56 h-56 rounded-lg"
            />
          </div>
          <p class="text-xs font-mono text-primary font-bold mt-3">
            CODE: {{ summary?.referral_code }}
          </p>
        </div>

        <DialogFooter>
          <Button class="w-full" variant="outline" @click="showQrModal = false"> Close </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </DashboardLayout>
</template>
