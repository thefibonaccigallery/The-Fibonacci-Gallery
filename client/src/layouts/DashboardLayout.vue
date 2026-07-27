<script setup lang="ts">
import AppSidebar from '@/components/dashboard/AppSidebar.vue'
import BottomNav from '@/components/dashboard/BottomNav.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { useDashboard } from '@/composables/useDashboard'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import { RouterLink } from 'vue-router'
import { ref } from 'vue'
import logo from '@/assets/images/white_final.webp'
import { PhArrowRight, PhList, PhX, PhSignOut } from '@phosphor-icons/vue'

const { activeTab, setActiveTab } = useDashboard()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)
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
        <nav
          v-if="!authStore.isAuthenticated"
          class="sticky top-0 z-50 w-full border-b border-border/20 bg-background py-3"
        >
          <div class="max-w-7xl mx-auto px-6 md:px-12 lg:px-16 flex items-center justify-between">
            <!-- Brand -->
            <RouterLink
              to="/"
              class="text-xl font-semibold tracking-tight text-foreground hover:text-primary transition-colors"
            >
              The Fibonacci Gallery
            </RouterLink>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-8">
              <RouterLink
                to="/"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Home</RouterLink
              >
              <a
                href="/#courses"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Courses</a
              >
              <a
                href="/#markets"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Markets</a
              >
              <a
                href="/#testimonials"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Testimonials</a
              >
              <a
                href="/#faq"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >FAQ</a
              >
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-3">
              <!-- <ThemeToggle /> -->
              <Button class="hidden md:inline-flex" size="sm" as-child>
                <RouterLink to="/login">
                  Get Started
                  <PhArrowRight class="ml-1 h-4 w-4" />
                </RouterLink>
              </Button>
              <!-- Mobile Menu Toggle -->
              <Button
                variant="ghost"
                size="icon"
                class="md:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen"
                aria-label="Toggle menu"
              >
                <PhList v-if="!mobileMenuOpen" class="h-5 w-5" />
                <PhX v-else class="h-5 w-5" />
              </Button>
            </div>
          </div>

          <!-- Mobile Menu -->
          <div v-if="mobileMenuOpen" class="md:hidden border-t border-border/20 mt-2 bg-background">
            <div class="px-6 py-4 flex flex-col gap-3">
              <RouterLink
                to="/"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                @click="mobileMenuOpen = false"
                >Home</RouterLink
              >
              <a
                href="/#courses"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                @click="mobileMenuOpen = false"
                >Courses</a
              >
              <a
                href="/#markets"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                @click="mobileMenuOpen = false"
                >Markets</a
              >
              <a
                href="/#testimonials"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                @click="mobileMenuOpen = false"
                >Testimonials</a
              >
              <a
                href="/#faq"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                @click="mobileMenuOpen = false"
                >FAQ</a
              >
              <Button class="mt-2 w-full" size="sm" as-child>
                <RouterLink to="/login" @click="mobileMenuOpen = false">
                  Get Started
                  <PhArrowRight class="ml-1 h-4 w-4" />
                </RouterLink>
              </Button>
            </div>
          </div>
        </nav>

        <!-- Mobile Header for authenticated users -->
        <header
          v-else
          class="md:hidden sticky top-0 z-40 w-full border-b border-border/20 bg-background/80 backdrop-blur-md px-4 py-3 flex items-center justify-between"
        >
          <div class="flex items-center gap-2.5">
            <div
              class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/30 shrink-0"
            >
              <img :src="logo" alt="Logo" class="w-5 h-5 rounded-lg" />
            </div>
            <div class="font-semibold text-xs tracking-tight text-foreground">
              <span class="block leading-tight">The Fibonacci</span>
              <span class="block text-[10px] text-muted-foreground leading-tight">Gallery</span>
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
