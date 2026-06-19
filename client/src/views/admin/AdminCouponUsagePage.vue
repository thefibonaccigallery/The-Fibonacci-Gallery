<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useAdminCoupons } from '@/composables/admin/useCoupons'
import { Button } from '@/components/ui/button'
import { PhArrowLeft, PhTicket, PhUsers, PhCheckCircle, PhXCircle } from '@phosphor-icons/vue'

const route = useRoute()
const router = useRouter()
const { fetchCouponUsage } = useAdminCoupons()

const couponUuid = computed(() => route.params.uuid as string)
const page = ref(1)

const { data, isLoading } = useQuery({
  queryKey: ['admin-coupon-usage', couponUuid, page],
  queryFn: () => fetchCouponUsage(couponUuid.value, { page: page.value, per_page: 15 }),
})

const coupon = computed(() => data.value?.coupon)
const enrollments = computed(() => data.value?.enrollments?.data || [])
const pagination = computed(() => ({
  current: data.value?.enrollments?.current_page || 1,
  last: data.value?.enrollments?.last_page || 1,
  total: data.value?.enrollments?.total || 0,
  from: data.value?.enrollments?.from || 0,
  to: data.value?.enrollments?.to || 0,
}))

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })

const isExpired = computed(() => {
  if (!coupon.value?.expires_at) return false
  return new Date(coupon.value.expires_at) < new Date()
})

const usagePercent = computed(() => {
  const c = coupon.value
  if (!c || c.max_uses === null) return null
  return Math.min(100, Math.round((c.enrollments_count / c.max_uses) * 100))
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="sm" @click="router.push('/admin/coupons')">
        <PhArrowLeft :size="18" class="mr-2" />
        Back to Coupons
      </Button>
      <h1 class="text-3xl font-bold text-foreground">Coupon Usage</h1>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading...</p>
    </div>

    <template v-else-if="coupon">
      <!-- Coupon Info Card -->
      <div class="liquid-glass rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
          <div class="space-y-1">
            <div class="flex items-center gap-3 mb-2">
              <PhTicket :size="24" class="text-primary" />
              <span class="font-mono text-2xl font-bold text-primary tracking-widest">
                {{ coupon.code }}
              </span>
              <span
                :class="[
                  'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                  coupon.is_active && !isExpired
                    ? 'bg-green-500/10 text-green-600'
                    : 'bg-red-500/10 text-red-500',
                ]"
              >
                <PhCheckCircle v-if="coupon.is_active && !isExpired" :size="12" class="mr-1" weight="fill" />
                <PhXCircle v-else :size="12" class="mr-1" weight="fill" />
                {{ coupon.is_active && !isExpired ? 'Active' : isExpired ? 'Expired' : 'Disabled' }}
              </span>
            </div>
            <p class="text-muted-foreground text-sm">
              Course: <span class="text-foreground font-medium">{{ coupon.course?.title }}</span>
            </p>
            <p class="text-muted-foreground text-sm">
              Discount:
              <span class="text-foreground font-medium">
                {{ Number(coupon.discount_percentage).toFixed(0) }}%
                <span v-if="Number(coupon.discount_percentage) === 100" class="text-green-500">(fully free)</span>
              </span>
            </p>
            <p v-if="coupon.expires_at" class="text-muted-foreground text-sm">
              Expires: <span class="text-foreground font-medium">{{ formatDate(coupon.expires_at) }}</span>
            </p>
          </div>

          <!-- Usage Stats -->
          <div class="liquid-glass rounded-xl p-4 min-w-[160px] text-center">
            <PhUsers :size="28" class="mx-auto text-primary mb-2" />
            <div class="text-3xl font-bold text-foreground">{{ coupon.enrollments_count }}</div>
            <div class="text-xs text-muted-foreground mt-1">
              {{ coupon.max_uses !== null ? `of ${coupon.max_uses} uses` : 'students enrolled' }}
            </div>
            <div v-if="usagePercent !== null" class="mt-3">
              <div class="flex-1 bg-muted rounded-full h-1.5">
                <div
                  class="h-1.5 rounded-full bg-primary transition-all"
                  :style="{ width: `${usagePercent}%` }"
                />
              </div>
              <p class="text-xs text-muted-foreground mt-1">{{ usagePercent }}% used</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Students Table -->
      <div class="liquid-glass rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border/40">
          <h2 class="text-lg font-semibold text-foreground">Students Who Used This Coupon</h2>
        </div>

        <!-- Empty -->
        <div v-if="enrollments.length === 0" class="p-12 text-center">
          <PhUsers :size="48" class="mx-auto text-muted-foreground mb-3" />
          <p class="text-muted-foreground">No students have used this coupon yet.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="border-b border-border/40">
              <tr class="text-left">
                <th class="px-4 py-3 text-muted-foreground font-medium">Student</th>
                <th class="px-4 py-3 text-muted-foreground font-medium">Email</th>
                <th class="px-4 py-3 text-muted-foreground font-medium">Enrolled</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border/20">
              <tr
                v-for="enrollment in enrollments"
                :key="enrollment.uuid"
                class="hover:bg-muted/30 transition-colors"
              >
                <td class="px-4 py-3 font-medium text-foreground">
                  {{ enrollment.user?.name }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                  {{ enrollment.user?.email }}
                </td>
                <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                  {{ formatDate(enrollment.enrolled_at) }}
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
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} students
        </p>
        <div class="flex gap-2">
          <Button @click="page--" :disabled="pagination.current === 1" variant="outline" size="sm">
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
    </template>
  </div>
</template>
