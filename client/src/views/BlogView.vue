<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { PhArrowRight, PhCalendar, PhClock, PhUser } from '@phosphor-icons/vue'

const authStore = useAuthStore()

const featuredPost = {
  title: 'Mastering Fibonacci Confluence Zones in Live Markets',
  excerpt: 'Learn how combining multiple Fibonacci retracement and extension levels creates powerful confluence areas for highly accurate entry and exit signals.',
  category: 'Advanced Strategy',
  date: 'July 5, 2026',
  readTime: '8 min read',
  author: 'Marcus Chen',
}

const articles = [
  {
    title: 'Trading Psychology: Overcoming the Fear of Missing Out (FOMO)',
    excerpt: 'Explore actionable techniques to master your emotions, stick to your trading plan, and maintain discipline during volatile market conditions.',
    category: 'Psychology',
    date: 'June 28, 2026',
    readTime: '5 min read',
    author: 'Elena Rostova',
  },
  {
    title: 'Why the 1% Risk Management Model is Crucial for Prop Firm Challenges',
    excerpt: 'A deep dive into how sizing your positions to risk exactly 1% of your balance keeps you safe from breaching strict daily drawdown rules.',
    category: 'Risk Management',
    date: 'June 22, 2026',
    readTime: '6 min read',
    author: 'David Okonkwo',
  },
  {
    title: 'An Introduction to Elliott Wave Theory and Market Cycles',
    excerpt: 'Understand the basic 5-wave motive and 3-wave corrective patterns that dictate long-term market trends across Forex and Stock indices.',
    category: 'Technical Analysis',
    date: 'June 15, 2026',
    readTime: '10 min read',
    author: 'Marcus Chen',
  },
  {
    title: 'Top 5 Cryptocurrency Indicators to Combine with Fibonacci Levels',
    excerpt: 'Maximize your altcoin trading win rate by using Fibonacci retracements in combination with RSI, MACD, and volume profile indicators.',
    category: 'Crypto Strategy',
    date: 'June 10, 2026',
    readTime: '7 min read',
    author: 'Sarah Williams',
  },
]
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary">
      <div class="container mx-auto px-6 py-16 md:py-24 max-w-6xl">
        <!-- Hero Section -->
        <div class="text-center mb-16">
          <div class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider">
            Trading Library
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Educational Insights & <span class="text-primary">Blog</span>
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            Expand your trading IQ with regular articles, guides, and strategic breakdowns from our veteran team of market analysts.
          </p>
        </div>

        <!-- Featured Post Card -->
        <div class="border border-border/20 rounded-3xl p-6 md:p-10 liquid-glass mb-16 bg-linear-to-tr from-primary/5 to-transparent">
          <div class="grid md:grid-cols-12 gap-8 items-center">
            <!-- Image placeholder -->
            <div class="md:col-span-5 aspect-4/3 rounded-2xl bg-muted/40 border border-border/10 flex items-center justify-center relative overflow-hidden group">
              <div class="absolute inset-0 bg-linear-to-tr from-primary/15 to-transparent"></div>
              <span class="text-primary font-bold text-7xl opacity-40">Φ</span>
            </div>
            
            <!-- Details -->
            <div class="md:col-span-7 space-y-6">
              <span class="inline-flex text-xs font-bold uppercase tracking-wider text-primary px-3 py-1 rounded bg-primary/10 border border-primary/20">
                {{ featuredPost.category }}
              </span>
              <h2 class="text-2xl md:text-3xl font-semibold hover:text-primary transition-colors cursor-pointer">
                {{ featuredPost.title }}
              </h2>
              <p class="text-muted-foreground leading-relaxed text-sm md:text-base">
                {{ featuredPost.excerpt }}
              </p>
              
              <!-- Meta -->
              <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-1"><PhUser class="h-4 w-4" /> {{ featuredPost.author }}</span>
                <span class="flex items-center gap-1"><PhCalendar class="h-4 w-4" /> {{ featuredPost.date }}</span>
                <span class="flex items-center gap-1"><PhClock class="h-4 w-4" /> {{ featuredPost.readTime }}</span>
              </div>
              
              <Button class="rounded-xl" as-child>
                <RouterLink to="/explore">
                  Read Article <PhArrowRight class="ml-2 h-4 w-4" />
                </RouterLink>
              </Button>
            </div>
          </div>
        </div>

        <!-- Latest Articles Grid -->
        <div class="space-y-8">
          <h3 class="text-2xl font-semibold">Latest Publications</h3>
          
          <div class="grid md:grid-cols-2 gap-8">
            <div
              v-for="post in articles"
              :key="post.title"
              class="border border-border/20 rounded-3xl p-6 liquid-glass flex flex-col justify-between hover:border-primary/30 transition-all duration-300 group"
            >
              <div class="space-y-4">
                <!-- Thumbnail placeholder -->
                <div class="aspect-video w-full rounded-2xl bg-muted/30 border border-border/10 flex items-center justify-center relative overflow-hidden mb-4">
                  <div class="absolute inset-0 bg-linear-to-tr from-primary/5 to-transparent group-hover:opacity-100 opacity-60 transition-opacity"></div>
                  <span class="text-primary font-bold text-5xl opacity-20">Φ</span>
                </div>
                
                <span class="inline-flex text-[10px] font-bold uppercase tracking-wider text-primary px-2 py-0.5 rounded bg-primary/10 border border-primary/20">
                  {{ post.category }}
                </span>
                <h4 class="text-lg font-semibold group-hover:text-primary transition-colors cursor-pointer leading-snug">
                  {{ post.title }}
                </h4>
                <p class="text-sm text-muted-foreground leading-relaxed">
                  {{ post.excerpt }}
                </p>
              </div>
              
              <div class="mt-6 pt-4 border-t border-border/10 flex justify-between items-center text-xs text-muted-foreground">
                <span class="flex items-center gap-1"><PhUser class="h-3.5 w-3.5" /> {{ post.author }}</span>
                <span class="flex items-center gap-1"><PhClock class="h-3.5 w-3.5" /> {{ post.readTime }}</span>
              </div>
            </div>
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
