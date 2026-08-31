<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import type { DashboardTab } from '@/types/DashboardType'
import { PhHouse, PhGraduationCap, PhCompass, PhUser, PhHandCoins } from '@phosphor-icons/vue'

const props = defineProps<{
  activeTab: DashboardTab
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: DashboardTab): void
}>()

const router = useRouter()
const route = useRoute()

const navItems = [
  { id: 'home' as DashboardTab, label: 'Home', icon: PhHouse, route: '/dashboard' },
  { id: 'courses' as DashboardTab, label: 'Courses', icon: PhGraduationCap, route: '/my-courses' },
  { id: 'explore' as DashboardTab, label: 'Explore', icon: PhCompass, route: '/explore' },
  { id: 'affiliate' as DashboardTab, label: 'Affiliate', icon: PhHandCoins, route: '/affiliate' },
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
  <nav
    class="md:hidden fixed bottom-0 left-0 right-0 z-30 liquid-glass border-t border-border/20 px-6 py-2 flex justify-around items-center pb-safe"
  >
    <button
      v-for="item in navItems"
      :key="item.id"
      @click="handleNavClick(item)"
      class="flex flex-col items-center justify-center py-1 transition-all duration-300 relative"
      :class="[isActive(item) ? 'text-primary' : 'text-muted-foreground']"
    >
      <component
        :is="item.icon"
        class="h-5 w-5 mb-1"
        :weight="isActive(item) ? 'fill' : 'regular'"
      />
      <span class="text-[10px] font-medium tracking-tight">{{ item.label }}</span>
      <span
        v-if="isActive(item)"
        class="absolute -top-2 w-1.5 h-1.5 rounded-full bg-primary"
      />
    </button>
  </nav>
</template>

<style scoped>
.pb-safe {
  padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0px));
}
</style>
