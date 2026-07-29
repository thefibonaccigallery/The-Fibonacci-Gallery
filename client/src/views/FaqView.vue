<script setup lang="ts">
import { ref, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import { PhMagnifyingGlass, PhArrowRight } from '@phosphor-icons/vue'

const authStore = useAuthStore()

const searchQuery = ref('')
const selectedCategory = ref('all')

const faqCategories = [
  { id: 'all', label: 'All Questions' },
  { id: 'general', label: 'General' },
  { id: 'platform', label: 'Platform & Access' },
  { id: 'billing', label: 'Billing & Refunds' },
  { id: 'technical', label: 'Technical Details' },
]

const faqs = [
  {
    category: 'general',
    question: 'What markets do the courses cover?',
    answer:
      'Our courses cover strategies for Forex, Commodities, Crypto, and more. The Big Five principles and technical analysis methods we teach are universal, yet modified to apply effectively across all financial markets.',
  },
  {
    category: 'general',
    question: 'Do I need prior trading experience?',
    answer:
      'Not at all. Our Fibonacci Foundations course is designed for complete beginners. We start from the very basics and progressively build your knowledge up to advanced strategies.',
  },
  {
    category: 'general',
    question: 'What makes The Fibonacci Gallery different',
    answer:
      'We combine cinematic-quality educational content with real-time market analysis. Our unique approach blends technical artistry with mathematical and structural precision, helping you see the markets as living, breathing patterns of opportunity.',
  },
  {
    category: 'platform',
    question: 'How long do I have access to the courses?',
    answer:
      "You get lifetime access to all course materials, including future updates. Plus, you'll have access to our free telegram channel and priority customer support for as long as you subscribed the courses.",
  },
  {
    category: 'platform',
    question: 'Can I access the platform on my mobile phone?',
    answer:
      'Yes! The Fibonacci Gallery is fully optimized for mobile devices. You can read, view lessons, check market analytics, and join live sessions from any phone or tablet.',
  },
  {
    category: 'billing',
    question: 'Are there any recurring subscription fees?',
    answer:
      'No. All our courses are available for a one-time purchase with lifetime access. There are no monthly subscriptions or hidden maintenance fees.',
  },
  {
    category: 'billing',
    question: 'What payment methods do you accept?',
    answer:
      'We accept payments in USDT (Tether) cryptocurrency via the BEP20 network (BNB Smart Chain). Once your payment is confirmed on the blockchain, you will be enrolled in your course automatically.',
  },
  {
    category: 'technical',
    question: 'What charting software do I need to follow along?',
    answer:
      'We primarily use TradingView, which is free and industry-standard. We also embed live TradingView charts inside our platform for your convenience.',
  },
]

const filteredFaqs = computed(() => {
  return faqs.filter((faq) => {
    const matchesCategory =
      selectedCategory.value === 'all' || faq.category === selectedCategory.value
    const matchesSearch =
      faq.question.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      faq.answer.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchesCategory && matchesSearch
  })
})
</script>

<template>
  <DashboardLayout>
    <div
      class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary"
    >
      <div class="container mx-auto px-6 pt-24 pb-16 md:pt-32 md:pb-24 max-w-5xl">
        <!-- Hero Section -->
        <div class="text-center mb-24">
          <div
            class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider"
          >
            Help Center
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Frequently Asked <span class="text-primary">Questions</span>
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            Find answers to common questions about our platform, trading methodologies, pricing, and
            live sessions.
          </p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-xl mx-auto relative mb-12">
          <PhMagnifyingGlass
            class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground"
          />
          <Input
            v-model="searchQuery"
            placeholder="Search questions or keywords..."
            class="pl-12 bg-background/50 border-border/40 py-6 rounded-2xl text-base shadow-sm focus-visible:ring-primary/20"
          />
        </div>

        <div class="grid md:grid-cols-12 gap-8 items-start">
          <!-- Categories Sidebar -->
          <div
            class="md:col-span-4 flex md:flex-col gap-2 overflow-x-auto pb-4 md:pb-0 scrollbar-none"
          >
            <button
              v-for="cat in faqCategories"
              :key="cat.id"
              @click="selectedCategory = cat.id"
              :class="[
                'w-full text-left px-5 py-3.5 rounded-2xl text-sm font-semibold border transition-all duration-300 shrink-0 md:shrink',
                selectedCategory === cat.id
                  ? 'bg-primary text-primary-foreground border-primary shadow-lg shadow-primary/15'
                  : 'bg-card/45 border-border/20 text-muted-foreground hover:bg-secondary/40 hover:text-foreground',
              ]"
            >
              {{ cat.label }}
            </button>
          </div>

          <!-- FAQ Accordion -->
          <div
            class="md:col-span-8 border border-border/20 rounded-3xl p-6 md:p-8 liquid-glass min-h-[300px]"
          >
            <div v-if="filteredFaqs.length === 0" class="text-center py-12 text-muted-foreground">
              No matching questions found. Try searching different keywords.
            </div>

            <Accordion v-else type="multiple" class="space-y-4">
              <AccordionItem
                v-for="(faq, index) in filteredFaqs"
                :key="index"
                :value="`faq-${index}`"
                class="border border-border/20 rounded-2xl overflow-hidden bg-background/30"
              >
                <AccordionTrigger
                  class="px-6 py-4.5 hover:bg-secondary/30 transition-colors text-left font-semibold text-sm md:text-base leading-snug"
                >
                  {{ faq.question }}
                </AccordionTrigger>
                <AccordionContent
                  class="px-6 py-4 text-muted-foreground text-sm leading-relaxed border-t border-border/10 bg-secondary/10"
                >
                  {{ faq.answer }}
                </AccordionContent>
              </AccordionItem>
            </Accordion>
          </div>
        </div>

        <!-- CTA Contact Section -->
        <div class="mt-24 text-center border border-border/20 rounded-3xl p-8 md:p-16 liquid-glass">
          <h2 class="text-2xl font-semibold mb-2">Still have questions?</h2>
          <p class="text-muted-foreground max-w-md mx-auto mb-8">
            Can't find the answer you are looking for? Contact our trading workspace support team.
          </p>
          <Button size="lg" as-child>
            <a href="mailto:support@fibonaccigallery.com">
              Contact Support <PhArrowRight class="ml-2 h-4 w-4" />
            </a>
          </Button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
  backdrop-filter: blur(10px);
}
</style>
