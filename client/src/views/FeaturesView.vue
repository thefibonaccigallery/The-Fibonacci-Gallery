<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import {
  PhChartLineUp,
  PhShieldCheck,
  PhVideoCamera,
  PhUsers,
  PhArrowRight,
  PhCheckCircle,
} from '@phosphor-icons/vue'

const authStore = useAuthStore()

const detailedFeatures = [
  {
    icon: PhChartLineUp,
    title: 'Technical Analysis Mastery',
    description: 'Learn to read charts like a professional. Master Fibonacci retracements, extensions, Elliott Wave theory, and advanced geometric pattern recognition.',
    points: [
      'Fibonacci Retracement and Extension ratios (0.236, 0.382, 0.5, 0.618, 0.786, etc.)',
      'Elliott Wave theory integration for structure mapping',
      'Confluence trading strategies with multi-timeframe analysis',
      'Live chart pattern recognition and drawing precision',
    ],
  },
  {
    icon: PhShieldCheck,
    title: 'Advanced Risk Management',
    description: 'Protect your capital with battle-tested risk management strategies. Position sizing, stop-loss placement, and portfolio diversification.',
    points: [
      'Strict 1-2% risk model placement algorithms',
      'Average True Range (ATR) based stop-loss calculations',
      'Risk-to-reward ratio optimization (minimum 1:2)',
      'Drawdown containment protocols and emotional trading filters',
    ],
  },
  {
    icon: PhVideoCamera,
    title: 'Live Market Sessions',
    description: 'Trade alongside expert traders in real-time. Watch live analysis, get instant feedback, and learn from every market move.',
    points: [
      'Interactive London & New York session live streams',
      'Q&A sessions with veteran prop firm traders',
      'Real-time trade walkthroughs and setup reviews',
      'Recording archive with timestamps for self-paced review',
    ],
  },
  {
    icon: PhUsers,
    title: 'Exclusive Trading Community',
    description: 'Connect with a network of over 2,500 active traders sharing insights, setups, and milestones daily.',
    points: [
      'Categorized discord channels (Forex, Crypto, Stocks)',
      'Weekly community trading challenges',
      'Peer review workspace for trading plans',
      'Direct mentorship access and collaboration',
    ],
  },
]
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary">
      <div class="container mx-auto px-6 pt-24 pb-16 md:pt-32 md:pb-24 max-w-6xl">
        <!-- Hero Section -->
        <div class="text-center mb-28">
          <div class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider">
            Platform Capabilities
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Designed for <span class="text-primary">Precision</span> Trading
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            Our curriculum blends mathematical precision with technical art. Explore the advanced features that make The Fibonacci Gallery the premier destination for serious market analysts.
          </p>
        </div>

        <!-- Detailed Feature Blocks -->
        <div class="space-y-16">
          <div
            v-for="(feat, index) in detailedFeatures"
            :key="feat.title"
            class="grid md:grid-cols-12 gap-8 items-center border border-border/20 rounded-3xl p-8 md:p-12 liquid-glass"
          >
            <!-- Content -->
            <div :class="['md:col-span-7 space-y-6', index % 2 === 1 ? 'md:order-last' : '']">
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary">
                <component :is="feat.icon" class="h-6 w-6" />
              </div>
              <h2 class="text-2xl md:text-3xl font-semibold">{{ feat.title }}</h2>
              <p class="text-muted-foreground leading-relaxed">{{ feat.description }}</p>
              
              <ul class="space-y-3">
                <li v-for="point in feat.points" :key="point" class="flex items-start gap-3 text-sm text-muted-foreground">
                  <PhCheckCircle class="h-5 w-5 text-primary shrink-0 mt-0.5" weight="fill" />
                  <span>{{ point }}</span>
                </li>
              </ul>
            </div>
            
            <!-- Graphic/Visual Element -->
            <div class="md:col-span-5 flex justify-center">
              <div class="w-full aspect-4/3 rounded-2xl bg-muted/30 border border-border/10 flex items-center justify-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-linear-to-tr from-primary/10 to-transparent opacity-60"></div>
                <component :is="feat.icon" class="h-20 w-20 text-primary/30 group-hover:scale-110 transition-transform duration-500" />
              </div>
            </div>
          </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-24 text-center border border-border/20 rounded-3xl p-8 md:p-16 liquid-glass bg-linear-to-b from-primary/5 to-transparent">
          <h2 class="text-3xl font-semibold mb-4">Start Your Trading Journey Today</h2>
          <p class="text-muted-foreground max-w-xl mx-auto mb-8">
            Access institutional-grade knowledge, live sessions, and the supportive community you need to thrive.
          </p>
          <div class="flex justify-center gap-4">
            <Button size="lg" as-child>
              <RouterLink to="/register">
                Join Now <PhArrowRight class="ml-2 h-4 w-4" />
              </RouterLink>
            </Button>
            <Button variant="outline" size="lg" as-child>
              <RouterLink to="/explore">Explore Courses</RouterLink>
            </Button>
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
