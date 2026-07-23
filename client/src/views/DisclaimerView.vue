<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { PhWarningCircle } from '@phosphor-icons/vue'

const authStore = useAuthStore()

const activeSection = ref('financial')

const sections = [
  { id: 'financial', label: '1. Financial Risk Warning' },
  { id: 'educational', label: '2. Educational Purpose Only' },
  { id: 'no-advice', label: '3. No Investment Advice' },
  { id: 'accuracy', label: '4. Accuracy of Information' },
  { id: 'performance', label: '5. Individual Performance' },
  { id: 'simulated', label: '6. Simulated Results' },
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
          <div class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/20 rounded-full px-4 py-1.5 text-xs font-semibold text-red-500 mb-6 uppercase tracking-wider">
            Risk Disclosure
          </div>
          <h1 class="text-4xl md:text-5xl font-normal mb-4 tracking-tight">
            Financial Disclaimer
          </h1>
          <p class="text-sm text-muted-foreground">
            Last Updated: July 6, 2026
          </p>
        </div>

        <!-- Big Alert Box -->
        <div class="border border-red-500/30 rounded-3xl p-6 md:p-8 mb-12 bg-red-500/5 flex items-start gap-4">
          <PhWarningCircle class="h-6 w-6 text-red-500 shrink-0 mt-0.5" />
          <div>
            <h3 class="text-lg font-semibold text-red-500 mb-1">High-Risk Warning</h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              Trading financial instruments (including but not limited to Forex, Cryptocurrencies, Stocks, and Commodities) involves high risk and may not be suitable for all investors. You could lose some or all of your deposited capital.
            </p>
          </div>
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
            
            <section id="financial" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">1. Financial Risk Warning</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                Leverage creates additional risk and loss exposure. Before deciding to trade foreign exchange or any other financial instrument, you should carefully consider your investment objectives, level of experience, and risk appetite.
              </p>
            </section>

            <section id="educational" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">2. Educational Purpose Only</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                All content, videos, charts, indicators, and tools provided on The Fibonacci Gallery are for educational and informational purposes only. No content should be construed as professional financial advice or recommendation to buy or sell any security.
              </p>
            </section>

            <section id="no-advice" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">3. No Investment Advice</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                Our mentors, staff, and platform do not provide personalized financial, legal, tax, or investment advice. You are solely responsible for conducting your own research and making your own trading decisions.
              </p>
            </section>

            <section id="accuracy" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">4. Accuracy of Information</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                While we strive to provide accurate, up-to-date chart analysis and market setups, we do not warrant the accuracy, completeness, or reliability of any information, charts, or feeds on this website. Any reliance you place on such information is strictly at your own risk.
              </p>
            </section>

            <section id="performance" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">5. Individual Performance</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                Past performance of any trading methodology, setup, or indicator is not indicative of future results. Trading results vary significantly between individuals based on emotional discipline, risk tolerance, execution speed, and market conditions.
              </p>
            </section>

            <section id="simulated" class="space-y-4">
              <h2 class="text-xl md:text-2xl font-semibold border-b border-border/10 pb-2">6. Simulated Results</h2>
              <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
                Any historical backtest metrics or simulated performance statistics shown on the platform have certain limitations. Unlike actual performance records, simulated results do not represent actual trading and may not account for real-world factors like market liquidity, slippage, and broker commissions.
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
