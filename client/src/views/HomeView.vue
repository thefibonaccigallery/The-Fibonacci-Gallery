<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import AnimatedHeading from '@/components/blocks/AnimatedHeading.vue'
import FadeIn from '@/components/blocks/FadeIn.vue'
import VideoBackground from '@/components/blocks/VideoBackground.vue'
import TraderviewWidget from '@/components/blocks/TraderviewWidget.vue'
import {
  PhChartLineUp,
  PhShieldCheck,
  PhVideoCamera,
  PhRocket,
  PhUsers,
  PhCertificate,
  PhClock,
  PhArrowRight,
  PhStar,
  PhList,
  PhX,
  PhCaretDown,
} from '@phosphor-icons/vue'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'

// Mobile menu state
const mobileMenuOpen = ref(false)

// Scroll-based navbar background
const scrolled = ref(false)
onMounted(() => {
  window.addEventListener('scroll', () => {
    scrolled.value = window.scrollY > 50
  })
})

// Stats counter animation
const statsVisible = ref(false)
const statsRef = ref<HTMLElement | null>(null)
onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        statsVisible.value = true
        observer.disconnect()
      }
    },
    { threshold: 0.3 },
  )
  if (statsRef.value) observer.observe(statsRef.value)
})

// Section visibility for scroll animations
const visibleSections = ref<Set<string>>(new Set())
onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          visibleSections.value.add(entry.target.id)
        }
      })
    },
    { threshold: 0.15 },
  )
  document.querySelectorAll('[data-animate]').forEach((el) => {
    observer.observe(el)
  })
})

const heroVideoUrl =
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260423_084718_72a17915-4964-4059-afcd-22d59399b72e.mp4'

const featureVideos = [
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260418_094631_d30ab262-45ee-4b7d-99f3-5d5848c8ef13.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260418_115655_b4d9cd77-feed-43cd-a198-af78ebdf1f7a.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260421_072701_f6a01abb-eb30-4559-9d6e-774362defbc3.mp4',
]

const courseVideos = [
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260308_114720_3dabeb9e-2c39-4907-b747-bc3544e2d5b7.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260307_083826_e938b29f-a43a-41ec-a153-3d4730578ab8.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260325_132944_a0d124bb-eaa1-4082-aa30-2310efb42b4b.mp4',
]

const features = [
  {
    icon: PhChartLineUp,
    title: 'Technical Analysis Mastery',
    description:
      'Learn to read charts like a professional. Master Fibonacci retracements, Elliott Wave theory, and advanced pattern recognition.',
    video: featureVideos[0],
  },
  {
    icon: PhShieldCheck,
    title: 'Risk Management',
    description:
      'Protect your capital with battle-tested risk management strategies. Position sizing, stop-loss placement, and portfolio diversification.',
    video: featureVideos[1],
  },
  {
    icon: PhVideoCamera,
    title: 'Live Market Sessions',
    description:
      'Trade alongside expert traders in real-time. Watch live analysis, get instant feedback, and learn from every market move.',
    video: featureVideos[2],
  },
]

const courses = [
  {
    title: 'Fibonacci Foundations',
    level: 'Beginner',
    lessons: 24,
    duration: '12 hours',
    price: '$97',
    description:
      'Start your trading journey with the mathematical principles behind market movements.',
    video: courseVideos[0],
  },
  {
    title: 'Advanced Chart Patterns',
    level: 'Intermediate',
    lessons: 36,
    duration: '18 hours',
    price: '$197',
    description:
      'Dive deep into harmonic patterns, supply & demand zones, and multi-timeframe analysis.',
    video: courseVideos[1],
  },
  {
    title: 'Elite Trading Masterclass',
    level: 'Advanced',
    lessons: 48,
    duration: '24 hours',
    price: '$497',
    description:
      'The complete system: institutional order flow, smart money concepts, and algorithmic edge detection.',
    video: courseVideos[2],
  },
]

const testimonials = [
  {
    name: 'Marcus Chen',
    role: 'Forex Trader',
    quote:
      'The Fibonacci Gallery completely transformed my approach to the markets. The live sessions alone are worth 10x the price.',
    rating: 5,
  },
  {
    name: 'Sarah Williams',
    role: 'Crypto Analyst',
    quote:
      'Finally a trading course that goes beyond the basics. The risk management module saved my portfolio during the last crash.',
    rating: 5,
  },
  {
    name: 'David Okonkwo',
    role: 'Swing Trader',
    quote:
      'I went from losing consistently to being profitable in 3 months. The chart pattern recognition course is incredible.',
    rating: 5,
  },
]

const faqs = [
  {
    question: 'What markets do the courses cover?',
    answer:
      'Our courses cover Forex, Crypto, Stocks, and Commodities. The Fibonacci principles and technical analysis methods taught are universal and apply across all financial markets.',
  },
  {
    question: 'Do I need prior trading experience?',
    answer:
      'Not at all. Our Fibonacci Foundations course is designed for complete beginners. We start from the very basics and progressively build your knowledge up to advanced strategies.',
  },
  {
    question: 'How long do I have access to the courses?',
    answer:
      "You get lifetime access to all course materials, including future updates. Plus, you'll have access to our community Discord and weekly live sessions for as long as your membership is active.",
  },
  {
    question: 'Is there a money-back guarantee?',
    answer:
      "Yes! We offer a 30-day money-back guarantee on all courses. If you're not satisfied with the content, simply reach out and we'll process a full refund within 48 hours.",
  },
  {
    question: 'What makes The Fibonacci Gallery different?',
    answer:
      'We combine cinematic-quality educational content with real-time market analysis. Our unique approach blends technical artistry with mathematical precision, helping you see the markets as living, breathing patterns of opportunity.',
  },
]

const navLinks = [
  { label: 'Features', href: '#features' },
  { label: 'Courses', href: '#courses' },
  { label: 'Markets', href: '#markets' },
  { label: 'Testimonials', href: '#testimonials' },
  { label: 'FAQ', href: '#faq' },
]
</script>

<template>
  <div class="min-h-screen bg-background text-foreground overflow-x-hidden">
    <!-- ═══════ NAVBAR ═══════ -->
    <nav
      id="navbar"
      class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
      :class="[scrolled ? 'liquid-glass border-b border-border/20 py-3' : 'py-5']"
    >
      <div class="max-w-7xl mx-auto px-6 md:px-12 lg:px-16 flex items-center justify-between">
        <!-- Brand -->
        <a href="#" class="text-xl font-semibold tracking-tight text-foreground">
          The Fibonacci Gallery
        </a>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-8">
          <a
            v-for="link in navLinks"
            :key="link.label"
            :href="link.href"
            class="text-sm text-muted-foreground hover:text-foreground transition-colors"
          >
            {{ link.label }}
          </a>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
          <ThemeToggle />
          <Button class="hidden md:inline-flex" size="sm">
            Get Started
            <PhArrowRight class="ml-1 h-4 w-4" />
          </Button>
          <!-- Mobile Menu Toggle -->
          <Button
            variant="ghost"
            size="icon"
            class="md:hidden"
            @click="mobileMenuOpen = !mobileMenuOpen"
            aria-label="Toggle menu"
          >
            <PhList v-if="!mobileMenuOpen" class="h-5 w-5" />
            <PhX v-else class="h-5 w-5" />
          </Button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden liquid-glass border-t border-border/20 mt-2">
        <div class="px-6 py-4 flex flex-col gap-3">
          <a
            v-for="link in navLinks"
            :key="link.label"
            :href="link.href"
            class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
            @click="mobileMenuOpen = false"
          >
            {{ link.label }}
          </a>
          <Button class="mt-2 w-full" size="sm">
            Get Started
            <PhArrowRight class="ml-1 h-4 w-4" />
          </Button>
        </div>
      </div>
    </nav>

    <!-- ═══════ HERO SECTION ═══════ -->
    <section id="hero" class="relative min-h-screen flex items-center justify-center">
      <VideoBackground :src="heroVideoUrl" :overlay-opacity="0.5" />

      <div class="relative z-10 max-w-4xl mx-auto px-6 md:px-12 text-center pt-24">
        <!-- Glass pill tagline -->
        <FadeIn :delay="100" :duration="600">
          <div
            class="inline-flex items-center gap-2 liquid-glass rounded-full px-5 py-2 border border-border/20 mb-8"
          >
            <PhRocket class="h-4 w-4 text-primary" />
            <span class="text-sm text-muted-foreground">Master the Markets with Precision</span>
          </div>
        </FadeIn>

        <!-- Animated Heading -->
        <AnimatedHeading
          text="Master The Art
of Trading"
          tag="h1"
          :delay="300"
          :char-delay="35"
          class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-normal mb-6"
          :style="{ letterSpacing: '-0.04em' }"
        />

        <!-- Subheadline -->
        <FadeIn :delay="1000" :duration="1000">
          <p class="text-base md:text-lg text-muted-foreground mb-8 max-w-2xl mx-auto">
            Unlock the hidden patterns in financial markets with our Fibonacci-based trading system.
            Learn from expert traders, access live sessions, and join a community of 2,500+ traders
            who've transformed their approach to the markets.
          </p>
        </FadeIn>

        <!-- CTAs -->
        <FadeIn :delay="1400" :duration="1000">
          <div class="flex flex-wrap gap-4 justify-center">
            <Button size="lg" class="px-8 py-3 rounded-lg font-medium text-base">
              Start Learning
              <PhArrowRight class="ml-2 h-4 w-4" />
            </Button>
            <Button
              variant="outline"
              size="lg"
              class="px-8 py-3 rounded-lg font-medium text-base liquid-glass border border-border/20"
            >
              Explore Courses
              <PhCaretDown class="ml-2 h-4 w-4" />
            </Button>
          </div>
        </FadeIn>

        <!-- Scroll indicator -->
        <FadeIn :delay="2000" :duration="1000">
          <div class="mt-16 animate-float">
            <a href="#stats" class="text-muted-foreground hover:text-foreground transition-colors">
              <PhCaretDown class="h-6 w-6 mx-auto" />
            </a>
          </div>
        </FadeIn>
      </div>
    </section>

    <!-- ═══════ STATS BAR ═══════ -->
    <section id="stats" ref="statsRef" class="relative z-10 -mt-16 px-6 md:px-12 lg:px-16">
      <div
        class="max-w-5xl mx-auto liquid-glass rounded-2xl border border-border/20 px-6 py-8 md:px-12 md:py-10"
      >
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
          <div
            v-for="(stat, i) in [
              { value: '2,500+', label: 'Active Traders', icon: PhUsers },
              { value: '95%', label: 'Success Rate', icon: PhChartLineUp },
              { value: '50+', label: 'Trading Courses', icon: PhCertificate },
              { value: '24/7', label: 'Live Markets', icon: PhClock },
            ]"
            :key="stat.label"
            class="text-center transition-all duration-700"
            :style="{
              opacity: statsVisible ? 1 : 0,
              transform: statsVisible ? 'scale(1)' : 'scale(0.8)',
              transitionDelay: `${i * 150}ms`,
            }"
          >
            <component :is="stat.icon" class="h-6 w-6 text-primary mx-auto mb-2" />
            <p
              class="text-2xl md:text-3xl font-semibold text-foreground"
              style="letter-spacing: -0.03em"
            >
              {{ stat.value }}
            </p>
            <p class="text-sm text-muted-foreground mt-1">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════ FEATURES SECTION ═══════ -->
    <section id="features" data-animate class="py-24 lg:py-32 px-6 md:px-12 lg:px-16">
      <div class="max-w-7xl mx-auto">
        <!-- Section header -->
        <div
          class="text-center mb-16 transition-all duration-700"
          :style="{
            opacity: visibleSections.has('features') ? 1 : 0,
            transform: visibleSections.has('features') ? 'translateY(0)' : 'translateY(24px)',
          }"
        >
          <div
            class="inline-flex items-center gap-2 liquid-glass rounded-full px-4 py-1.5 border border-border/20 mb-6"
          >
            <span class="text-xs text-primary font-medium uppercase tracking-wider"
              >Why Choose Us</span
            >
          </div>
          <h2
            class="text-3xl md:text-4xl lg:text-5xl font-normal mb-4"
            style="letter-spacing: -0.03em"
          >
            Everything You Need to
            <span class="text-primary">Succeed</span>
          </h2>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto">
            Our platform combines world-class education with real-time market tools to give you the
            ultimate trading advantage.
          </p>
        </div>

        <!-- Feature cards -->
        <div class="grid md:grid-cols-3 gap-6">
          <div
            v-for="(feature, i) in features"
            :key="feature.title"
            class="group relative rounded-2xl overflow-hidden border border-border/20 transition-all duration-500 hover:border-primary/30 min-h-[380px]"
            :style="{
              opacity: visibleSections.has('features') ? 1 : 0,
              transform: visibleSections.has('features') ? 'translateY(0)' : 'translateY(30px)',
              transitionDelay: `${i * 150 + 200}ms`,
            }"
          >
            <!-- Card video background -->
            <div class="absolute inset-0">
              <video
                autoplay
                loop
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
              >
                <source :src="feature.video" type="video/mp4" />
              </video>
              <div
                class="absolute inset-0 bg-background/80 dark:bg-black/70 transition-opacity duration-500 group-hover:opacity-90"
              />
            </div>

            <!-- Card content -->
            <div class="relative z-10 p-8 flex flex-col h-full justify-end">
              <div
                class="inline-flex items-center justify-center w-12 h-12 rounded-xl liquid-glass border border-border/20 mb-4"
              >
                <component :is="feature.icon" class="h-6 w-6 text-primary" />
              </div>
              <h3 class="text-xl font-medium mb-3 text-foreground">{{ feature.title }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ feature.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════ COURSES SECTION ═══════ -->
    <section
      id="courses"
      data-animate
      class="py-24 lg:py-32 px-6 md:px-12 lg:px-16 bg-secondary/30 dark:bg-black/50"
    >
      <div class="max-w-7xl mx-auto">
        <!-- Section header -->
        <div
          class="text-center mb-16 transition-all duration-700"
          :style="{
            opacity: visibleSections.has('courses') ? 1 : 0,
            transform: visibleSections.has('courses') ? 'translateY(0)' : 'translateY(24px)',
          }"
        >
          <div
            class="inline-flex items-center gap-2 liquid-glass rounded-full px-4 py-1.5 border border-border/20 mb-6"
          >
            <span class="text-xs text-primary font-medium uppercase tracking-wider"
              >Our Courses</span
            >
          </div>
          <h2
            class="text-3xl md:text-4xl lg:text-5xl font-normal mb-4"
            style="letter-spacing: -0.03em"
          >
            Choose Your
            <span class="text-primary">Path</span>
          </h2>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto">
            From beginner fundamentals to elite strategies, we have a course tailored to your
            trading level.
          </p>
        </div>

        <!-- Course cards -->
        <div class="grid md:grid-cols-3 gap-6">
          <div
            v-for="(course, i) in courses"
            :key="course.title"
            class="group relative rounded-2xl overflow-hidden border border-border/20 transition-all duration-500 hover:border-primary/30 flex flex-col"
            :style="{
              opacity: visibleSections.has('courses') ? 1 : 0,
              transform: visibleSections.has('courses') ? 'translateY(0)' : 'translateY(30px)',
              transitionDelay: `${i * 150 + 200}ms`,
            }"
          >
            <!-- Video thumbnail -->
            <div class="relative h-48 overflow-hidden">
              <video
                autoplay
                loop
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
              >
                <source :src="course.video" type="video/mp4" />
              </video>
              <div class="absolute inset-0 bg-background/30 dark:bg-black/30" />
              <!-- Level badge -->
              <div class="absolute top-4 left-4 z-10">
                <span
                  class="liquid-glass rounded-full px-3 py-1 text-xs font-medium border border-border/20"
                  :class="[
                    course.level === 'Beginner'
                      ? 'text-green-600 dark:text-green-400'
                      : course.level === 'Intermediate'
                        ? 'text-yellow-600 dark:text-yellow-400'
                        : 'text-red-500 dark:text-red-400',
                  ]"
                >
                  {{ course.level }}
                </span>
              </div>
            </div>

            <!-- Card body -->
            <div class="flex flex-col flex-1 p-6 bg-card">
              <h3 class="text-lg font-medium mb-2 text-foreground">{{ course.title }}</h3>
              <p class="text-sm text-muted-foreground mb-4 flex-1">{{ course.description }}</p>

              <!-- Meta -->
              <div class="flex items-center gap-4 text-xs text-muted-foreground mb-4">
                <span class="flex items-center gap-1">
                  <PhVideoCamera class="h-3.5 w-3.5" />
                  {{ course.lessons }} Lessons
                </span>
                <span class="flex items-center gap-1">
                  <PhClock class="h-3.5 w-3.5" />
                  {{ course.duration }}
                </span>
              </div>

              <!-- Price & CTA -->
              <div class="flex items-center justify-between pt-4 border-t border-border/20">
                <span class="text-2xl font-semibold text-foreground">{{ course.price }}</span>
                <Button size="sm" class="rounded-lg">
                  Enroll Now
                  <PhArrowRight class="ml-1 h-3.5 w-3.5" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════ MARKETS / TRADINGVIEW SECTION ═══════ -->
    <section id="markets" data-animate class="py-24 lg:py-32 px-6 md:px-12 lg:px-16">
      <div class="max-w-7xl mx-auto">
        <div
          class="grid lg:grid-cols-2 gap-12 items-center"
          :style="{
            opacity: visibleSections.has('markets') ? 1 : 0,
            transform: visibleSections.has('markets') ? 'translateY(0)' : 'translateY(24px)',
            transition: 'all 0.7s ease-out',
          }"
        >
          <!-- Text content -->
          <div>
            <div
              class="inline-flex items-center gap-2 liquid-glass rounded-full px-4 py-1.5 border border-border/20 mb-6"
            >
              <span class="text-xs text-primary font-medium uppercase tracking-wider"
                >Live Markets</span
              >
            </div>
            <h2
              class="text-3xl md:text-4xl lg:text-5xl font-normal mb-6"
              style="letter-spacing: -0.03em"
            >
              Real-Time Market
              <span class="text-primary">Data</span>
            </h2>
            <p class="text-base md:text-lg text-muted-foreground mb-6">
              Stay connected to the markets 24/7. Our integrated TradingView widget gives you
              instant access to indices, futures, bonds, and forex pairs — all in one place.
            </p>
            <ul class="space-y-3 text-sm text-muted-foreground">
              <li class="flex items-center gap-2">
                <PhChartLineUp class="h-4 w-4 text-primary flex-shrink-0" />
                Live price feeds across all major markets
              </li>
              <li class="flex items-center gap-2">
                <PhShieldCheck class="h-4 w-4 text-primary flex-shrink-0" />
                Institutional-grade charting tools
              </li>
              <li class="flex items-center gap-2">
                <PhClock class="h-4 w-4 text-primary flex-shrink-0" />
                24/7 market monitoring and alerts
              </li>
            </ul>
          </div>

          <!-- TradingView Widget -->
          <div class="flex justify-center lg:justify-end">
            <div class="rounded-2xl overflow-hidden border border-border/20 liquid-glass p-1">
              <TraderviewWidget />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════ TESTIMONIALS SECTION ═══════ -->
    <section
      id="testimonials"
      data-animate
      class="py-24 lg:py-32 px-6 md:px-12 lg:px-16 bg-secondary/30 dark:bg-black/50"
    >
      <div class="max-w-7xl mx-auto">
        <!-- Section header -->
        <div
          class="text-center mb-16 transition-all duration-700"
          :style="{
            opacity: visibleSections.has('testimonials') ? 1 : 0,
            transform: visibleSections.has('testimonials') ? 'translateY(0)' : 'translateY(24px)',
          }"
        >
          <div
            class="inline-flex items-center gap-2 liquid-glass rounded-full px-4 py-1.5 border border-border/20 mb-6"
          >
            <span class="text-xs text-primary font-medium uppercase tracking-wider"
              >Testimonials</span
            >
          </div>
          <h2
            class="text-3xl md:text-4xl lg:text-5xl font-normal mb-4"
            style="letter-spacing: -0.03em"
          >
            Trusted by
            <span class="text-primary">Traders</span>
          </h2>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto">
            Hear from students who've transformed their trading with The Fibonacci Gallery.
          </p>
        </div>

        <!-- Testimonial cards -->
        <div class="grid md:grid-cols-3 gap-6">
          <div
            v-for="(testimonial, i) in testimonials"
            :key="testimonial.name"
            class="liquid-glass rounded-2xl border border-border/20 p-8 transition-all duration-500 hover:border-primary/20"
            :style="{
              opacity: visibleSections.has('testimonials') ? 1 : 0,
              transform: visibleSections.has('testimonials') ? 'translateY(0)' : 'translateY(30px)',
              transitionDelay: `${i * 150 + 200}ms`,
            }"
          >
            <!-- Stars -->
            <div class="flex gap-1 mb-4">
              <PhStar
                v-for="s in testimonial.rating"
                :key="s"
                weight="fill"
                class="h-4 w-4 text-primary"
              />
            </div>

            <!-- Quote -->
            <p class="text-sm text-muted-foreground leading-relaxed mb-6 italic">
              "{{ testimonial.quote }}"
            </p>

            <!-- Author -->
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-semibold text-sm"
              >
                {{ testimonial.name.charAt(0) }}
              </div>
              <div>
                <p class="text-sm font-medium text-foreground">{{ testimonial.name }}</p>
                <p class="text-xs text-muted-foreground">{{ testimonial.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════ FAQ SECTION ═══════ -->
    <section id="faq" data-animate class="py-24 lg:py-32 px-6 md:px-12 lg:px-16">
      <div class="max-w-3xl mx-auto">
        <!-- Section header -->
        <div
          class="text-center mb-16 transition-all duration-700"
          :style="{
            opacity: visibleSections.has('faq') ? 1 : 0,
            transform: visibleSections.has('faq') ? 'translateY(0)' : 'translateY(24px)',
          }"
        >
          <div
            class="inline-flex items-center gap-2 liquid-glass rounded-full px-4 py-1.5 border border-border/20 mb-6"
          >
            <span class="text-xs text-primary font-medium uppercase tracking-wider">FAQ</span>
          </div>
          <h2
            class="text-3xl md:text-4xl lg:text-5xl font-normal mb-4"
            style="letter-spacing: -0.03em"
          >
            Frequently Asked
            <span class="text-primary">Questions</span>
          </h2>
        </div>

        <!-- Accordion -->
        <div
          class="transition-all duration-700"
          :style="{
            opacity: visibleSections.has('faq') ? 1 : 0,
            transform: visibleSections.has('faq') ? 'translateY(0)' : 'translateY(20px)',
            transitionDelay: '200ms',
          }"
        >
          <Accordion type="single" collapsible class="w-full space-y-3">
            <AccordionItem
              v-for="(faq, i) in faqs"
              :key="i"
              :value="`faq-${i}`"
              class="liquid-glass rounded-xl border border-border/20 px-6 overflow-hidden"
            >
              <AccordionTrigger
                class="text-sm md:text-base font-medium text-foreground hover:no-underline"
              >
                {{ faq.question }}
              </AccordionTrigger>
              <AccordionContent class="text-sm text-muted-foreground leading-relaxed pb-4">
                {{ faq.answer }}
              </AccordionContent>
            </AccordionItem>
          </Accordion>
        </div>
      </div>
    </section>

    <!-- ═══════ FINAL CTA SECTION ═══════ -->
    <section id="cta" data-animate class="relative py-24 lg:py-32 px-6 md:px-12 lg:px-16">
      <VideoBackground :src="courseVideos[2]" :overlay-opacity="0.6" />

      <div
        class="relative z-10 max-w-4xl mx-auto text-center transition-all duration-700"
        :style="{
          opacity: visibleSections.has('cta') ? 1 : 0,
          transform: visibleSections.has('cta') ? 'translateY(0)' : 'translateY(24px)',
        }"
      >
        <div
          class="inline-flex items-center gap-2 liquid-glass rounded-full px-5 py-2 border border-border/20 mb-8"
        >
          <PhRocket class="h-4 w-4 text-primary" />
          <span class="text-sm text-muted-foreground">Start Your Journey Today</span>
        </div>

        <h2
          class="text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-normal mb-6"
          style="letter-spacing: -0.04em"
        >
          Ready to Transform Your
          <span class="text-primary">Trading?</span>
        </h2>

        <p class="text-base md:text-lg text-muted-foreground mb-10 max-w-2xl mx-auto">
          Join 2,500+ traders who've already discovered the power of Fibonacci-based analysis. Your
          first profitable trade is closer than you think.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
          <Button size="lg" class="px-10 py-4 rounded-lg font-medium text-base">
            Start Learning Now
            <PhArrowRight class="ml-2 h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            size="lg"
            class="px-10 py-4 rounded-lg font-medium text-base liquid-glass border border-border/20"
          >
            View All Courses
          </Button>
        </div>
      </div>
    </section>

    <!-- ═══════ FOOTER ═══════ -->
    <footer class="border-t border-border/20 py-12 px-6 md:px-12 lg:px-16 bg-card">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <!-- Brand -->
          <div class="md:col-span-1">
            <p class="text-lg font-semibold tracking-tight text-foreground mb-3">
              The Fibonacci Gallery
            </p>
            <p class="text-sm text-muted-foreground leading-relaxed">
              Where mathematics meets market mastery. Learn to trade with precision and confidence.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <p class="text-sm font-medium text-foreground mb-3">Platform</p>
            <ul class="space-y-2">
              <li>
                <a
                  href="#courses"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Courses</a
                >
              </li>
              <li>
                <a
                  href="#features"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Features</a
                >
              </li>
              <li>
                <a
                  href="#markets"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Live Markets</a
                >
              </li>
            </ul>
          </div>

          <!-- Resources -->
          <div>
            <p class="text-sm font-medium text-foreground mb-3">Resources</p>
            <ul class="space-y-2">
              <li>
                <a
                  href="#faq"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >FAQ</a
                >
              </li>
              <li>
                <a
                  href="#testimonials"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Testimonials</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Blog</a
                >
              </li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <p class="text-sm font-medium text-foreground mb-3">Legal</p>
            <ul class="space-y-2">
              <li>
                <a
                  href="#"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Privacy Policy</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Terms of Service</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                  >Disclaimer</a
                >
              </li>
            </ul>
          </div>
        </div>

        <!-- Bottom bar -->
        <div
          class="border-t border-border/20 pt-8 flex flex-col md:flex-row items-center justify-between gap-4"
        >
          <p class="text-xs text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} The Fibonacci Gallery. All rights reserved.
          </p>
          <p class="text-xs text-muted-foreground">
            Trading involves risk. Past performance is not indicative of future results.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
