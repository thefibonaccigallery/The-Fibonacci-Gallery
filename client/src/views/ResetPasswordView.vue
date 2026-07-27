<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import AnimatedHeading from '@/components/blocks/AnimatedHeading.vue'
import FadeIn from '@/components/blocks/FadeIn.vue'
import VideoBackground from '@/components/blocks/VideoBackground.vue'
import {
  PhArrowLeft,
  PhLockKey,
  PhEye,
  PhEyeSlash,
  PhCheckCircle,
  PhWarningCircle,
  PhSpinner,
} from '@phosphor-icons/vue'
import { usePasswordReset } from '@/composables/usePasswordReset'

const route = useRoute()
const token = computed(() => (route.query.token as string) || '')

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const tokenValid = ref<boolean | null>(null)

const {
  resetForm,
  resetPasswordErrors,
  resetCompleted,
  isSubmittingReset,
  isVerifyingToken,
  verifyToken,
  clearResetPasswordError,
  handleResetPasswordSubmit,
} = usePasswordReset()

onMounted(async () => {
  if (!token.value) {
    tokenValid.value = false
    return
  }

  try {
    const res = await verifyToken(token.value)
    tokenValid.value = res.valid
  } catch {
    tokenValid.value = false
  }
})

const bgVideoUrl =
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260325_094440_a3592600-bd1e-49e5-9bce-a73662061d83.mp4'
</script>

<template>
  <div class="min-h-screen bg-background text-foreground relative flex flex-col">
    <VideoBackground :src="bgVideoUrl" :overlay-opacity="0.6" />

    <!-- Top Navigation -->
    <nav class="relative z-10 w-full p-6 flex justify-between items-center">
      <Button variant="ghost" size="sm" as-child class="liquid-glass border-border/20">
        <RouterLink to="/login">
          <PhArrowLeft class="mr-2 h-4 w-4" />
          Back to Login
        </RouterLink>
      </Button>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 relative z-10">
      <div class="w-full max-w-md text-center mb-8">
        <AnimatedHeading
          text="Reset Password"
          tag="h1"
          :delay="200"
          :char-delay="35"
          class="text-4xl md:text-5xl font-normal text-foreground"
          :style="{ letterSpacing: '-0.04em' }"
        />
        <p class="text-sm text-muted-foreground mt-2">
          Create a new secure password for your account
        </p>
      </div>

      <FadeIn :delay="600" :duration="800" class="w-full max-w-md">
        <div class="liquid-glass rounded-2xl border border-border/20 p-8 shadow-2xl">
          <!-- Loading State for Token Verification -->
          <div v-if="isVerifyingToken || tokenValid === null" class="text-center py-8 space-y-4">
            <PhSpinner class="h-8 w-8 animate-spin text-primary mx-auto" />
            <p class="text-sm text-muted-foreground">Verifying your reset link...</p>
          </div>

          <!-- Invalid/Expired Token State -->
          <div v-else-if="!tokenValid" class="text-center space-y-6 py-4">
            <div
              class="w-16 h-16 rounded-full bg-destructive/20 text-destructive border border-destructive/30 flex items-center justify-center mx-auto"
            >
              <PhWarningCircle class="h-8 w-8" />
            </div>

            <div class="space-y-2">
              <h2 class="text-xl font-semibold text-foreground">Invalid or Expired Link</h2>
              <p class="text-sm text-muted-foreground leading-relaxed">
                This password reset link is invalid or has expired. Please request a new link to reset your password.
              </p>
            </div>

            <div class="pt-4 border-t border-border/20 flex flex-col gap-3">
              <Button as-child class="w-full">
                <RouterLink to="/forgot-password">Request New Reset Link</RouterLink>
              </Button>
              <Button as-child variant="ghost" class="w-full">
                <RouterLink to="/login">Back to Login</RouterLink>
              </Button>
            </div>
          </div>

          <!-- Success State -->
          <div v-else-if="resetCompleted" class="text-center space-y-6 py-4">
            <div
              class="w-16 h-16 rounded-full bg-primary/20 text-primary border border-primary/30 flex items-center justify-center mx-auto"
            >
              <PhCheckCircle class="h-8 w-8" />
            </div>

            <div class="space-y-2">
              <h2 class="text-xl font-semibold text-foreground">Password Reset Complete</h2>
              <p class="text-sm text-muted-foreground leading-relaxed">
                Your password has been successfully updated. You can now log in with your new password.
              </p>
            </div>

            <div class="pt-4 border-t border-border/20">
              <Button as-child class="w-full h-11 text-base font-medium">
                <RouterLink to="/login">Sign In Now</RouterLink>
              </Button>
            </div>
          </div>

          <!-- Reset Password Form State -->
          <form
            v-else
            @submit.prevent="handleResetPasswordSubmit(token)"
            class="space-y-6"
          >
            <div class="space-y-4">
              <!-- New Password Input -->
              <div class="space-y-2">
                <label for="reset-password" class="text-sm font-medium text-foreground">
                  New Password
                </label>
                <div class="relative">
                  <Input
                    id="reset-password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="resetForm.state.values.password"
                    placeholder="••••••••"
                    :class="{ 'border-destructive': resetPasswordErrors.password }"
                    class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary pr-10"
                    @input="clearResetPasswordError('password')"
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
                <p v-if="resetPasswordErrors.password" class="text-xs text-destructive mt-1">
                  {{ resetPasswordErrors.password }}
                </p>
              </div>

              <!-- Confirm New Password Input -->
              <div class="space-y-2">
                <label for="reset-password-confirm" class="text-sm font-medium text-foreground">
                  Confirm New Password
                </label>
                <div class="relative">
                  <Input
                    id="reset-password-confirm"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="resetForm.state.values.password_confirmation"
                    placeholder="••••••••"
                    :class="{ 'border-destructive': resetPasswordErrors.password_confirmation }"
                    class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary pr-10"
                    @input="clearResetPasswordError('password_confirmation')"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    aria-label="Toggle password visibility"
                  >
                    <PhEye v-if="!showConfirmPassword" class="h-4 w-4" />
                    <PhEyeSlash v-else class="h-4 w-4" />
                  </button>
                </div>
                <p
                  v-if="resetPasswordErrors.password_confirmation"
                  class="text-xs text-destructive mt-1"
                >
                  {{ resetPasswordErrors.password_confirmation }}
                </p>
              </div>
            </div>

            <Button
              type="submit"
              class="w-full h-11 text-base font-medium"
              :disabled="isSubmittingReset"
            >
              <span v-if="isSubmittingReset">Resetting Password...</span>
              <span v-else class="flex items-center justify-center gap-2">
                Reset Password
                <PhLockKey class="h-5 w-5" />
              </span>
            </Button>
          </form>
        </div>
      </FadeIn>
    </main>
  </div>
</template>
