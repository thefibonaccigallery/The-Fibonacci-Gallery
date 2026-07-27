<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import Cookies from 'js-cookie'
import { Button } from '@/components/ui/button'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import AnimatedHeading from '@/components/blocks/AnimatedHeading.vue'
import FadeIn from '@/components/blocks/FadeIn.vue'
import VideoBackground from '@/components/blocks/VideoBackground.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import TraderviewWidget from '@/components/blocks/TraderviewWidget.vue'
import axiosInstance from '@/api/axiosInstance'
import {
  PhChartLineUp,
  PhShieldCheck,
  PhVideoCamera,
  PhRocket,
  PhUsers,
  PhCertificate,
  PhClock,
  PhGlobe,
  PhTrendUp,
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
      if (entry?.isIntersecting) {
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
  'https://res.cloudinary.com/dqxy77qks/video/upload/v1777545513/204306-923909642_medium_zpfrhy.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260421_072701_f6a01abb-eb30-4559-9d6e-774362defbc3.mp4',
  'https://res.cloudinary.com/dqxy77qks/video/upload/v1778869902/308080_medium_gzownl.mp4',
]

const courseVideos = [
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260308_114720_3dabeb9e-2c39-4907-b747-bc3544e2d5b7.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260307_083826_e938b29f-a43a-41ec-a153-3d4730578ab8.mp4',
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260325_132944_a0d124bb-eaa1-4082-aa30-2310efb42b4b.mp4',
]
const footerSectionVideo = [
  'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260423_161253_c72b1869-400f-45ed-ac0c-52f68c2ed5bd.mp4',
]

const features = [
  {
    icon: PhChartLineUp,
    title: 'Technical Analysis Mastery',
    description:
      'Learn to read charts like a professional. Master The Big Five strategy  and advanced patterns.',
    video: featureVideos[0],
  },
  {
    icon: PhShieldCheck,
    title: 'Risk Management',
    description:
      'Protect vour capital with back-tested risk management strategies. Position sizing ,capital preservation, Mental & Psychological Discipline.',
    video: featureVideos[1],
  },
  {
    icon: PhTrendUp,
    title: 'Weekly Gold Analysis',
    description:
      'Stay ahead of the market with in-depth weekly gold analysis. Get detailed breakdowns of price action, key market levels, and high-probability setups.',
    video: featureVideos[2],
  },
]

// ── Courses from backend ──────────────────────────────────────────────────────
interface BackendCourse {
  uuid: string
  title: string
  description: string
  thumbnail_url: string | null
  level: string
  price: string
  totalLessons: number
  length_seconds: number
  published_at: string
}

const fetchedCourses = ref<BackendCourse[]>([])
const coursesLoading = ref(true)
const coursesError = ref(false)

onMounted(async () => {
  try {
    const res = await axiosInstance.get('/public-courses')
    fetchedCourses.value = res.data.courses ?? []
  } catch {
    coursesError.value = true
  } finally {
    coursesLoading.value = false
  }
})

/** Format length_seconds → human-readable string */
function formatDuration(seconds: number): string {
  if (!seconds) return 'N/A'
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  if (h > 0) return `${h}h ${m > 0 ? m + 'm' : ''}`.trim()
  return `${m}m`
}

/** Always show exactly 3 cards; pad with coming-soon placeholders */
const displayCourses = computed(() => {
  const real = fetchedCourses.value.slice(0, 3).map((c, i) => ({
    type: 'real' as const,
    uuid: c.uuid,
    title: c.title,
    description: c.description,
    thumbnail_url: c.thumbnail_url,
    level: c.level,
    price: `$${parseFloat(c.price).toFixed(0)}`,
    lessons: c.totalLessons,
    duration: formatDuration(c.length_seconds),
    video: courseVideos[i] ?? courseVideos[0],
  }))
  const slots = 3 - real.length
  const placeholders = Array.from({ length: slots }, (_, i) => ({
    type: 'coming-soon' as const,
    id: `cs-${i}`,
    video: courseVideos[(real.length + i) % courseVideos.length],
  }))
  return [...real, ...placeholders]
})

// ── Auth-aware enroll handler ─────────────────────────────────────────────────
const router = useRouter()
function enrollOrLogin(courseUuid: string) {
  router.push(`/courses/${courseUuid}/details`)
}

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
      'Our courses cover strategies for Forex, Commodities, Crypto, and more. The Big Five principles and technical analysis methods we teach are universal, yet modified to apply effectively across all financial markets.',
  },
  {
    question: 'Do I need prior trading experience?',
    answer:
      'Not at all. Our Big Five Foundations course is designed for complete beginners. We start from the very basics and progressively build your knowledge up to advanced strategies.',
  },
  {
    question: 'How long do I have access to the courses?',
    answer:
      "You get lifetime access to all course materials, including future updates. Plus, you'll have access to our free telegram channel and priority customer support for as long as you subscribed the courses.",
  },
  {
    question: 'What makes The Fibonacci Gallery different?',
    answer:
      'We combine cinematic-quality educational content with real-time market analysis. Our unique approach blends technical artistry with mathematical and structural precision, helping you see the markets as living, breathing patterns of opportunity',
  },
]

const navLinks = [
  { label: 'Courses', href: '/explore' },
  { label: 'Markets', href: '/markets' },
  { label: 'Testimonials', href: '/testimonials' },
  { label: 'FAQ', href: '/faq' },
]
</script>

<template>
  <div class="min-h-screen bg-background text-foreground overflow-x-hidden">
    <!-- ═══════ NAVBAR ═══════ -->
    <nav
      id="navbar"
      class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
      :class="[scrolled ? 'bg-background border-b border-border/20 py-3' : 'bg-background py-5']"
    >
      <div class="max-w-7xl mx-auto px-6 md:px-12 lg:px-16 flex items-center justify-between">
        <!-- Brand -->
        <RouterLink
          to="/"
          class="text-xl font-semibold tracking-tight text-foreground hover:text-primary transition-colors"
        >
          The Fibonacci Gallery
        </RouterLink>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-8">
          <RouterLink
            v-for="link in navLinks"
            :key="link.label"
            :to="link.href"
            class="text-sm text-muted-foreground hover:text-foreground transition-colors"
          >
            {{ link.label }}
          </RouterLink>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
          <ThemeToggle />
          <Button class="hidden md:inline-flex" size="sm" as-child>
            <RouterLink to="/login">
              Get Started
              <PhArrowRight class="ml-1 h-4 w-4" />
            </RouterLink>
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
      <div v-if="mobileMenuOpen" class="md:hidden bg-background border-t border-border/20 mt-2">
        <div class="px-6 py-4 flex flex-col gap-3">
          <RouterLink
            v-for="link in navLinks"
            :key="link.label"
            :to="link.href"
            class="text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
            @click="mobileMenuOpen = false"
          >
            {{ link.label }}
          </RouterLink>
          <Button class="mt-2 w-full" size="sm" as-child>
            <RouterLink to="/register" @click="mobileMenuOpen = false">
              Get Started
              <PhArrowRight class="ml-1 h-4 w-4" />
            </RouterLink>
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
            <span class="text-sm text-balack dark:text-muted-foreground"
              >Master the Markets with Precision</span
            >
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
          <p
            class="text-base md:text-lg text-black dark:text-muted-foreground mb-8 max-w-2xl mx-auto"
          >
            Spot hidden market patterns with our Big Five (S.R.S.F.T.R.) trading system. It was
            built by an expert who spent 5+ years studying, backtesting, and developing it. This
            course offers a detailed breakdown for beginners, intermediates, and pros. It covers The
            Big Five strategy, each part individually and combined, with backtesting on 3 months of
            data from the most common market movements: trending and ranging structures each. Plus,
            you'll join 200+ traders who've already changed how they trade.
          </p>
        </FadeIn>

        <!-- CTAs -->
        <FadeIn :delay="1400" :duration="1000">
          <div class="flex flex-wrap gap-4 justify-center">
            <Button size="lg" class="px-8 py-3 rounded-lg font-medium text-base" as-child>
              <RouterLink to="/register">
                Start Learning
                <PhArrowRight class="ml-2 h-4 w-4" />
              </RouterLink>
            </Button>
            <Button
              variant="outline"
              size="lg"
              class="px-8 py-3 rounded-lg font-medium text-base liquid-glass border border-border/20"
              as-child
            >
              <RouterLink to="/explore">
                Explore Courses
                <PhArrowRight class="ml-2 h-4 w-4" />
              </RouterLink>
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
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
          <div
            v-for="(stat, i) in [
              { value: '200+', label: 'Active Traders', icon: PhUsers },
              { value: '20+', label: 'Member Countries', icon: PhGlobe },
              { value: 'Weekly', label: 'Gold Analysis', icon: PhTrendUp },
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
            Our courses combine world-class education, institutional-grade market analysis, and
            real-time tools to give you the ultimate trading advantage.
          </p>
        </div>

        <!-- Feature cards -->
        <div class="grid md:grid-cols-3 gap-6">
          <div
            v-for="(feature, i) in features"
            :key="feature.title"
            class="group relative rounded-2xl overflow-hidden border border-border/30 dark:border-border/20 transition-all duration-500 hover:border-primary/40 min-h-95"
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
              <!-- Light mode: heavy overlay so text stays readable; dark: darker cinematic tone -->
              <div
                class="absolute inset-0 bg-background/60 dark:bg-black/75 transition-opacity duration-500 group-hover:opacity-60 dark:group-hover:opacity-90"
              />
            </div>

            <!-- Card content -->
            <div class="relative z-10 p-8 flex flex-col h-full justify-end">
              <div
                class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary/10 dark:liquid-glass border border-primary/20 dark:border-border/20 mb-4"
              >
                <component :is="feature.icon" class="h-6 w-6 text-primary" />
              </div>
              <h3 class="text-xl font-semibold mb-3 text-foreground">{{ feature.title }}</h3>
              <p class="text-sm text-black dark:text-muted-foreground leading-relaxed">
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
              >Our Purpose</span
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

        <!-- Loading skeleton -->
        <div v-if="coursesLoading" class="grid md:grid-cols-3 gap-6">
          <div
            v-for="n in 3"
            :key="n"
            class="rounded-2xl overflow-hidden border border-border/20 animate-pulse flex flex-col"
          >
            <div class="h-48 bg-muted/40" />
            <div class="p-6 bg-card flex flex-col gap-3">
              <div class="h-4 bg-muted/40 rounded w-3/4" />
              <div class="h-3 bg-muted/30 rounded w-full" />
              <div class="h-3 bg-muted/30 rounded w-5/6" />
              <div class="mt-auto pt-4 border-t border-border/20 flex justify-between items-center">
                <div class="h-6 bg-muted/40 rounded w-16" />
                <div class="h-8 bg-muted/40 rounded w-24" />
              </div>
            </div>
          </div>
        </div>

        <!-- Course cards (real + coming soon) -->
        <div v-else class="grid md:grid-cols-3 gap-6">
          <template
            v-for="(item, i) in displayCourses"
            :key="item.type === 'real' ? item.uuid : item.id"
          >
            <!-- ── Real course card ── -->
            <div
              v-if="item.type === 'real'"
              class="group relative rounded-2xl overflow-hidden border border-border/20 transition-all duration-500 hover:border-primary/30 flex flex-col"
              :style="{
                opacity: visibleSections.has('courses') ? 1 : 0,
                transform: visibleSections.has('courses') ? 'translateY(0)' : 'translateY(30px)',
                transitionDelay: `${i * 150 + 200}ms`,
              }"
            >
              <!-- Thumbnail: backend image or fallback video -->
              <div class="relative h-48 overflow-hidden">
                <img
                  v-if="item.thumbnail_url"
                  :src="item.thumbnail_url"
                  :alt="item.title"
                  class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <video
                  v-else
                  autoplay
                  loop
                  muted
                  playsinline
                  class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                >
                  <source :src="item.video" type="video/mp4" />
                </video>
                <div class="absolute inset-0 bg-background/30 dark:bg-black/30" />
                <!-- Level badge -->
                <div class="absolute top-4 left-4 z-10">
                  <span
                    class="liquid-glass rounded-full px-3 py-1 text-xs font-medium border border-border/20"
                    :class="[
                      item.level === 'Beginner'
                        ? 'text-green-600 dark:text-green-400'
                        : item.level === 'Intermediate'
                          ? 'text-yellow-600 dark:text-yellow-400'
                          : 'text-red-500 dark:text-red-400',
                    ]"
                  >
                    {{ item.level }}
                  </span>
                </div>
              </div>

              <!-- Card body -->
              <div class="flex flex-col flex-1 p-6 bg-card">
                <h3 class="text-lg font-medium mb-2 text-foreground">{{ item.title }}</h3>
                <p class="text-sm text-muted-foreground mb-4 flex-1">{{ item.description }}</p>

                <!-- Meta -->
                <div class="flex items-center gap-4 text-xs text-muted-foreground mb-4">
                  <span class="flex items-center gap-1">
                    <PhVideoCamera class="h-3.5 w-3.5" />
                    {{ item.lessons }} Lessons
                  </span>
                  <span class="flex items-center gap-1">
                    <PhClock class="h-3.5 w-3.5" />
                    {{ item.duration }}
                  </span>
                </div>

                <!-- Price & CTA -->
                <div class="flex items-center justify-between pt-4 border-t border-border/20">
                  <span class="text-2xl font-semibold text-foreground">{{ item.price }}</span>
                  <Button size="sm" class="rounded-lg" @click="enrollOrLogin(item.uuid)">
                    Enroll Now
                    <PhArrowRight class="ml-1 h-3.5 w-3.5" />
                  </Button>
                </div>
              </div>
            </div>

            <!-- ── Coming Soon card ── -->
            <div
              v-else
              class="group relative rounded-2xl overflow-hidden border border-dashed border-border/60 dark:border-border/40 transition-all duration-500 hover:border-primary/40 flex flex-col bg-card shadow-sm"
              :style="{
                opacity: visibleSections.has('courses') ? 1 : 0,
                transform: visibleSections.has('courses') ? 'translateY(0)' : 'translateY(30px)',
                transitionDelay: `${i * 150 + 200}ms`,
              }"
            >
              <!-- Blurred video background -->
              <div class="relative h-48 overflow-hidden bg-muted/40">
                <video
                  autoplay
                  loop
                  muted
                  playsinline
                  class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-25 dark:opacity-30"
                >
                  <source :src="item.video" type="video/mp4" />
                </video>
                <div class="absolute inset-0 bg-background/80 dark:bg-black/80 backdrop-blur-sm" />
                <!-- Coming soon badge -->
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 z-10">
                  <div
                    class="w-12 h-12 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center shadow-xs"
                  >
                    <PhRocket class="h-5 w-5 text-primary" />
                  </div>
                  <span
                    class="bg-background/90 dark:bg-card/90 rounded-full px-3.5 py-1 text-xs font-semibold border border-border/40 text-foreground shadow-xs"
                  >
                    Coming Soon
                  </span>
                </div>
              </div>

              <!-- Card body -->
              <div class="flex flex-col flex-1 p-6 bg-card">
                <div class="flex-1 flex flex-col items-center justify-center text-center py-4">
                  <h3 class="text-lg font-semibold mb-2 text-foreground/80">New Course</h3>
                  <p class="text-sm text-muted-foreground leading-relaxed">
                    We're crafting something incredible. Stay tuned for the next level of trading
                    education.
                  </p>
                </div>

                <!-- Placeholder footer -->
                <div
                  class="flex items-center justify-between pt-4 border-t border-border/40 border-dashed"
                >
                  <Button size="sm" variant="outline" class="rounded-lg font-medium" disabled>
                    Coming Soon
                  </Button>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- View All Courses Button -->
        <div
          class="mt-12 text-center transition-all duration-700"
          :style="{
            opacity: visibleSections.has('courses') ? 1 : 0,
            transform: visibleSections.has('courses') ? 'translateY(0)' : 'translateY(20px)',
            transitionDelay: '600ms',
          }"
        >
          <Button
            variant="outline"
            size="lg"
            class="px-8 py-3 rounded-lg font-medium text-base liquid-glass border border-border/20"
            as-child
          >
            <RouterLink to="/explore">
              View All Courses
              <PhArrowRight class="ml-2 h-4 w-4" />
            </RouterLink>
          </Button>
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
                <PhChartLineUp class="h-4 w-4 text-primary shrink-0" />
                Live price feeds across all major markets
              </li>
              <li class="flex items-center gap-2">
                <PhShieldCheck class="h-4 w-4 text-primary shrink-0" />
                Institutional-grade charting tools
              </li>
              <li class="flex items-center gap-2">
                <PhClock class="h-4 w-4 text-primary shrink-0" />
                24/7 market monitoring and alerts
              </li>
            </ul>
          </div>

          <!-- TradingView Widget -->
          <div class="flex justify-center lg:justify-end w-full overflow-hidden">
            <div
              class="rounded-2xl overflow-hidden border border-border/20 liquid-glass p-1 sm:p-2 w-full max-w-md"
            >
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
      <VideoBackground :src="footerSectionVideo[0] || ''" :overlay-opacity="0.6" />

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
          Access our masterclasses, specialize The Big Five, and join the supportive trading
          community right now.
        </p>

        <div class="flex flex-wrap gap-4 justify-center">
          <Button size="lg" class="px-10 py-4 rounded-lg font-medium text-base" as-child>
            <RouterLink to="/register">
              Start Learning Now
              <PhArrowRight class="ml-2 h-4 w-4" />
            </RouterLink>
          </Button>
          <RouterLink
            to="/explore"
            class="px-10 flex items-center justify-center py-1 rounded-lg font-medium text-base liquid-glass border border-border/20"
          >
            View All Courses
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ═══════ FOOTER ═══════ -->
    <AppFooter />
  </div>
</template>
