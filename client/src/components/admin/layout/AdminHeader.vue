<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useColorMode } from '@vueuse/core'
import { useAuthStore } from '@/stores/auth'
import { PhBell, PhMoon, PhSun, PhUser, PhSignOut, PhCaretRight, PhList } from '@phosphor-icons/vue'
import logo from '@/assets/images/logo.png'

const route = useRoute()
const router = useRouter()
const mode = useColorMode()
const authStore = useAuthStore()
const showDropdown = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

const emit = defineEmits<{
  toggleMobileSidebar: []
}>()

const pageTitle = computed(() => {
  const titleMap: Record<string, string> = {
    '/admin/dashboard': 'Dashboard',
    '/admin/courses': 'Courses',
    '/admin/chapters': 'Chapters',
    '/admin/videos': 'Videos',
    '/admin/students': 'Students',
    '/admin/enrollments': 'Enrollments',
    '/admin/orders': 'Orders',
    '/admin/settings': 'Settings',
    '/admin/profile': 'Admin Profile',
  }
  return titleMap[route.path] || 'Admin'
})

const breadcrumbs = computed(() => {
  const path = route.path
  const parts = path.split('/').filter(Boolean)
  return parts.map((part, index) => ({
    label: part.charAt(0).toUpperCase() + part.slice(1),
    path: '/' + parts.slice(0, index + 1).join('/'),
    isLast: index === parts.length - 1,
  }))
})

const toggleTheme = () => {
  mode.value = mode.value === 'dark' ? 'light' : 'dark'
}

const handleLogout = () => {
  showDropdown.value = false
  authStore.logout()
}

const handleProfile = () => {
  router.push('/admin/settings')
  showDropdown.value = false
}

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <header class="sticky top-0 z-30 liquid-glass border-b border-border/50">
    <div class="flex items-center justify-between px-4 lg:px-6 py-4">
      <!-- Left: Mobile Menu + Logo + Title & Breadcrumb -->
      <div class="flex items-center gap-3">
        <!-- Mobile Menu Toggle -->
        <button
          @click="emit('toggleMobileSidebar')"
          class="lg:hidden p-2 rounded-lg hover:bg-accent/50 transition-colors text-foreground"
        >
          <PhList :size="24" />
        </button>

        <!-- Logo (mobile only) -->
        <RouterLink to="/admin/dashboard" class="lg:hidden flex items-center gap-2">
          <div class="w-10 h-10 flex items-center justify-center shrink-0">
            <img :src="logo" alt="Logo" class="w-10 h-10 object-contain" />
          </div>
        </RouterLink>

        <div>
          <h1 class="text-xl lg:text-2xl font-bold text-foreground">{{ pageTitle }}</h1>
          <nav class="flex items-center gap-1 mt-1 text-xs lg:text-sm text-muted-foreground">
            <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
              <RouterLink
                v-if="!crumb.isLast"
                :to="crumb.path"
                class="hover:text-primary transition-colors"
              >
                {{ crumb.label }}
              </RouterLink>
              <span v-else class="text-foreground font-medium">{{ crumb.label }}</span>
              <PhCaretRight v-if="!crumb.isLast" :size="12" class="mx-1" />
            </template>
          </nav>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="flex items-center gap-2 lg:gap-3">
        <!-- Notification Bell -->
        <button
          class="relative p-2 rounded-lg hover:bg-accent/50 transition-colors text-foreground/70 hover:text-foreground"
          title="Notifications"
        >
          <PhBell :size="20" />
          <span
            class="absolute top-1.5 right-1.5 w-2 h-2 bg-primary rounded-full border-2 border-background"
          ></span>
        </button>

        <!-- Theme Toggle (Mobile Only)
        <button
          @click="toggleTheme"
          class="lg:hidden p-2 rounded-lg hover:bg-accent/50 transition-colors text-foreground/70 hover:text-foreground"
          title="Toggle theme"
        >
          <PhMoon v-if="mode === 'light'" :size="20" />
          <PhSun v-else :size="20" />
        </button> -->

        <!-- Admin Avatar with Dropdown -->
        <div class="relative" ref="dropdownRef">
          <button
            @click="showDropdown = !showDropdown"
            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-accent/50 transition-colors"
          >
            <div
              class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary"
            >
              <PhUser :size="18" weight="bold" />
            </div>
            <span class="hidden lg:block text-sm font-medium text-foreground">
              {{ authStore.user?.name || 'Admin' }}
            </span>
          </button>

          <!-- Dropdown Menu -->
          <Transition name="dropdown">
            <div
              v-if="showDropdown"
              class="absolute right-0 mt-2 w-48 liquid-glass rounded-lg shadow-lg border border-border/50 py-1 animate-fade-in"
            >
              <button
                @click="handleProfile"
                class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-accent/50 transition-colors text-foreground text-sm"
              >
                <PhUser :size="18" />
                <span>Profile</span>
              </button>
              <div class="h-px bg-border/50 my-1"></div>
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-destructive/10 transition-colors text-destructive text-sm"
              >
                <PhSignOut :size="18" />
                <span>Logout</span>
              </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
