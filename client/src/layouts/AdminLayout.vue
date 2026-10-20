<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminSidebar from '@/components/admin/layout/AdminSidebar.vue'
import AdminHeader from '@/components/admin/layout/AdminHeader.vue'
import AdminBottomNav from '@/components/admin/layout/AdminBottomNav.vue'
import {
  PhHouse,
  PhBook,
  PhBookOpen,
  PhVideo,
  PhUsers,
  PhUserList,
  PhShoppingCart,
  PhGear,
  PhTicket,
  PhHandCoins,
  PhUserCircle,
} from '@phosphor-icons/vue'
import logo from '@/assets/images/white_final.webp'

import AppFooter from '@/components/layout/AppFooter.vue'

const route = useRoute()
const authStore = useAuthStore()
const showMobileSidebar = ref(false)

const navSections = [
  {
    label: 'MAIN',
    items: [{ name: 'Dashboard', path: '/admin/dashboard', icon: PhHouse }],
  },
  {
    label: 'CONTENT',
    items: [
      { name: 'Courses', path: '/admin/courses', icon: PhBook },
      { name: 'Chapters', path: '/admin/chapters', icon: PhBookOpen },
      { name: 'Videos', path: '/admin/videos', icon: PhVideo },
    ],
  },
  {
    label: 'STUDENTS',
    items: [
      { name: 'Students', path: '/admin/students', icon: PhUsers },
      { name: 'Enrollments', path: '/admin/enrollments', icon: PhUserList },
      { name: 'Coupons', path: '/admin/coupons', icon: PhTicket },
    ],
  },
  {
    label: 'PAYMENTS',
    items: [
      { name: 'Orders', path: '/admin/orders', icon: PhShoppingCart },
      { name: 'Affiliate Payouts', path: '/admin/payouts', icon: PhHandCoins },
    ],
  },
  {
    label: 'SETTINGS',
    items: [{ name: 'Settings', path: '/admin/settings', icon: PhGear }],
  },
]

const toggleMobileSidebar = () => {
  showMobileSidebar.value = !showMobileSidebar.value
}
</script>

<template>
  <div class="h-screen overflow-hidden bg-background flex flex-col lg:flex-row font-primary">
    <!-- Desktop Sidebar -->
    <AdminSidebar />

    <!-- Mobile Sidebar Overlay -->
    <Transition name="overlay">
      <div
        v-if="showMobileSidebar"
        @click="showMobileSidebar = false"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"
      ></div>
    </Transition>

    <!-- Mobile Sidebar -->
    <Transition name="slide">
      <aside
        v-if="showMobileSidebar"
        class="fixed left-0 top-0 h-screen w-64 z-50 lg:hidden liquid-glass flex flex-col"
      >
        <!-- Logo & Platform Name -->
        <div class="flex items-center gap-3 p-6 border-b border-border/50">
          <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/30 shrink-0">
            <img :src="logo" alt="The Fibonacci Gallery" class="w-6 h-6 object-contain rounded-md" />
          </div>
          <div class="flex flex-col">
            <span class="font-semibold text-sm text-foreground leading-tight">The Fibonacci</span>
            <span class="text-xs text-muted-foreground leading-tight">Gallery</span>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-6">
          <div v-for="section in navSections" :key="section.label" class="space-y-1">
            <h3 class="px-3 text-xs font-semibold text-muted-foreground mb-2">
              {{ section.label }}
            </h3>

            <RouterLink
              v-for="item in section.items"
              :key="item.path"
              :to="item.path"
              @click="showMobileSidebar = false"
              :class="[
                'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200',
                'hover:bg-accent/50',
                route.path === item.path
                  ? 'bg-primary/10 text-primary font-medium'
                  : 'text-foreground/70 hover:text-foreground',
              ]"
            >
              <component :is="item.icon" :size="20" weight="regular" class="shrink-0" />
              <span class="text-sm">{{ item.name }}</span>
            </RouterLink>
          </div>
        </nav>

        <!-- Bottom Section -->
        <div class="p-4 border-t border-border/50 space-y-3">
          <RouterLink
            to="/admin/settings"
            @click="showMobileSidebar = false"
            class="block px-3 py-2 rounded-lg hover:bg-accent/40 transition-colors group"
          >
            <p class="text-sm font-medium text-foreground group-hover:text-primary transition-colors truncate">
              {{ authStore.user?.name || 'Admin User' }}
            </p>
            <p class="text-xs text-muted-foreground truncate">
              {{ authStore.user?.email || 'Administrator' }}
            </p>
          </RouterLink>
        </div>
      </aside>
    </Transition>

    <!-- Main Content Area -->
    <main class="flex-1 h-screen overflow-y-auto w-full relative flex flex-col justify-between">
      <div>
        <!-- Header -->
        <AdminHeader @toggle-mobile-sidebar="toggleMobileSidebar" />

        <!-- Page Content -->
        <div class="w-full px-4 lg:px-6 py-4 lg:py-6 pb-24 lg:pb-6">
          <RouterView />
        </div>
      </div>

      <AppFooter />
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <AdminBottomNav />
  </div>
</template>

<style>
.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
