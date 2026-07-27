<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useColorMode } from '@vueuse/core'
import { useAuthStore } from '@/stores/auth'
import {
  PhHouse,
  PhBook,
  PhBookOpen,
  PhVideo,
  PhUsers,
  PhUserList,
  PhShoppingCart,
  PhGear,
  PhMoon,
  PhSun,
  PhList,
  PhX,
  PhSignOut,
  PhTicket,
} from '@phosphor-icons/vue'

const route = useRoute()
const mode = useColorMode()
const authStore = useAuthStore()
const isCollapsed = ref(false)

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
    items: [{ name: 'Orders', path: '/admin/orders', icon: PhShoppingCart }],
  },
  {
    label: 'SETTINGS',
    items: [{ name: 'Settings', path: '/admin/settings', icon: PhGear }],
  },
]

const isActive = (path: string) => {
  return route.path === path
}

const toggleTheme = () => {
  mode.value = mode.value === 'dark' ? 'light' : 'dark'
}

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
}

const handleLogout = () => {
  authStore.logout()
}
</script>

<template>
  <aside
    :class="[
      'fixed left-0 top-0 h-screen z-40 transition-all duration-300',
      'hidden lg:flex flex-col',
      'liquid-glass',
      isCollapsed ? 'w-20' : 'w-64',
    ]"
  >
    <!-- Logo & Platform Name -->
    <div class="flex items-center gap-3 p-6 border-b border-border/50">
      <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center flex-shrink-0">
        <PhBook :size="20" weight="bold" class="text-primary-foreground" />
      </div>
      <transition name="fade">
        <span v-if="!isCollapsed" class="font-semibold text-lg text-foreground">
          Trading Academy
        </span>
      </transition>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-6">
      <div v-for="section in navSections" :key="section.label" class="space-y-1">
        <transition name="fade">
          <h3 v-if="!isCollapsed" class="px-3 text-xs font-semibold text-muted-foreground mb-2">
            {{ section.label }}
          </h3>
        </transition>
        <div v-if="isCollapsed" class="h-px bg-border/50 my-2"></div>

        <RouterLink
          v-for="item in section.items"
          :key="item.path"
          :to="item.path"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200',
            'hover:bg-accent/50',
            isActive(item.path)
              ? 'bg-primary/10 text-primary font-medium'
              : 'text-foreground/70 hover:text-foreground',
            isCollapsed ? 'justify-center' : '',
          ]"
          :title="isCollapsed ? item.name : ''"
        >
          <component :is="item.icon" :size="20" weight="regular" class="flex-shrink-0" />
          <transition name="fade">
            <span v-if="!isCollapsed" class="text-sm">{{ item.name }}</span>
          </transition>
        </RouterLink>
      </div>
    </nav>

    <!-- Bottom Section -->
    <div class="p-4 border-t border-border/50 space-y-3">
      <!-- Admin Info -->
      <transition name="fade">
        <div v-if="!isCollapsed" class="px-3 py-2">
          <p class="text-sm font-medium text-foreground">Admin User</p>
          <p class="text-xs text-muted-foreground">Administrator</p>
        </div>
      </transition>

      <!-- Collapse Toggle (Desktop) -->
      <button
        @click="toggleCollapse"
        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg hover:bg-accent/50 transition-colors text-foreground/70 hover:text-foreground"
        :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      >
        <PhList v-if="isCollapsed" :size="20" />
        <PhX v-else :size="20" />
      </button>

      <!-- Theme Toggle -->
      <!-- <button
        @click="toggleTheme"
        :class="[
          'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
          'hover:bg-accent/50 text-foreground/70 hover:text-foreground',
          isCollapsed ? 'justify-center' : '',
        ]"
        :title="isCollapsed ? 'Toggle theme' : ''"
      >
        <PhMoon v-if="mode === 'light'" :size="20" />
        <PhSun v-else :size="20" />
        <transition name="fade">
          <span v-if="!isCollapsed" class="text-sm">
            {{ mode === 'light' ? 'Dark Mode' : 'Light Mode' }}
          </span>
        </transition>
      </button> -->

      <!-- Logout Button -->
      <button
        @click="handleLogout"
        :class="[
          'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
          'hover:bg-destructive/10 text-destructive hover:text-destructive',
          isCollapsed ? 'justify-center' : '',
        ]"
        :title="isCollapsed ? 'Logout' : ''"
        :disabled="authStore.isLoading"
      >
        <PhSignOut :size="20" />
        <transition name="fade">
          <span v-if="!isCollapsed" class="text-sm">
            {{ authStore.isLoading ? 'Logging out...' : 'Logout' }}
          </span>
        </transition>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
