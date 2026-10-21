<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { PhArrowRight, PhList, PhX } from '@phosphor-icons/vue'
import logo from '@/assets/images/logo.png'

// Mobile menu state
const mobileMenuOpen = ref(false)

// Scroll-based navbar background
const scrolled = ref(false)
onMounted(() => {
  window.addEventListener('scroll', () => {
    scrolled.value = window.scrollY > 50
  })
})

const navLinks = [
  { label: 'Courses', href: '/explore' },
  { label: 'Markets', href: '/markets' },
  { label: 'Testimonials', href: '/testimonials' },
  { label: 'FAQ', href: '/faq' },
  { label: 'Contact Us', href: '/contact' },
]
</script>

<template>
  <nav
    id="navbar"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
    :class="[scrolled ? 'bg-background border-b border-border/20 py-3' : 'bg-background py-5']"
  >
    <div class="max-w-7xl mx-auto px-6 md:px-12 lg:px-16 flex items-center justify-between">
      <!-- Brand with Logo -->
      <RouterLink to="/" class="flex items-center gap-3 group">
        <div class="w-16 h-16 flex items-center justify-center shrink-0">
          <img :src="logo" alt="The Fibonacci Gallery" class="w-16 h-16 object-contain" />
        </div>
        <span
          class="text-lg md:text-xl font-bold tracking-tight text-foreground group-hover:text-primary transition-colors"
        >
          The Fibonacci Gallery
        </span>
      </RouterLink>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center gap-8">
        <RouterLink
          v-for="link in navLinks"
          :key="link.label"
          :to="link.href"
          class="text-sm text-muted-foreground hover:text-foreground transition-colors"
        >
          {{ link.label }}
        </RouterLink>
      </div>

      <!-- Right Side -->
      <div class="flex items-center gap-3">
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
    <div v-if="mobileMenuOpen" class="md:hidden bg-background border-t border-border/20 mt-2">
      <div class="px-6 py-4 flex flex-col gap-3">
        <RouterLink
          v-for="link in navLinks"
          :key="link.label"
          :to="link.href"
          class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
          @click="mobileMenuOpen = false"
        >
          {{ link.label }}
        </RouterLink>
        <Button class="mt-2 w-full" size="sm" as-child>
          <RouterLink to="/register" @click="mobileMenuOpen = false">
            Get Started
            <PhArrowRight class="ml-1 h-4 w-4" />
          </RouterLink>
        </Button>
      </div>
    </div>
  </nav>
</template>
