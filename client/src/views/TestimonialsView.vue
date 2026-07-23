<script setup lang="ts">
import { ref, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { PhStar, PhArrowRight } from '@phosphor-icons/vue'

const authStore = useAuthStore()

const activeFilter = ref('all')

const reviews = [
  {
    name: 'Marcus Chen',
    role: 'Forex Trader',
    level: 'intermediate',
    quote: 'The Fibonacci Gallery completely transformed my approach to the markets. The live sessions alone are worth 10x the price. Being able to watch professional traders execute trades in real-time gave me the confidence I was missing.',
    rating: 5,
  },
  {
    name: 'Sarah Williams',
    role: 'Crypto Analyst',
    level: 'advanced',
    quote: 'Finally a trading course that goes beyond the basics. The risk management module saved my portfolio during the last crash. I appreciate the strict focus on protecting capital first.',
    rating: 5,
  },
  {
    name: 'David Okonkwo',
    role: 'Swing Trader',
    level: 'beginner',
    quote: 'I went from losing consistently to being profitable in 3 months. The chart pattern recognition course is incredible. The step-by-step guidance is very easy to follow for newcomers.',
    rating: 5,
  },
  {
    name: 'Elena Rostova',
    role: 'Quantitative Analyst',
    level: 'advanced',
    quote: 'The mathematical approach to market structures taught here is elite. Understanding Fibonacci clusters and confluence points has taken my automated strategies to a whole new level.',
    rating: 5,
  },
  {
    name: 'Liam Davis',
    role: 'Retail Investor',
    level: 'beginner',
    quote: 'Before this, charts looked like noise. The Fibonacci Foundations course broke down technical analysis into simple, logical pieces. I feel confident looking at any asset chart now.',
    rating: 5,
  },
  {
    name: 'Chloe Vance',
    role: 'Forex Scalper',
    level: 'intermediate',
    quote: 'The community setup reviews are insanely valuable. Sharing ideas and having them vetted by mentors prevents making stupid mistakes. My win rate has jumped from 48% to 62%.',
    rating: 5,
  },
]

const filteredReviews = computed(() => {
  if (activeFilter.value === 'all') return reviews
  return reviews.filter((r) => r.level === activeFilter.value)
})
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary">
      <div class="container mx-auto px-6 pt-24 pb-16 md:pt-32 md:pb-24 max-w-6xl">
        <!-- Hero Section -->
        <div class="text-center mb-24">
          <div class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider">
            Student Reviews
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Trusted by <span class="text-primary">Traders</span> Globally
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            See how our students transformed their trading consistency and confidence using our Fibonacci-based education and live trading community workspace.
          </p>
        </div>

        <!-- Rating Highlight -->
        <div class="max-w-md mx-auto text-center border border-border/20 rounded-3xl p-6 mb-12 liquid-glass flex flex-col items-center justify-center gap-2">
          <div class="flex gap-1">
            <PhStar v-for="n in 5" :key="n" class="h-6 w-6 text-primary" weight="fill" />
          </div>
          <p class="text-lg font-semibold mt-1">4.9 out of 5 Stars</p>
          <p class="text-xs text-muted-foreground">Based on 2,500+ student ratings worldwide</p>
        </div>

        <!-- Interactive Filters -->
        <div class="flex justify-center gap-2 mb-12 flex-wrap">
          <Button
            v-for="filter in ['all', 'beginner', 'intermediate', 'advanced']"
            :key="filter"
            @click="activeFilter = filter"
            :variant="activeFilter === filter ? 'default' : 'outline'"
            size="sm"
            class="capitalize rounded-full px-5"
          >
            {{ filter }}
          </Button>
        </div>

        <!-- Testimonial Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="review in filteredReviews"
            :key="review.name"
            class="liquid-glass border border-border/20 rounded-2xl p-6 flex flex-col justify-between hover:border-primary/30 transition-all duration-300"
          >
            <div class="space-y-4">
              <!-- Rating -->
              <div class="flex gap-1">
                <PhStar v-for="n in review.rating" :key="n" class="h-4 w-4 text-primary" weight="fill" />
              </div>
              <!-- Quote -->
              <p class="text-sm text-foreground/80 leading-relaxed italic">
                "{{ review.quote }}"
              </p>
            </div>
            
            <!-- Author -->
            <div class="mt-6 pt-4 border-t border-border/10 flex justify-between items-center">
              <div>
                <h4 class="text-sm font-semibold">{{ review.name }}</h4>
                <p class="text-xs text-muted-foreground mt-0.5">{{ review.role }}</p>
              </div>
              <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-border/20 bg-muted/20 text-muted-foreground tracking-wider">
                {{ review.level }}
              </span>
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
