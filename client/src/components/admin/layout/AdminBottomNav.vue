<script setup lang="ts">
import { useRoute } from 'vue-router'
import { PhHouse, PhBook, PhUsers, PhShoppingCart, PhGear } from '@phosphor-icons/vue'

const route = useRoute()

const navItems = [
  { name: 'Dashboard', path: '/admin/dashboard', icon: PhHouse },
  { name: 'Courses', path: '/admin/courses', icon: PhBook },
  { name: 'Students', path: '/admin/students', icon: PhUsers },
  { name: 'Orders', path: '/admin/orders', icon: PhShoppingCart },
  { name: 'Settings', path: '/admin/settings', icon: PhGear },
]

const isActive = (path: string) => {
  return route.path === path
}
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden liquid-glass border-t border-border/50">
    <div class="flex items-center justify-around px-2 py-3">
      <RouterLink
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        :class="[
          'flex flex-col items-center gap-1 px-3 py-2 rounded-lg transition-all duration-200 min-w-[64px]',
          isActive(item.path)
            ? 'text-primary'
            : 'text-foreground/60 hover:text-foreground hover:bg-accent/30',
        ]"
      >
        <component :is="item.icon" :size="22" :weight="isActive(item.path) ? 'fill' : 'regular'" />
        <span class="text-[10px] font-medium">{{ item.name }}</span>
      </RouterLink>
    </div>
  </nav>
</template>
