<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import AnimatedHeading from '@/components/blocks/AnimatedHeading.vue'
import FadeIn from '@/components/blocks/FadeIn.vue'
import VideoBackground from '@/components/blocks/VideoBackground.vue'
import { PhArrowLeft, PhPaperPlaneTilt, PhEnvelopeSimple, PhCheckCircle } from '@phosphor-icons/vue'
import { usePasswordReset } from '@/composables/usePasswordReset'

const {
  forgotForm,
  forgotPasswordErrors,
  forgotMailSent,
  isSubmittingForgot,
  clearForgotPasswordError,
  handleForgotPasswordSubmit,
} = usePasswordReset()

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
          text="Forgot Password"
          tag="h1"
          :delay="200"
          :char-delay="35"
          class="text-4xl md:text-5xl font-normal text-foreground"
          :style="{ letterSpacing: '-0.04em' }"
        />
        <p class="text-sm text-muted-foreground mt-2">
          Enter your email to receive a password reset link
        </p>
      </div>

      <FadeIn :delay="600" :duration="800" class="w-full max-w-md">
        <div class="liquid-glass rounded-2xl border border-border/20 p-8 shadow-2xl">
          <!-- State 1: Email Input Form -->
          <form
            v-if="!forgotMailSent"
            @submit.prevent="handleForgotPasswordSubmit"
            class="space-y-6"
          >
            <div class="space-y-4">
              <div class="space-y-2">
                <label for="forgot-email" class="text-sm font-medium text-foreground">
                  Email Address
                </label>
                <div class="relative">
                  <Input
                    id="forgot-email"
                    v-model="forgotForm.state.values.email"
                    placeholder="name@example.com"
                    :class="{ 'border-destructive': forgotPasswordErrors.email }"
                    class="bg-background/50 border-border/40 text-foreground placeholder:text-muted-foreground focus-visible:ring-primary pl-10"
                    @input="clearForgotPasswordError('email')"
                  />
                  <PhEnvelopeSimple
                    class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
                  />
                </div>
                <p v-if="forgotPasswordErrors.email" class="text-xs text-destructive mt-1">
                  {{ forgotPasswordErrors.email }}
                </p>
              </div>
            </div>

            <Button
              type="submit"
              class="w-full h-11 text-base font-medium"
              :disabled="isSubmittingForgot"
            >
              <span v-if="isSubmittingForgot">Sending Link...</span>
              <span v-else class="flex items-center justify-center gap-2">
                Send Reset Link
                <PhPaperPlaneTilt class="h-5 w-5" />
              </span>
            </Button>

            <div class="text-center text-sm text-muted-foreground pt-4 border-t border-border/20">
              Remembered your password?
              <RouterLink
                to="/login"
                class="text-foreground hover:text-primary transition-colors font-medium ml-1"
              >
                Sign in
              </RouterLink>
            </div>
          </form>

          <!-- State 2: Mail Sent Confirmation -->
          <div v-else class="text-center space-y-6 py-4">
            <div
              class="w-16 h-16 rounded-full bg-primary/20 text-primary border border-primary/30 flex items-center justify-center mx-auto"
            >
              <PhCheckCircle class="h-8 w-8" />
            </div>

            <div class="space-y-2">
              <h2 class="text-xl font-semibold text-foreground">Check Your Inbox</h2>
              <p class="text-sm text-muted-foreground leading-relaxed">
                If an account exists for
                <span class="font-medium text-foreground">{{ forgotForm.state.values.email }}</span>,
                we've sent a link to reset your password. The link will expire in 60 minutes.
              </p>
            </div>

            <div class="pt-4 border-t border-border/20 flex flex-col gap-3">
              <Button variant="outline" class="w-full" @click="forgotMailSent = false">
                Try Another Email
              </Button>
              <Button as-child variant="ghost" class="w-full">
                <RouterLink to="/login">Back to Login</RouterLink>
              </Button>
            </div>
          </div>
        </div>
      </FadeIn>
    </main>
  </div>
</template>
