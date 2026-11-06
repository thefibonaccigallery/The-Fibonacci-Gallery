<script setup lang="ts">
import { ref } from 'vue'
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import BottomNav from '@/components/dashboard/BottomNav.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { useDashboard } from '@/composables/useDashboard'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import logo from '@/assets/images/logo.png'
import {
  PhSignOut,
  PhList,
  PhX,
  PhHouse,
  PhGraduationCap,
  PhCertificate,
  PhCompass,
  PhHandCoins,
  PhUser,
} from '@phosphor-icons/vue'
import type { DashboardTab } from '@/types/DashboardType'

const { activeTab, setActiveTab } = useDashboard()
const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const showMobileDrawer = ref(false)

const drawerNavItems = [
  { id: 'home' as DashboardTab, label: 'Dashboard', icon: PhHouse, route: '/dashboard' },
  {
    id: 'courses' as DashboardTab,
    label: 'My Courses',
    icon: PhGraduationCap,
    route: '/my-courses',
  },
  {
    id: 'certificates' as DashboardTab,
    label: 'Certificates',
    icon: PhCertificate,
    route: '/certificates',
  },
  { id: 'explore' as DashboardTab, label: 'Explore Masterclasses', icon: PhCompass, route: '/explore' },
  { id: 'affiliate' as DashboardTab, label: 'Affiliate Program', icon: PhHandCoins, route: '/affiliate' },
  { id: 'profile' as DashboardTab, label: 'Profile Settings', icon: PhUser, route: '/profile' },
]

const handleDrawerNavClick = (item: (typeof drawerNavItems)[0]) => {
  showMobileDrawer.value = false
  if (item.route) {
    router.push(item.route)
  } else {
    setActiveTab(item.id)
  }
}

const isItemActive = (item: (typeof drawerNavItems)[0]) => {
  if (item.route) {
    return route.path === item.route
  }
  return activeTab.value === item.id
}
</script>

<template>
  <div class="h-screen overflow-hidden bg-background flex flex-col md:flex-row font-primary">
    <!-- Desktop Sidebar -->
    <AppSidebar
      v-if="authStore.isAuthenticated"
      :activeTab="activeTab"
      @update:activeTab="setActiveTab"
    />

    <!-- Mobile Drawer Overlay -->
    <Transition name="overlay">
      <div
        v-if="authStore.isAuthenticated && showMobileDrawer"
        @click="showMobileDrawer = false"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 md:hidden"
      ></div>
    </Transition>

    <!-- Mobile Drawer Sidebar -->
    <Transition name="slide">
      <aside
        v-if="authStore.isAuthenticated && showMobileDrawer"
        class="fixed left-0 top-0 h-screen w-72 z-50 md:hidden bg-background/95 backdrop-blur-xl border-r border-border/30 flex flex-col shadow-2xl"
      >
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-5 border-b border-border/20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center shrink-0">
              <img :src="logo" alt="Logo" class="w-10 h-10 object-contain" />
            </div>
            <div class="flex flex-col">
              <span class="font-bold text-sm tracking-tight text-foreground">
                The Fibonacci Gallery
              </span>
              <span class="text-[11px] text-muted-foreground font-mono">
                Trading Academy
              </span>
            </div>
          </div>

          <Button
            variant="ghost"
            size="icon"
            class="h-8 w-8 rounded-lg text-muted-foreground hover:text-foreground"
            @click="showMobileDrawer = false"
            aria-label="Close menu"
          >
            <PhX class="h-5 w-5" />
          </Button>
        </div>

        <!-- Drawer Navigation Links -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1.5">
          <button
            v-for="item in drawerNavItems"
            :key="item.id"
            @click="handleDrawerNavClick(item)"
            class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium transition-all duration-200 cursor-pointer text-left"
            :class="[
              isItemActive(item)
                ? 'bg-primary/10 text-primary border border-primary/20 shadow-xs'
                : 'text-muted-foreground hover:text-foreground hover:bg-secondary/50 border border-transparent',
            ]"
          >
            <component
              :is="item.icon"
              class="h-5 w-5 shrink-0"
              :weight="isItemActive(item) ? 'fill' : 'regular'"
            />
            <span>{{ item.label }}</span>
          </button>
        </nav>

        <!-- Drawer User Card & Logout -->
        <div class="p-4 border-t border-border/20 bg-card/30 space-y-3">
          <RouterLink
            to="/profile"
            @click="showMobileDrawer = false"
            class="block px-3 py-2 rounded-xl hover:bg-secondary/40 transition-colors"
          >
            <p class="text-xs font-semibold text-foreground truncate">
              {{ authStore.user?.name || 'Trader' }}
            </p>
            <p class="text-[11px] text-muted-foreground truncate">
              {{ authStore.user?.email }}
            </p>
          </RouterLink>

          <Button
            variant="ghost"
            size="sm"
            class="w-full justify-start text-destructive hover:text-destructive hover:bg-destructive/10 rounded-xl font-medium"
            @click="() => { showMobileDrawer = false; authStore.logout(); }"
            :disabled="authStore.isLoading"
          >
            <PhSignOut class="mr-2 h-4 w-4" />
            Log Out
          </Button>
        </div>
      </aside>
    </Transition>

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
            <!-- Mobile Menu Drawer Hamburger Button -->
            <Button
              variant="ghost"
              size="icon"
              class="h-9 w-9 rounded-xl text-foreground hover:bg-secondary/60 shrink-0"
              @click="showMobileDrawer = true"
              aria-label="Open sidebar menu"
            >
              <PhList class="h-5 w-5" weight="bold" />
            </Button>

            <div
              class="w-10 h-10 flex items-center justify-center shrink-0 cursor-pointer"
              @click="router.push('/dashboard')"
            >
              <img :src="logo" alt="Logo" class="w-10 h-10 object-contain" />
            </div>
            <div
              class="font-bold text-sm tracking-tight text-foreground cursor-pointer truncate"
              @click="router.push('/dashboard')"
            >
              The Fibonacci Gallery
            </div>
          </div>

          <div class="flex items-center gap-1.5">
            <Button
              variant="ghost"
              size="icon"
              class="text-destructive hover:text-destructive hover:bg-destructive/10 rounded-xl"
              @click="authStore.logout()"
              title="Logout"
              aria-label="Logout"
            >
              <PhSignOut class="h-5 w-5" />
            </Button>
          </div>
        </header>

        <div :class="[authStore.isAuthenticated ? 'px-3 sm:px-6' : '']">
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

<style scoped>
.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.25s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.25s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
