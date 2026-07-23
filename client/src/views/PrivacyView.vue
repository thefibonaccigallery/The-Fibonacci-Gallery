<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'

const authStore = useAuthStore()

const activeSection = ref('collect')

const sections = [
  { id: 'collect', label: '1. Information We Collect' },
  { id: 'use', label: '2. How We Use Information' },
  { id: 'security', label: '3. Data Security' },
  { id: 'cookies', label: '4. Cookies & Tracking' },
  { id: 'third-party', label: '5. Third-Party Services' },
  { id: 'rights', label: '6. Your Choices & Rights' },
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
            Privacy Policy
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
            
            <section id="collect" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">1. Information We Collect</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                We collect personal information that you voluntarily provide to us when you register on our platform, purchase courses, express interest in obtaining information about us, or contact us.
              </p>
              <ul class="list-disc pl-5 space-y-2 text-sm text-muted-foreground leading-relaxed">
                <li><strong>Personal Data:</strong> Name, email address, password, country, and contact details.</li>
                <li><strong>Payment Info:</strong> Transaction receipts, currency preferences, and payment provider references (all financial processing is handled securely through external gateways).</li>
                <li><strong>Usage Data:</strong> Lesson completion progress, live market session attendance log, and platform activity data.</li>
              </ul>
            </section>

            <section id="use" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">2. How We Use Information</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                We process your information for purposes based on legitimate business interests, the fulfillment of our contract with you, compliance with our legal obligations, and/or your consent.
              </p>
              <ul class="list-disc pl-5 space-y-2 text-sm text-muted-foreground leading-relaxed">
                <li>To facilitate account creation and logon processes.</li>
                <li>To deliver trading education and track course progress.</li>
                <li>To send administrative notifications, updates, and marketing communications.</li>
                <li>To protect our platform and prevent unauthorized account access.</li>
              </ul>
            </section>

            <section id="security" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">3. Data Security</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                We aim to protect your personal information through a system of organizational and technical security measures. We have implemented appropriate internal security measures to prevent your personal data from being accidentally lost, used, or accessed in an unauthorized way.
              </p>
              <p class="text-sm text-muted-foreground leading-relaxed">
                Please remember that we cannot guarantee that the internet itself is 100% secure. Although we do our best to protect your personal information, transmission of personal information to and from our website is at your own risk.
              </p>
            </section>

            <section id="cookies" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">4. Cookies & Tracking</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                We may use cookies and similar tracking technologies to access or store information. Most web browsers are set to accept cookies by default. If you prefer, you can usually choose to set your browser to remove cookies and to reject cookies.
              </p>
            </section>

            <section id="third-party" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">5. Third-Party Services</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                Our service may contain links to other websites or services (e.g. TradingView, Discord, payment gateways) that are not operated by us. We strongly advise you to review the Privacy Policy of every site you visit. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party services.
              </p>
            </section>

            <section id="rights" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">6. Your Choices & Rights</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                Depending on your location, you may have rights under applicable data protection laws (such as GDPR or CCPA). These may include the right to request access to and obtain a copy of your personal information, request rectification or erasure of your data, or withdraw consent.
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
