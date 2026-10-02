<script setup lang="ts">
import { useRouter } from 'vue-router'
import type { Student } from '@/types/StudentTypes'

interface Props {
  students: Student[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const router = useRouter()

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
      <h2 class="text-xl font-bold text-foreground">Recent Students</h2>
      <button
        @click="router.push('/admin/students')"
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
        <div class="h-8 w-8 bg-muted rounded-full"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="students.length === 0" class="py-12 text-center">
      <p class="text-muted-foreground text-sm">No students registered yet</p>
    </div>

    <template v-else>
      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-border/50">
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Name</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Email</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Country</th>
              <th class="text-center py-3 px-4 text-sm font-semibold text-muted-foreground">
                Enrolled Courses
              </th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">
                Joined Date
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="student in students"
              :key="student.uuid"
              class="border-b border-border/30 hover:bg-accent/30 transition-colors"
            >
              <td class="py-4 px-4 text-sm font-medium text-foreground">
                <div class="flex items-center gap-2">
                  {{ student.name }}
                  <span
                    v-if="student.is_banned"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-destructive/10 text-destructive"
                  >
                    Banned
                  </span>
                </div>
              </td>
              <td class="py-4 px-4 text-sm text-foreground/80">{{ student.email }}</td>
              <td class="py-4 px-4 text-sm text-foreground/80">{{ student.country || 'N/A' }}</td>
              <td class="py-4 px-4 text-sm text-center">
                <span
                  class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-medium"
                >
                  {{ student.enrollments_count ?? 0 }}
                </span>
              </td>
              <td class="py-4 px-4 text-sm text-muted-foreground whitespace-nowrap">
                {{ formatDate(student.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="md:hidden space-y-3">
        <div
          v-for="student in students"
          :key="student.uuid"
          class="p-4 rounded-lg bg-accent/20 border border-border/30"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <div class="flex items-center gap-2">
                <p class="font-medium text-foreground">{{ student.name }}</p>
                <span
                  v-if="student.is_banned"
                  class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-destructive/10 text-destructive"
                >
                  Banned
                </span>
              </div>
              <p class="text-sm text-muted-foreground">{{ student.email }}</p>
            </div>
            <span
              class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-medium text-sm"
            >
              {{ student.enrollments_count ?? 0 }}
            </span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-foreground/80">{{ student.country || 'N/A' }}</span>
            <span class="text-muted-foreground text-xs">{{ formatDate(student.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="flex items-center justify-between mt-6 pt-4 border-t border-border/50">
        <p class="text-sm text-muted-foreground">
          Showing latest {{ students.length }} {{ students.length === 1 ? 'student' : 'students' }}
        </p>
        <button
          @click="router.push('/admin/students')"
          class="px-3 py-1.5 text-sm rounded-lg border border-border/50 hover:bg-accent/50 transition-colors text-foreground"
        >
          View all students
        </button>
      </div>
    </template>
  </div>
</template>
