<script setup lang="ts">
import type { DashboardTab } from '@/types/DashboardType'
import { PhHouse, PhGraduationCap, PhCompass, PhUser } from '@phosphor-icons/vue'

defineProps<{
  activeTab: DashboardTab
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: DashboardTab): void
}>()

const navItems = [
  { id: 'home' as DashboardTab, label: 'Home', icon: PhHouse },
  { id: 'courses' as DashboardTab, label: 'Courses', icon: PhGraduationCap },
  { id: 'explore' as DashboardTab, label: 'Explore', icon: PhCompass },
  { id: 'profile' as DashboardTab, label: 'Profile', icon: PhUser },
]
</script>

<template>
  <nav
    class="md:hidden fixed bottom-0 left-0 right-0 z-30 liquid-glass border-t border-border/20 px-6 py-2 flex justify-around items-center pb-safe"
  >
    <button
      v-for="item in navItems"
      :key="item.id"
      @click="emit('update:activeTab', item.id)"
      class="flex flex-col items-center justify-center py-1 transition-all duration-300 relative"
      :class="[activeTab === item.id ? 'text-primary' : 'text-muted-foreground']"
    >
      <component
        :is="item.icon"
        class="h-5 w-5 mb-1"
        :weight="activeTab === item.id ? 'fill' : 'regular'"
      />
      <span class="text-[10px] font-medium tracking-tight">{{ item.label }}</span>
      <span
        v-if="activeTab === item.id"
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
