<script setup lang="ts">
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import BottomNav from '@/components/dashboard/BottomNav.vue'
import { useDashboard } from '@/composables/useDashboard'

const { activeTab, setActiveTab } = useDashboard()
</script>

<template>
  <div class="h-screen overflow-hidden bg-background flex flex-col md:flex-row font-primary">
    <!-- Desktop Sidebar -->
    <AppSidebar :activeTab="activeTab" @update:activeTab="setActiveTab" />

    <!-- Main Content Area -->
    <main class="flex-1 h-screen overflow-y-auto w-full relative">
      <slot />
    </main>

    <!-- Mobile Bottom Navigation -->
    <BottomNav :activeTab="activeTab" @update:activeTab="setActiveTab" />
  </div>
</template>
