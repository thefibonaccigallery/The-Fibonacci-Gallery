<script setup lang="ts">
import { computed } from 'vue'
import { useAdminDashboard } from '@/composables/admin/useAdminDashboard'
import StatsCard from '@/components/admin/dashboard/StatsCard.vue'
import RecentOrdersTable from '@/components/admin/dashboard/RecentOrdersTable.vue'
import RecentStudentsTable from '@/components/admin/dashboard/RecentStudentsTable.vue'
import { PhUsers, PhBook, PhCurrencyDollar, PhShoppingCart } from '@phosphor-icons/vue'

const { stats, recentOrders, recentStudents } = useAdminDashboard()
</script>

<template>
  <div class="space-y-6">
    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
      <StatsCard
        :icon="PhUsers"
        label="Total Students"
        :value="stats.totalStudents"
        trend="+12% this month"
      />
      <StatsCard
        :icon="PhBook"
        label="Total Courses"
        :value="stats.totalCourses"
        trend="+2 new courses"
      />
      <StatsCard
        :icon="PhCurrencyDollar"
        label="Total Revenue"
        :value="stats.totalRevenue"
        :format-as-currency="true"
        trend="+18% this month"
      />
      <StatsCard
        :icon="PhShoppingCart"
        label="Total Orders"
        :value="stats.totalOrders"
        trend="+24 this week"
      />
    </div>

    <!-- Recent Orders Table -->
    <RecentOrdersTable :orders="recentOrders" />

    <!-- Recent Students Table -->
    <RecentStudentsTable :students="recentStudents" />
  </div>
</template>
