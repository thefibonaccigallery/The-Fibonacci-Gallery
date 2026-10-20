<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useEnrollments } from '@/composables/admin/useEnrollments'
import type { Enrollment } from '@/types/EnrollmentTypes'
import { PhUserList } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog.vue'

const { fetchEnrollments, removeEnrollment, isRemoving } = useEnrollments()

const page = ref(1)
const search = ref('')
const filterCompleted = ref<boolean | undefined>(undefined)

const { data: enrollmentsData, isLoading } = useQuery({
  queryKey: ['admin-enrollments', page, search, filterCompleted],
  queryFn: () =>
    fetchEnrollments({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
      is_completed: filterCompleted.value,
    }),
})

const enrollments = computed(() => enrollmentsData.value?.data || [])
const pagination = computed(() => ({
  current: enrollmentsData.value?.current_page || 1,
  last: enrollmentsData.value?.last_page || 1,
  total: enrollmentsData.value?.total || 0,
  from: enrollmentsData.value?.from || 0,
  to: enrollmentsData.value?.to || 0,
}))

// Remove dialog
const removeDialogOpen = ref(false)
const enrollmentToRemove = ref<Enrollment | null>(null)

const openRemoveDialog = (enrollment: Enrollment) => {
  enrollmentToRemove.value = enrollment
  removeDialogOpen.value = true
}

const confirmRemove = () => {
  if (!enrollmentToRemove.value) return
  removeEnrollment(enrollmentToRemove.value.uuid, {
    onSuccess: () => {
      removeDialogOpen.value = false
      enrollmentToRemove.value = null
    },
  })
}

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const progressBarColor = (pct: number) => {
  if (pct >= 100) return 'bg-green-500'
  if (pct >= 50) return 'bg-primary'
  return 'bg-yellow-500'
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-foreground">Enrollments</h1>
    </div>

    <!-- Filters -->
    <div class="liquid-glass rounded-xl p-4 flex flex-col sm:flex-row gap-4">
      <Input v-model="search" placeholder="Search by student or course..." class="flex-1" @input="page = 1" />
      <div class="flex gap-2">
        <Button
          :variant="filterCompleted === undefined ? 'default' : 'outline'"
          size="sm"
          @click="filterCompleted = undefined; page = 1"
        >
          All
        </Button>
        <Button
          :variant="filterCompleted === false ? 'default' : 'outline'"
          size="sm"
          @click="filterCompleted = false; page = 1"
        >
          In Progress
        </Button>
        <Button
          :variant="filterCompleted === true ? 'default' : 'outline'"
          size="sm"
          @click="filterCompleted = true; page = 1"
        >
          Completed
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading enrollments...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="enrollments.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhUserList :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">No enrollments found</h3>
    </div>

    <!-- Enrollments Table -->
    <div v-else class="liquid-glass rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="border-b border-border/40">
            <tr class="text-left">
              <th class="px-4 py-3 text-muted-foreground font-medium">Student</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Course</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Progress</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Type</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Status</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Enrolled</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/20">
            <tr
              v-for="enrollment in enrollments"
              :key="enrollment.uuid"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3">
                <div class="font-medium text-foreground">{{ enrollment.user?.name }}</div>
                <div class="text-xs text-muted-foreground">{{ enrollment.user?.email }}</div>
              </td>
              <td class="px-4 py-3 max-w-[180px]">
                <div class="line-clamp-2 text-foreground">{{ enrollment.course?.title }}</div>
                <div v-if="enrollment.course?.level" class="text-xs text-muted-foreground capitalize">{{ enrollment.course?.level }}</div>
              </td>
              <td class="px-4 py-3 min-w-[120px]">
                <div class="flex items-center gap-2">
                  <div class="flex-1 bg-muted rounded-full h-2">
                    <div
                      :class="['h-2 rounded-full transition-all', progressBarColor(Number(enrollment.progress_percentage))]"
                      :style="{ width: `${Math.min(Number(enrollment.progress_percentage), 100)}%` }"
                    />
                  </div>
                  <span class="text-xs text-muted-foreground w-9 text-right">
                    {{ Number(enrollment.progress_percentage).toFixed(0) }}%
                  </span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    enrollment.type === 'paid'
                      ? 'bg-blue-500/10 text-blue-600'
                      : enrollment.type === 'coupon'
                        ? 'bg-purple-500/10 text-purple-600'
                        : 'bg-secondary/50 text-muted-foreground',
                  ]"
                >
                  {{ enrollment.type === 'paid' ? 'Paid' : enrollment.type === 'coupon' ? 'Coupon' : 'Free' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    enrollment.completed_at
                      ? 'bg-green-500/10 text-green-600'
                      : 'bg-yellow-500/10 text-yellow-600',
                  ]"
                >
                  {{ enrollment.completed_at ? 'Completed' : 'In Progress' }}
                </span>
              </td>
              <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                {{ formatDate(enrollment.enrolled_at) }}
              </td>
              <td class="px-4 py-3">
                <Button
                  variant="outline"
                  size="sm"
                  class="text-destructive hover:text-destructive"
                  @click="openRemoveDialog(enrollment)"
                >
                  Remove
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
        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} enrollments
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

    <!-- Remove Confirmation Dialog -->
    <ConfirmDialog
      v-model:open="removeDialogOpen"
      title="Remove Enrollment"
      description="This will revoke the student's access to the course. This action cannot be undone."
      confirm-text="Remove"
      cancel-text="Cancel"
      variant="destructive"
      :loading="isRemoving"
      @confirm="confirmRemove"
    />
  </div>
</template>
