<script setup lang="ts">
import { useAdminDashboard } from '@/composables/admin/useAdminDashboard'
import StatsCard from '@/components/admin/dashboard/StatsCard.vue'
import RecentOrdersTable from '@/components/admin/dashboard/RecentOrdersTable.vue'
import RecentStudentsTable from '@/components/admin/dashboard/RecentStudentsTable.vue'
import { PhUsers, PhBook, PhCurrencyDollar, PhShoppingCart, PhWarningCircle, PhArrowsClockwise } from '@phosphor-icons/vue'

const { stats, recentOrders, recentStudents, isLoading, isError, refetch } = useAdminDashboard()
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-foreground">Dashboard</h1>
        <p class="text-sm text-muted-foreground mt-1">Overview of platform performance and recent activity</p>
      </div>
      <button
        @click="() => refetch()"
        :disabled="isLoading"
        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border border-border/50 hover:bg-accent/40 text-foreground transition-colors disabled:opacity-50"
      >
        <PhArrowsClockwise :size="16" :class="{ 'animate-spin': isLoading }" />
        Refresh
      </button>
    </div>

    <!-- Error State Banner -->
    <div
      v-if="isError"
      class="p-4 rounded-xl bg-destructive/10 border border-destructive/20 flex items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3">
        <PhWarningCircle :size="24" class="text-destructive shrink-0" />
        <p class="text-sm text-foreground">
          Failed to load dashboard data. Please check your network connection and try again.
        </p>
      </div>
      <button
        @click="() => refetch()"
        class="px-3 py-1 text-xs font-semibold rounded-lg bg-destructive text-destructive-foreground hover:bg-destructive/90 transition-colors"
      >
        Retry
      </button>
    </div>

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
      <StatsCard
        :icon="PhUsers"
        label="Total Students"
        :value="stats.totalStudents"
        :trend="stats.studentsTrend"
        :loading="isLoading"
      />
      <StatsCard
        :icon="PhBook"
        label="Total Courses"
        :value="stats.totalCourses"
        :trend="stats.coursesTrend"
        :loading="isLoading"
      />
      <StatsCard
        :icon="PhCurrencyDollar"
        label="Total Revenue"
        :value="stats.totalRevenue"
        :format-as-currency="true"
        :trend="stats.revenueTrend"
        :loading="isLoading"
      />
      <StatsCard
        :icon="PhShoppingCart"
        label="Total Orders"
        :value="stats.totalOrders"
        :trend="stats.ordersTrend"
        :loading="isLoading"
      />
    </div>

    <!-- Recent Orders Table -->
    <RecentOrdersTable :orders="recentOrders" :loading="isLoading" />

    <!-- Recent Students Table -->
    <RecentStudentsTable :students="recentStudents" :loading="isLoading" />
  </div>
</template>
