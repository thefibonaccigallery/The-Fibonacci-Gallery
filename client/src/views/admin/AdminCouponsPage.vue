<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useAdminCoupons } from '@/composables/admin/useCoupons'
import type { Coupon } from '@/types/CouponTypes'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog.vue'
import { PhTicket, PhPencil, PhTrash, PhUsers, PhToggleLeft, PhToggleRight } from '@phosphor-icons/vue'

const router = useRouter()
const { fetchCoupons, deleteCoupon, toggleCoupon, isDeleting, isToggling } = useAdminCoupons()

const page = ref(1)
const search = ref('')
const filterActive = ref<boolean | undefined>(undefined)

const { data: couponsData, isLoading } = useQuery({
  queryKey: ['admin-coupons', page, search, filterActive],
  queryFn: () =>
    fetchCoupons({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
      is_active: filterActive.value,
    }),
})

const coupons = computed(() => couponsData.value?.data || [])
const pagination = computed(() => ({
  current: couponsData.value?.current_page || 1,
  last: couponsData.value?.last_page || 1,
  total: couponsData.value?.total || 0,
  from: couponsData.value?.from || 0,
  to: couponsData.value?.to || 0,
}))

// Delete dialog
const deleteDialogOpen = ref(false)
const couponToDelete = ref<Coupon | null>(null)

const openDeleteDialog = (coupon: Coupon) => {
  couponToDelete.value = coupon
  deleteDialogOpen.value = true
}

const confirmDelete = () => {
  if (!couponToDelete.value) return
  deleteCoupon(couponToDelete.value.uuid, {
    onSuccess: () => {
      deleteDialogOpen.value = false
      couponToDelete.value = null
    },
  })
}

const handleToggle = (coupon: Coupon) => {
  toggleCoupon(coupon.uuid)
}

const formatDate = (date: string | null) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-3xl font-bold text-foreground">Coupons</h1>
      <Button @click="router.push('/admin/coupons/create')">
        <PhTicket :size="18" class="mr-2" />
        Create Coupon
      </Button>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 flex flex-col sm:flex-row gap-4">
      <Input
        v-model="search"
        placeholder="Search by coupon code..."
        class="flex-1"
        @input="page = 1"
      />
      <div class="flex gap-2">
        <Button
          :variant="filterActive === undefined ? 'default' : 'outline'"
          size="sm"
          @click="filterActive = undefined; page = 1"
        >
          All
        </Button>
        <Button
          :variant="filterActive === true ? 'default' : 'outline'"
          size="sm"
          @click="filterActive = true; page = 1"
        >
          Active
        </Button>
        <Button
          :variant="filterActive === false ? 'default' : 'outline'"
          size="sm"
          @click="filterActive = false; page = 1"
        >
          Disabled
        </Button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading coupons...</p>
    </div>

    <!-- Empty -->
    <div v-else-if="coupons.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhTicket :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">No coupons found</h3>
      <p class="text-muted-foreground mb-6">Create your first coupon to offer discounts on courses.</p>
      <Button @click="router.push('/admin/coupons/create')">Create Coupon</Button>
    </div>

    <!-- Table -->
    <div v-else class="liquid-glass rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="border-b border-border/40">
            <tr class="text-left">
              <th class="px-4 py-3 text-muted-foreground font-medium">Code</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Course</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Discount</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Uses</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Expires</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Status</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/20">
            <tr
              v-for="coupon in coupons"
              :key="coupon.uuid"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3">
                <span class="font-mono font-semibold text-primary tracking-wider">
                  {{ coupon.code }}
                </span>
              </td>
              <td class="px-4 py-3 max-w-[160px]">
                <span class="line-clamp-1 text-foreground">{{ coupon.course?.title }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="font-semibold text-foreground">
                  {{ Number(coupon.discount_percentage).toFixed(0) }}%
                </span>
                <span v-if="Number(coupon.discount_percentage) === 100" class="ml-1 text-xs text-green-500">
                  (free)
                </span>
              </td>
              <td class="px-4 py-3 text-foreground">
                <span class="font-medium">{{ coupon.enrollments_count }}</span>
                <span class="text-muted-foreground">
                  {{ coupon.max_uses !== null ? ` / ${coupon.max_uses}` : ' / ∞' }}
                </span>
              </td>
              <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                {{ formatDate(coupon.expires_at) }}
              </td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    coupon.is_active
                      ? 'bg-green-500/10 text-green-600'
                      : 'bg-red-500/10 text-red-500',
                  ]"
                >
                  {{ coupon.is_active ? 'Active' : 'Disabled' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0"
                    title="View usage"
                    @click="router.push(`/admin/coupons/${coupon.uuid}/usage`)"
                  >
                    <PhUsers :size="16" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0"
                    title="Edit coupon"
                    @click="router.push(`/admin/coupons/${coupon.uuid}/edit`)"
                  >
                    <PhPencil :size="16" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0"
                    :title="coupon.is_active ? 'Disable coupon' : 'Enable coupon'"
                    :disabled="isToggling"
                    @click="handleToggle(coupon)"
                  >
                    <PhToggleRight v-if="coupon.is_active" :size="16" class="text-green-500" />
                    <PhToggleLeft v-else :size="16" class="text-muted-foreground" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                    title="Delete coupon"
                    @click="openDeleteDialog(coupon)"
                  >
                    <PhTrash :size="16" />
                  </Button>
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
      class="flex items-center justify-between liquid-glass rounded-xl p-4"
    >
      <p class="text-sm text-muted-foreground">
        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} coupons
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

    <!-- Delete Confirmation -->
    <ConfirmDialog
      v-model:open="deleteDialogOpen"
      title="Delete Coupon"
      :description="`Are you sure you want to delete coupon &quot;${couponToDelete?.code}&quot;? This action cannot be undone.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="destructive"
      :loading="isDeleting"
      @confirm="confirmDelete"
    />
  </div>
</template>
