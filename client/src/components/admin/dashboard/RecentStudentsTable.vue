<script setup lang="ts">
import type { Student } from '@/composables/admin/useAdminDashboard'

interface Props {
  students: Student[]
}

const props = defineProps<Props>()

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
      <h2 class="text-xl font-bold text-foreground">Recent Students</h2>
      <button class="text-sm text-primary hover:underline">View All</button>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border/50">
            <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Name</th>
            <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Email</th>
            <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">Country</th>
            <th class="text-left py-3 px-4 text-sm font-semibold text-muted-foreground">
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
            :key="student.id"
            class="border-b border-border/30 hover:bg-accent/30 transition-colors"
          >
            <td class="py-4 px-4 text-sm font-medium text-foreground">{{ student.name }}</td>
            <td class="py-4 px-4 text-sm text-foreground/80">{{ student.email }}</td>
            <td class="py-4 px-4 text-sm text-foreground/80">{{ student.country }}</td>
            <td class="py-4 px-4 text-sm text-center">
              <span
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-medium"
              >
                {{ student.enrolledCourses }}
              </span>
            </td>
            <td class="py-4 px-4 text-sm text-muted-foreground">
              {{ formatDate(student.joinedAt) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
      <div
        v-for="student in students"
        :key="student.id"
        class="p-4 rounded-lg bg-accent/20 border border-border/30"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <p class="font-medium text-foreground">{{ student.name }}</p>
            <p class="text-sm text-muted-foreground">{{ student.email }}</p>
          </div>
          <span
            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-medium text-sm"
          >
            {{ student.enrolledCourses }}
          </span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-foreground/80">{{ student.country }}</span>
          <span class="text-muted-foreground">{{ formatDate(student.joinedAt) }}</span>
        </div>
      </div>
    </div>

    <!-- Pagination Placeholder -->
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-border/50">
      <p class="text-sm text-muted-foreground">Showing 1-5 of {{ students.length }} students</p>
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
