<script setup lang="ts">
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import BottomNav from '@/components/dashboard/BottomNav.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { useDashboard } from '@/composables/useDashboard'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { RouterLink } from 'vue-router'
import logo from '@/assets/images/logo.png'
import { PhSignOut } from '@phosphor-icons/vue'

const { activeTab, setActiveTab } = useDashboard()
const authStore = useAuthStore()
</script>

<template>
  <div class="h-screen overflow-hidden bg-background flex flex-col md:flex-row font-primary">
    <!-- Desktop Sidebar -->
    <AppSidebar
      v-if="authStore.isAuthenticated"
      :activeTab="activeTab"
      @update:activeTab="setActiveTab"
    />

    <!-- Main Content Area -->
    <main class="flex-1 h-screen overflow-y-auto w-full relative flex flex-col justify-between">
      <div>
        <!-- Public header for unauthenticated users -->
        <AppHeader v-if="!authStore.isAuthenticated" />

        <!-- Mobile Header for authenticated users -->
        <header
          v-else
          class="md:hidden sticky top-0 z-40 w-full border-b border-border/20 bg-background/80 backdrop-blur-md px-4 py-3 flex items-center justify-between"
        >
          <div class="flex items-center gap-2.5">
            <div
              class="w-12 h-12 flex items-center justify-center shrink-0"
            >
              <img :src="logo" alt="Logo" class="w-12 h-12 object-contain" />
            </div>
            <div class="font-bold text-sm tracking-tight text-foreground">
              The Fibonacci Gallery
            </div>
          </div>

          <div class="flex items-center gap-2">
            <!-- <ThemeToggle /> -->
            <Button
              variant="ghost"
              size="icon"
              class="text-destructive hover:text-destructive hover:bg-destructive/10 rounded-xl"
              @click="authStore.logout()"
              title="Logout"
            >
              <PhSignOut class="h-5 w-5" />
            </Button>
          </div>
        </header>

        <div :class="[authStore.isAuthenticated ? 'pl-4' : '']">
          <slot />
        </div>
      </div>

      <AppFooter />
    </main>

    <!-- Mobile Bottom Navigation -->
    <BottomNav
      v-if="authStore.isAuthenticated"
      :activeTab="activeTab"
      @update:activeTab="setActiveTab"
    />
  </div>
</template>
