<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import AnimatedHeading from '@/components/blocks/AnimatedHeading.vue'
import FadeIn from '@/components/blocks/FadeIn.vue'
import VideoBackground from '@/components/blocks/VideoBackground.vue'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import { PhArrowLeft, PhSignIn, PhEye, PhEyeSlash } from '@phosphor-icons/vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const showPassword = ref(false)

const bgVideoUrl =
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260423_084718_72a17915-4964-4059-afcd-22d59399b72e.mp4'
</script>

<template>
  <div class="min-h-screen bg-background text-foreground relative flex flex-col">
    <VideoBackground :src="bgVideoUrl" :overlay-opacity="0.6" />

    <!-- Top Navigation -->
    <nav class="relative z-10 w-full p-6 flex justify-between items-center">
      <Button variant="ghost" size="sm" as-child class="liquid-glass border-border/20">
        <RouterLink to="/">
          <PhArrowLeft class="mr-2 h-4 w-4" />
          Back to Home
        </RouterLink>
      </Button>
      <ThemeToggle />
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 relative z-10">
      <div class="w-full max-w-md text-center mb-8">
        <AnimatedHeading
          text="Welcome Back"
          tag="h1"
          :delay="200"
          :char-delay="35"
          class="text-4xl md:text-5xl font-normal text-foreground"
          :style="{ letterSpacing: '-0.04em' }"
        />
      </div>

      <FadeIn :delay="800" :duration="800" class="w-full max-w-md">
        <div class="liquid-glass rounded-2xl border border-border/20 p-8 shadow-2xl">
          <form @submit.prevent="authStore.handleLogin()" class="space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <label for="login-email" class="text-sm font-medium text-foreground">Email Address</label>
                <Input
                  id="login-email"
                  type="email"
                  v-model="authStore.loginForm.state.values.email"
                  placeholder="name@example.com"
                  :class="{ 'border-destructive': authStore.errors.email }"
                  class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                  @input="authStore.clearError('email')"
                />
                <p v-if="authStore.errors.email" class="text-xs text-destructive mt-1">{{ authStore.errors.email }}</p>
              </div>
              
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label for="login-password" class="text-sm font-medium text-foreground">Password</label>
                  <a href="#" class="text-xs text-primary hover:text-primary/80 transition-colors">
                    Forgot password?
                  </a>
                </div>
                <div class="relative">
                  <Input
                    id="login-password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="authStore.loginForm.state.values.password"
                    placeholder="••••••••"
                    :class="{ 'border-destructive': authStore.errors.password }"
                    class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary pr-10"
                    @input="authStore.clearError('password')"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    aria-label="Toggle password visibility"
                  >
                    <PhEye v-if="!showPassword" class="h-4 w-4" />
                    <PhEyeSlash v-else class="h-4 w-4" />
                  </button>
                </div>
                <p v-if="authStore.errors.password" class="text-xs text-destructive mt-1">{{ authStore.errors.password }}</p>
              </div>
            </div>

            <Button type="submit" class="w-full h-11 text-base font-medium" :disabled="authStore.isLoading">
              <span v-if="authStore.isLoading">Signing In...</span>
              <span v-else>
                Sign In
                <PhSignIn class="ml-2 h-5 w-5 inline" />
              </span>
            </Button>

            <div class="text-center text-sm text-muted-foreground pt-4 border-t border-border/20">
              Don't have an account?
              <RouterLink to="/register" class="text-foreground hover:text-primary transition-colors font-medium ml-1">
                Create one now
              </RouterLink>
            </div>
          </form>
        </div>
      </FadeIn>
    </main>
  </div>
</template>
