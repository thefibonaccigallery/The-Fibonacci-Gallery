<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useStudents } from '@/composables/admin/useStudents'
import { useEnrollments } from '@/composables/admin/useEnrollments'
import type { Student } from '@/types/StudentTypes'
import type { Enrollment } from '@/types/EnrollmentTypes'
import { PhUsers } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog'

const { fetchStudents } = useStudents()
const { fetchEnrollments } = useEnrollments()

const page = ref(1)
const search = ref('')

const { data: studentsData, isLoading } = useQuery({
  queryKey: ['admin-students', page, search],
  queryFn: () =>
    fetchStudents({
      page: page.value,
      per_page: 15,
      search: search.value || undefined,
    }),
})

const students = computed(() => studentsData.value?.data || [])
const pagination = computed(() => ({
  current: studentsData.value?.current_page || 1,
  last: studentsData.value?.last_page || 1,
  total: studentsData.value?.total || 0,
  from: studentsData.value?.from || 0,
  to: studentsData.value?.to || 0,
}))

// View enrollments dialog
const enrollmentsDialogOpen = ref(false)
const selectedStudent = ref<Student | null>(null)

const { data: studentEnrollmentsData, isLoading: isLoadingEnrollments } = useQuery({
  queryKey: ['admin-student-enrollments', computed(() => selectedStudent.value?.email)],
  queryFn: () =>
    fetchEnrollments({
      search: selectedStudent.value?.email,
      per_page: 100,
    }),
  enabled: computed(() => !!selectedStudent.value),
})

const studentEnrollments = computed<Enrollment[]>(
  () => studentEnrollmentsData.value?.data || [],
)

const openEnrollmentsDialog = (student: Student) => {
  selectedStudent.value = student
  enrollmentsDialogOpen.value = true
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
      <h1 class="text-3xl font-bold text-foreground">Students</h1>
    </div>

    <!-- Search -->
    <div class="liquid-glass rounded-xl p-4">
      <Input v-model="search" placeholder="Search by name or email..." @input="page = 1" />
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="text-muted-foreground mt-4">Loading students...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="students.length === 0" class="liquid-glass rounded-xl p-12 text-center">
      <PhUsers :size="64" class="mx-auto text-muted-foreground mb-4" />
      <h3 class="text-xl font-semibold text-foreground mb-2">No students found</h3>
    </div>

    <!-- Students Table -->
    <div v-else class="liquid-glass rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="border-b border-border/40">
            <tr class="text-left">
              <th class="px-4 py-3 text-muted-foreground font-medium">Name</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Email</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Country</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Courses</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Joined</th>
              <th class="px-4 py-3 text-muted-foreground font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/20">
            <tr
              v-for="student in students"
              :key="student.uuid"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3 font-medium text-foreground">{{ student.name }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ student.email }}</td>
              <td class="px-4 py-3 text-muted-foreground">{{ student.country ?? 'N/A' }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                  {{ student.enrollments_count }}
                </span>
              </td>
              <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                {{ formatDate(student.created_at) }}
              </td>
              <td class="px-4 py-3">
                <Button
                  variant="outline"
                  size="sm"
                  @click="openEnrollmentsDialog(student)"
                >
                  View Enrollments
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

    <!-- View Enrollments Dialog -->
    <Dialog v-model:open="enrollmentsDialogOpen">
      <DialogContent class="max-w-lg">
        <DialogHeader>
          <DialogTitle>{{ selectedStudent?.name }}'s Enrollments</DialogTitle>
          <DialogDescription>{{ selectedStudent?.email }}</DialogDescription>
        </DialogHeader>

        <div class="py-2">
          <!-- Loading -->
          <div v-if="isLoadingEnrollments" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
          </div>

          <!-- Empty -->
          <p v-else-if="studentEnrollments.length === 0" class="text-center text-muted-foreground py-6">
            No courses enrolled yet.
          </p>

          <!-- List -->
          <ul v-else class="space-y-3">
            <li
              v-for="enrollment in studentEnrollments"
              :key="enrollment.uuid"
              class="rounded-lg border border-border/40 p-3 space-y-2"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="font-medium text-foreground text-sm">{{ enrollment.course?.title }}</p>
                  <p class="text-xs text-muted-foreground capitalize">{{ enrollment.course?.level }}</p>
                </div>
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium shrink-0',
                    enrollment.completed_at
                      ? 'bg-green-500/10 text-green-600'
                      : 'bg-yellow-500/10 text-yellow-600',
                  ]"
                >
                  {{ enrollment.completed_at ? 'Completed' : 'In Progress' }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <div class="flex-1 bg-muted rounded-full h-1.5">
                  <div
                    :class="['h-1.5 rounded-full transition-all', progressBarColor(Number(enrollment.progress_percentage))]"
                    :style="{ width: `${Math.min(Number(enrollment.progress_percentage), 100)}%` }"
                  />
                </div>
                <span class="text-xs text-muted-foreground w-9 text-right">
                  {{ Number(enrollment.progress_percentage).toFixed(0) }}%
                </span>
              </div>
              <p class="text-xs text-muted-foreground">Enrolled {{ formatDate(enrollment.enrolled_at) }}</p>
            </li>
          </ul>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>
