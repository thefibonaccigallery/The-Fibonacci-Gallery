<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { PhStar, PhArrowRight } from '@phosphor-icons/vue'

const authStore = useAuthStore()

const reviews = [
  {
    name: 'Yohannes Bekele',
    quote:
      'The Fibonacci Gallery completely changed how I look at market liquidity. Instead of gambling on volatile US30 and Gold sessions, I now wait patiently for golden pocket retracements and structural confluence. The live trade breakdowns are pure gold.',
    rating: 5,
  },
  {
    name: 'Selamawit Girma',
    quote:
      'Most trading courses online only scratch the surface, but this academy dives deep into market psychology and capital protection. The risk management framework alone prevented me from blowing accounts during volatile market swings.',
    rating: 5,
  },
  {
    name: 'Dawit Alemu',
    quote:
      'I used to follow random Telegram signal channels and lost money constantly. Enrolling here was the best decision—the step-by-step curriculum took me from absolute zero to building a real trading plan with consistent risk-to-reward.',
    rating: 5,
  },
  {
    name: 'Bethlehem Haile',
    quote:
      'The structural approach to market cycles taught here is unmatched. Understanding Fibonacci confluence and institutional order flow refined my strategy completely. My entries are cleaner and my stop-losses much tighter.',
    rating: 5,
  },
  {
    name: 'Natnael Abebe',
    quote:
      'Charts always looked like overwhelming chaos to me before taking the foundations course. The lessons break technical analysis down into clear, logical rules. Now I can analyze EURUSD and Gold on my own with confidence.',
    rating: 5,
  },
  {
    name: 'Ermias Kassahun',
    quote:
      'The community setup discussions and mentor feedback are invaluable. Having experienced traders review setups before taking entries has transformed my discipline. My win rate has steadily risen and my drawdown is under control.',
    rating: 5,
  },
]
</script>

<template>
  <DashboardLayout>
    <div
      class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary"
    >
      <div class="container mx-auto px-6 pt-24 pb-16 md:pt-32 md:pb-24 max-w-6xl">
        <!-- Hero Section -->
        <div class="text-center mb-24">
          <div
            class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider"
          >
            Student Reviews
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Trusted by <span class="text-primary">Traders</span> Globally
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            See how our students transformed their trading consistency and confidence using our
            Fibonacci-based education and live trading community workspace.
          </p>
        </div>

        <!-- Rating Highlight -->
        <div
          class="max-w-md mx-auto text-center border border-border/20 rounded-3xl p-6 mb-16 liquid-glass flex flex-col items-center justify-center gap-2"
        >
          <div class="flex gap-1">
            <PhStar v-for="n in 5" :key="n" class="h-6 w-6 text-primary" weight="fill" />
          </div>
          <p class="text-lg font-semibold mt-1">4.9 out of 5 Stars</p>
          <p class="text-xs text-muted-foreground">Based on 2,500+ student ratings worldwide</p>
        </div>

        <!-- Testimonial Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="review in reviews"
            :key="review.name"
            class="liquid-glass border border-border/20 rounded-2xl p-6 flex flex-col justify-between hover:border-primary/30 transition-all duration-300"
          >
            <div class="space-y-4">
              <!-- Rating -->
              <div class="flex gap-1">
                <PhStar
                  v-for="n in review.rating"
                  :key="n"
                  class="h-4 w-4 text-primary"
                  weight="fill"
                />
              </div>
              <!-- Quote -->
              <p class="text-sm text-foreground/80 leading-relaxed italic">"{{ review.quote }}"</p>
            </div>

            <!-- Author -->
            <div class="mt-6 pt-4 border-t border-border/10 flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-semibold text-sm"
              >
                {{ review.name.charAt(0) }}
              </div>
              <div>
                <h4 class="text-sm font-semibold text-foreground">{{ review.name }}</h4>
              </div>
            </div>
          </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-24 text-center border border-border/20 rounded-3xl p-8 md:p-16 liquid-glass">
          <h2 class="text-3xl font-semibold mb-4">Ready to Write Your Success Story?</h2>
          <p class="text-muted-foreground max-w-xl mx-auto mb-8">
            Access our masterclasses, live indicators, and supportive trading community right now.
          </p>
          <Button size="lg" as-child>
            <RouterLink to="/register">
              Get Started Now <PhArrowRight class="ml-2 h-4 w-4" />
            </RouterLink>
          </Button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
  backdrop-filter: blur(10px);
}
</style>
