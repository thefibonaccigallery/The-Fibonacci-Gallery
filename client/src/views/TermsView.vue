<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'

const authStore = useAuthStore()

const activeSection = ref('agreement')

const sections = [
  { id: 'agreement', label: '1. Agreement to Terms' },
  { id: 'accounts', label: '2. User Accounts' },
  { id: 'ip', label: '3. Intellectual Property' },
  { id: 'purchase', label: '4. Purchase & Payment' },
  { id: 'liability', label: '5. Limitation of Liability' },
  { id: 'governing', label: '6. Governing Law' },
]

const scrollToSection = (id: string) => {
  activeSection.value = id
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary">
      <div class="container mx-auto px-6 py-16 md:py-24 max-w-5xl">
        <!-- Hero Section -->
        <div class="text-center mb-16">
          <div class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider">
            Legal Center
          </div>
          <h1 class="text-4xl md:text-5xl font-normal mb-4 tracking-tight">
            Terms of Service
          </h1>
          <p class="text-sm text-muted-foreground">
            Last Updated: July 6, 2026
          </p>
        </div>

        <div class="grid md:grid-cols-12 gap-12 items-start">
          <!-- Sidebar Navigation -->
          <div class="md:col-span-4 sticky top-24 hidden md:block">
            <div class="border border-border/20 rounded-3xl p-6 liquid-glass space-y-2">
              <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4 px-2">Table of Contents</p>
              <button
                v-for="sec in sections"
                :key="sec.id"
                @click="scrollToSection(sec.id)"
                :class="[
                  'w-full text-left px-3 py-2 rounded-xl text-sm font-semibold transition-all duration-300',
                  activeSection === sec.id
                    ? 'bg-primary/10 text-primary border-l-2 border-primary pl-4'
                    : 'text-muted-foreground hover:text-foreground hover:bg-secondary/40',
                ]"
              >
                {{ sec.label }}
              </button>
            </div>
          </div>

          <!-- Document Content -->
          <div class="md:col-span-8 border border-border/20 rounded-3xl p-8 md:p-10 liquid-glass space-y-10 prose prose-invert max-w-none">
            
            <section id="agreement" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">1. Agreement to Terms</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                By accessing or using our platform, you agree to be bound by these Terms of Service. If you do not agree to all of these terms, do not access or use the platform.
              </p>
              <p class="text-sm text-muted-foreground leading-relaxed">
                We reserve the right, at our sole discretion, to modify or replace these Terms at any time. Your continued use of the platform following the posting of any changes constitutes acceptance of those changes.
              </p>
            </section>

            <section id="accounts" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">2. User Accounts</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                When you create an account with us, you guarantee that the information you provide is accurate, complete, and current at all times. Inaccurate or incomplete information may result in the immediate termination of your account.
              </p>
              <p class="text-sm text-muted-foreground leading-relaxed">
                You are responsible for maintaining the confidentiality of your account credentials. You agree to accept responsibility for all activities that occur under your account.
              </p>
            </section>

            <section id="ip" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">3. Intellectual Property</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                The platform and its original content, features, and functionality (including but not limited to all masterclass videos, indicators, study guides, and visual interfaces) are and will remain the exclusive property of The Fibonacci Gallery and its licensors.
              </p>
              <p class="text-sm text-muted-foreground leading-relaxed">
                You are granted a limited, non-exclusive, non-transferable license to access our educational content for personal learning purposes only. Sharing credentials, downloading course videos, or redistributing our materials is strictly prohibited.
              </p>
            </section>

            <section id="purchase" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">4. Purchase & Payment</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                If you wish to purchase any course or service made available through the platform, you may be asked to supply certain relevant transaction information.
              </p>
              <p class="text-sm text-muted-foreground leading-relaxed">
                We reserve the right to refuse or cancel your order if fraud or an unauthorized transaction is suspected. We also reserve the right to correct pricing errors even after a purchase has been initiated.
              </p>
            </section>

            <section id="liability" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">5. Limitation of Liability</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                In no event shall The Fibonacci Gallery, nor its directors, employees, or mentors, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of the platform.
              </p>
            </section>

            <section id="governing" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">6. Governing Law</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                These Terms shall be governed and construed in accordance with the laws of the jurisdiction in which our business is registered, without regard to its conflict of law provisions. Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
              </p>
            </section>

          </div>
        </div>
      </div>

      <!-- Public Footer (only shown if not authenticated) -->
      <footer v-if="!authStore.isAuthenticated" class="border-t border-border/20 py-12 px-6 bg-card mt-24">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
          <p class="text-xs text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} The Fibonacci Gallery. All rights reserved.
          </p>
          <div class="flex gap-6">
            <RouterLink to="/privacy" class="text-xs text-muted-foreground hover:text-foreground">Privacy Policy</RouterLink>
            <RouterLink to="/terms" class="text-xs text-muted-foreground hover:text-foreground">Terms of Service</RouterLink>
            <RouterLink to="/disclaimer" class="text-xs text-muted-foreground hover:text-foreground">Disclaimer</RouterLink>
          </div>
        </div>
      </footer>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
  backdrop-filter: blur(10px);
}
</style>
