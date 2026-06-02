<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import type { DashboardTab } from '@/types/DashboardType'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import { Button } from '@/components/ui/button'
import { useAuthStore } from '@/stores/auth'
import { PhHouse, PhGraduationCap, PhCompass, PhUser, PhSignOut } from '@phosphor-icons/vue'

const props = defineProps<{
  activeTab: DashboardTab
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: DashboardTab): void
}>()

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const navItems = [
  { id: 'home' as DashboardTab, label: 'Dashboard', icon: PhHouse, route: '/dashboard' },
  { id: 'courses' as DashboardTab, label: 'My Courses', icon: PhGraduationCap, route: '/my-courses' },
  { id: 'explore' as DashboardTab, label: 'Explore', icon: PhCompass, route: '/explore' },
  { id: 'profile' as DashboardTab, label: 'Profile', icon: PhUser, route: '/profile' },
]

const handleNavClick = (item: typeof navItems[0]) => {
  if (item.route) {
    router.push(item.route)
  } else {
    emit('update:activeTab', item.id)
  }
}

const isActive = (item: typeof navItems[0]) => {
  if (item.route) {
    return route.path === item.route
  }
  return props.activeTab === item.id
}
</script>

<template>
  <aside
    class="hidden md:flex flex-col w-64 h-full shrink-0 z-30 liquid-glass border-r border-border/20 p-6"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 mb-10">
      <div
        class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/30"
      >
        <span class="text-primary font-bold text-lg">Φ</span>
      </div>
      <div>
        <h1 class="font-semibold text-sm tracking-tight text-foreground">The Fibonacci</h1>
        <p class="text-xs text-muted-foreground">Gallery</p>
      </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 space-y-2">
      <button
        v-for="item in navItems"
        :key="item.id"
        @click="handleNavClick(item)"
        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300"
        :class="[
          isActive(item)
            ? 'bg-primary/10 text-primary border border-primary/20'
            : 'text-muted-foreground hover:text-foreground hover:bg-secondary/50 border border-transparent',
        ]"
      >
        <component
          :is="item.icon"
          class="h-5 w-5"
          :weight="isActive(item) ? 'fill' : 'regular'"
        />
        {{ item.label }}
      </button>
    </nav>

    <!-- Footer Settings & Toggle -->
    <div class="pt-6 border-t border-border/10 space-y-4">
      <div class="flex items-center justify-between">
        <span class="text-xs text-muted-foreground font-medium">Appearance</span>
        <ThemeToggle />
      </div>

      <Button
        variant="ghost"
        size="sm"
        class="w-full justify-start text-destructive hover:text-destructive hover:bg-destructive/10 rounded-xl"
        @click="authStore.logout()"
        :disabled="authStore.isLoading"
      >
        <PhSignOut class="mr-2 h-4 w-4" />
        Log Out
      </Button>
    </div>
  </aside>
</template>
