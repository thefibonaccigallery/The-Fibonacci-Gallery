<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import AnimatedHeading from '@/components/blocks/AnimatedHeading.vue'
import FadeIn from '@/components/blocks/FadeIn.vue'
import VideoBackground from '@/components/blocks/VideoBackground.vue'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import { PhArrowLeft, PhUserPlus, PhEye, PhEyeSlash, PhUserCheck } from '@phosphor-icons/vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const route = useRoute()
const referralCode = computed(() => (route.query.ref as string) || (route.query.referral as string) || '')

const loginLink = computed(() => {
  const redirect = route.query.redirect as string | undefined
  return redirect ? { path: '/login', query: { redirect } } : '/login'
})

onMounted(() => {
  if (referralCode.value) {
    authStore.registerForm.state.values.referral_code = referralCode.value.trim()
  }
})

const bgVideoUrl =
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260314_131748_f2ca2a28-fed7-44c8-b9a9-bd9acdd5ec31.mp4'
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
      <!-- <ThemeToggle /> -->
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 relative z-10">
      <div class="w-full max-w-md text-center mb-8">
        <AnimatedHeading
          text="Join The Gallery"
          tag="h1"
          :delay="200"
          :char-delay="35"
          class="text-4xl md:text-5xl font-normal text-foreground"
          :style="{ letterSpacing: '-0.04em' }"
        />
      </div>

      <FadeIn :delay="800" :duration="800" class="w-full max-w-md">
        <div class="liquid-glass rounded-2xl border border-border/20 p-8 shadow-2xl">
          <form @submit.prevent="authStore.handleSubmit()" class="space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <label for="register-name" class="text-sm font-medium text-foreground"
                  >Full Name</label
                >
                <Input
                  id="register-name"
                  type="text"
                  v-model="authStore.registerForm.state.values.name"
                  placeholder="John Doe"
                  :class="{ 'border-destructive': authStore.errors.name }"
                  class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                  @input="authStore.clearError('name')"
                />
                <p v-if="authStore.errors.name" class="text-xs text-destructive mt-1">
                  {{ authStore.errors.name }}
                </p>
              </div>

              <div class="space-y-2">
                <label for="register-email" class="text-sm font-medium text-foreground"
                  >Email Address</label
                >
                <Input
                  id="register-email"
                  v-model="authStore.registerForm.state.values.email"
                  placeholder="name@example.com"
                  :class="{ 'border-destructive': authStore.errors.email }"
                  class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                  @input="authStore.clearError('email')"
                />
                <p v-if="authStore.errors.email" class="text-xs text-destructive mt-1">
                  {{ authStore.errors.email }}
                </p>
              </div>

              <div class="space-y-2">
                <label for="register-country" class="text-sm font-medium text-foreground"
                  >Country</label
                >
                <div class="relative">
                  <select
                    id="register-country"
                    v-model="authStore.registerForm.state.values.country"
                    :class="[
                      'flex h-10 w-full rounded-md border bg-background/50 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                      authStore.errors.country
                        ? 'border-destructive focus-visible:ring-destructive'
                        : 'border-border/40 focus-visible:ring-primary',
                    ]"
                    @change="authStore.clearError('country')"
                  >
                    <option value="" disabled selected>Select your country</option>
                    <option v-for="country in authStore.countries" :key="country" :value="country">
                      {{ country }}
                    </option>
                  </select>
                </div>
                <p v-if="authStore.errors.country" class="text-xs text-destructive mt-1">
                  {{ authStore.errors.country }}
                </p>
              </div>

              <div class="space-y-2">
                <label for="register-password" class="text-sm font-medium text-foreground"
                  >Password</label
                >
                <div class="relative">
                  <Input
                    id="register-password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="authStore.registerForm.state.values.password"
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
                <p v-if="authStore.errors.password" class="text-xs text-destructive mt-1">
                  {{ authStore.errors.password }}
                </p>
              </div>

              <div class="space-y-2">
                <label for="register-confirm-password" class="text-sm font-medium text-foreground"
                  >Confirm Password</label
                >
                <div class="relative">
                  <Input
                    id="register-confirm-password"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="authStore.registerForm.state.values.password_confirmation"
                    placeholder="••••••••"
                    :class="{ 'border-destructive': authStore.errors.password_confirmation }"
                    class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary pr-10"
                    @input="authStore.clearError('password_confirmation')"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    aria-label="Toggle confirm password visibility"
                  >
                    <PhEye v-if="!showConfirmPassword" class="h-4 w-4" />
                    <PhEyeSlash v-else class="h-4 w-4" />
                  </button>
                </div>
                <p
                  v-if="authStore.errors.password_confirmation"
                  class="text-xs text-destructive mt-1"
                >
                  {{ authStore.errors.password_confirmation }}
                </p>
              </div>
            </div>

            <Button
              type="submit"
              class="w-full h-11 text-base font-medium"
              :disabled="authStore.isLoading"
            >
              <span v-if="authStore.isLoading">Creating Account...</span>
              <span v-else>
                Create Account
                <PhUserPlus class="ml-2 h-5 w-5 inline" />
              </span>
            </Button>

            <div class="text-center text-sm text-muted-foreground pt-4 border-t border-border/20">
              Already have an account?
              <RouterLink
                :to="loginLink"
                class="text-foreground hover:text-primary transition-colors font-medium ml-1"
              >
                Sign in
              </RouterLink>
            </div>
          </form>
        </div>
      </FadeIn>
    </main>
  </div>
</template>
