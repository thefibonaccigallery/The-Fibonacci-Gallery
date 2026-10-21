<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useSeoMeta } from '@/composables/useSeoMeta'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import {
  PhPaperPlaneTilt,
  PhEnvelopeSimple,
  PhTelegramLogo,
  PhCheckCircle,
  PhClock,
  PhGlobe,
} from '@phosphor-icons/vue'

const { setSeoMeta } = useSeoMeta()

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: '',
})

const isSubmitting = ref(false)
const submitSuccess = ref(false)

const handleSubmit = async () => {
  isSubmitting.value = true
  // Simulate submission delay
  await new Promise((resolve) => setTimeout(resolve, 1500))
  isSubmitting.value = false
  submitSuccess.value = true
  
  // Reset form after success
  setTimeout(() => {
    form.value = { name: '', email: '', subject: '', message: '' }
    submitSuccess.value = false
  }, 3000)
}

const contactMethods = [
  {
    icon: PhTelegramLogo,
    title: 'Telegram Support',
    description: 'Get instant support from our team',
    action: 'Chat with us',
    link: 'https://t.me/thefibonaccigallerysupport',
    color: 'text-blue-500',
  },
  {
    icon: PhEnvelopeSimple,
    title: 'Email Us',
    description: 'Send us a detailed message',
    action: 'thefibonaccigallery@gmail.com',
    link: 'mailto:thefibonaccigallery@gmail.com',
    color: 'text-primary',
  },
]

onMounted(() => {
  setSeoMeta({
    title: 'Contact Us',
    description:
      'Get in touch with The Fibonacci Gallery support team. We are here to help you with any questions about our trading academy.',
    canonicalUrl: '/contact',
    keywords: [
      'contact fibonacci gallery',
      'trading academy support',
      'telegram trading support',
      'customer service',
    ],
  })
})
</script>

<template>
  <DashboardLayout>
    <div
      class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary"
    >
      <div class="container mx-auto px-6 pt-24 pb-16 md:pt-32 md:pb-24 max-w-6xl">
        <!-- Hero Section -->
        <div class="text-center mb-16">
          <div
            class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider"
          >
            Get In Touch
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Contact <span class="text-primary">Us</span>
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            Have questions about our courses, platform, or trading strategies? We're here to help
            you on your trading journey.
          </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mb-16">
          <!-- Contact Methods -->
          <div class="space-y-6">
            <h2 class="text-2xl font-semibold mb-6">Quick Contact</h2>
            
            <div
              v-for="method in contactMethods"
              :key="method.title"
              class="border border-border/20 rounded-2xl p-6 liquid-glass hover:border-primary/40 transition-all duration-300 group"
            >
              <div class="flex items-start gap-4">
                <div
                  class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                  :class="`bg-${method.color.split('-')[1]}-500/10`"
                >
                  <component :is="method.icon" :class="method.color" class="w-6 h-6" />
                </div>
                <div class="flex-1">
                  <h3 class="font-semibold text-lg mb-1">{{ method.title }}</h3>
                  <p class="text-sm text-muted-foreground mb-3">{{ method.description }}</p>
                  <Button size="sm" variant="outline" as-child>
                    <a
                      :href="method.link"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-2"
                    >
                      {{ method.action }}
                      <PhPaperPlaneTilt class="w-4 h-4" />
                    </a>
                  </Button>
                </div>
              </div>
            </div>

            <!-- Info Cards -->
            <div class="space-y-4 mt-8">
              <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <PhClock class="w-5 h-5 text-primary shrink-0" />
                <span>Response time: Usually within 24 hours</span>
              </div>
              <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <PhGlobe class="w-5 h-5 text-primary shrink-0" />
                <span>Available worldwide for global traders</span>
              </div>
            </div>
          </div>

          <!-- Contact Form -->
          <div class="border border-border/20 rounded-2xl p-6 md:p-8 liquid-glass">
            <h2 class="text-2xl font-semibold mb-6">Send Us a Message</h2>
            
            <form @submit.prevent="handleSubmit" class="space-y-5">
              <div class="space-y-2">
                <label for="name" class="text-sm font-medium text-foreground">Your Name</label>
                <Input
                  id="name"
                  v-model="form.name"
                  placeholder="John Doe"
                  required
                  class="bg-background/50 border-border/40"
                />
              </div>

              <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-foreground">Email Address</label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="john@example.com"
                  required
                  class="bg-background/50 border-border/40"
                />
              </div>

              <div class="space-y-2">
                <label for="subject" class="text-sm font-medium text-foreground">Subject</label>
                <Input
                  id="subject"
                  v-model="form.subject"
                  placeholder="How can we help you?"
                  required
                  class="bg-background/50 border-border/40"
                />
              </div>

              <div class="space-y-2">
                <label for="message" class="text-sm font-medium text-foreground">Message</label>
                <Textarea
                  id="message"
                  v-model="form.message"
                  placeholder="Tell us more about your question or concern..."
                  rows="5"
                  required
                  class="bg-background/50 border-border/40 resize-none"
                />
              </div>

              <Button
                type="submit"
                size="lg"
                class="w-full"
                :disabled="isSubmitting || submitSuccess"
              >
                <PhCheckCircle v-if="submitSuccess" class="mr-2 h-5 w-5" />
                <PhPaperPlaneTilt v-else class="mr-2 h-5 w-5" />
                {{ submitSuccess ? 'Message Sent!' : isSubmitting ? 'Sending...' : 'Send Message' }}
              </Button>

              <p class="text-xs text-muted-foreground text-center">
                By submitting this form, you agree to our privacy policy and terms of service.
              </p>
            </form>
          </div>
        </div>

        <!-- FAQ Link Section -->
        <div class="text-center border border-border/20 rounded-3xl p-8 md:p-12 liquid-glass">
          <h2 class="text-2xl font-semibold mb-2">Looking for Quick Answers?</h2>
          <p class="text-muted-foreground max-w-md mx-auto mb-6">
            Check out our FAQ section for instant answers to common questions about courses, billing,
            and platform access.
          </p>
          <Button size="lg" variant="outline" as-child>
            <RouterLink to="/faq">
              Visit FAQ Section
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
