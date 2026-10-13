<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserCourses } from '@/composables/useUserCourses'
import { useAuthStore } from '@/stores/auth'
import { useSeoMeta } from '@/composables/useSeoMeta'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import {
  PhSpinner,
  PhArrowLeft,
  PhBookOpen,
  PhClock,
  PhPlay,
  PhCheckCircle,
  PhLock,
} from '@phosphor-icons/vue'

const route = useRoute()
const router = useRouter()
const { useCourseDetail } = useUserCourses()
const authStore = useAuthStore()
const { setSeoMeta, generateCourseSchema } = useSeoMeta()

const courseUuid = computed(() => route.params.uuid as string)

const handleEnrollClick = () => {
  if (authStore.isAuthenticated) {
    router.push(`/checkout/${courseUuid.value}`)
  } else {
    router.push({
      path: '/login',
      query: { redirect: `/courses/${courseUuid.value}/details` }
    })
  }
}
const { data, isLoading, error } = useCourseDetail(courseUuid.value)

const course = computed(() => data.value?.course)

const displayedChapters = computed(() => {
  if (!course.value?.chapters) return []
  if (course.value.is_enrolled) return course.value.chapters
  return course.value.chapters.slice(0, 10)
})

const hiddenChaptersCount = computed(() => {
  if (!course.value?.chapters || course.value.is_enrolled) return 0
  return Math.max(0, course.value.chapters.length - 10)
})

watch(
  course,
  (c) => {
    if (c) {
      setSeoMeta({
        title: `${c.title} | Masterclass`,
        description: c.description || undefined,
        ogImage: c.thumbnail_url || undefined,
        canonicalUrl: `/courses/${c.uuid}/details`,
        jsonLd: generateCourseSchema({
          uuid: c.uuid,
          title: c.title,
          description: c.description,
          thumbnail_url: c.thumbnail_url,
          price: Number(c.price) || 0,
          level: c.level,
          published_at: c.published_at,
          totalLessons: c.totalLessons,
          length_seconds: c.length_seconds,
        }),
      })
    }
  },
  { immediate: true },
)

const formatDuration = (seconds: number) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
}

const goToCoursePlayer = () => {
  router.push(`/courses/${courseUuid.value}/player`)
}

const getLevelColor = (level: string) => {
  switch (level) {
    case 'beginner':
      return 'text-green-500 border-green-500/30 bg-green-500/10'
    case 'intermediate':
      return 'text-yellow-500 border-yellow-500/30 bg-yellow-500/10'
    case 'advanced':
      return 'text-red-500 border-red-500/30 bg-red-500/10'
    default:
      return 'text-muted-foreground border-border/30 bg-secondary/50'
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background">
      <!-- Header -->
      <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-6">
          <Button @click="router.back()" variant="ghost" class="hover:bg-secondary/50">
            <PhArrowLeft class="h-4 w-4 mr-2" />
            Back
          </Button>
        </div>
      </div>

      <!-- Content -->
      <div class="container mx-auto px-4 py-8 pb-24 md:pb-8">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-20">
          <PhSpinner class="h-8 w-8 animate-spin text-primary" />
        </div>

        <!-- Error State -->
        <div
          v-else-if="error"
          class="text-center py-20 liquid-glass rounded-2xl border border-border/20 p-8"
        >
          <p class="text-destructive">Failed to load course details. Please try again.</p>
        </div>

        <!-- Course Details -->
        <div v-else-if="course" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Course Header -->
            <div class="liquid-glass rounded-2xl p-8 border border-border/20">
              <div class="flex items-start gap-4 mb-4">
                <div
                  :class="[
                    'px-3 py-1 rounded-lg text-sm font-bold border capitalize',
                    getLevelColor(course.level),
                  ]"
                >
                  {{ course.level }}
                </div>
                <div
                  v-if="course.is_enrolled"
                  class="px-3 py-1 rounded-lg text-sm font-bold border bg-primary/10 text-primary border-primary/30"
                >
                  <PhCheckCircle class="h-4 w-4 inline mr-1" weight="fill" />
                  Enrolled
                </div>
              </div>

              <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-4">
                {{ course.title }}
              </h1>

              <p class="text-muted-foreground text-base leading-relaxed mb-6">
                {{ course.description || 'No description available' }}
              </p>

              <!-- Course Stats -->
              <div class="flex flex-wrap gap-6 text-sm">
                <div class="flex items-center gap-2">
                  <PhBookOpen class="h-5 w-5 text-primary" />
                  <span class="text-foreground font-medium">{{ course.totalLessons }} lessons</span>
                </div>
                <div class="flex items-center gap-2">
                  <PhClock class="h-5 w-5 text-primary" />
                  <span class="text-foreground font-medium">
                    {{ formatDuration(course.length_seconds) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Course Curriculum -->
            <div class="liquid-glass rounded-2xl p-8 border border-border/20">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-foreground">Course Curriculum</h2>
                <span
                  v-if="!course.is_enrolled && hiddenChaptersCount > 0"
                  class="text-xs text-muted-foreground font-medium bg-secondary/60 px-3 py-1 rounded-full border border-border/30"
                >
                  Showing 10 of {{ course.chapters.length }} chapters
                </span>
              </div>

              <Accordion type="multiple" class="space-y-4">
                <AccordionItem
                  v-for="(chapter, index) in displayedChapters"
                  :key="chapter.uuid"
                  :value="`chapter-${chapter.uuid}`"
                  class="border border-border/20 rounded-xl overflow-hidden bg-background/50"
                >
                  <AccordionTrigger class="px-6 py-4 hover:bg-secondary/30 transition-colors">
                    <div class="flex items-center gap-4 text-left">
                      <div
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-sm"
                      >
                        {{ index + 1 }}
                      </div>
                      <div class="flex-1">
                        <h3 class="font-semibold text-foreground">{{ chapter.title }}</h3>
                        <p class="text-xs text-muted-foreground mt-1">
                          {{ chapter.video_count }} videos
                          <span v-if="chapter.level" class="capitalize">
                            • {{ chapter.level }}
                          </span>
                        </p>
                      </div>
                    </div>
                  </AccordionTrigger>
                  <AccordionContent class="px-6 py-4 bg-secondary/20">
                    <p class="text-sm text-muted-foreground">
                      This chapter contains {{ chapter.video_count }} video{{
                        chapter.video_count !== 1 ? 's' : ''
                      }}
                      covering essential topics.
                    </p>
                  </AccordionContent>
                </AccordionItem>
              </Accordion>

              <!-- Locked Chapters Callout -->
              <div
                v-if="!course.is_enrolled && hiddenChaptersCount > 0"
                class="mt-6 p-6 rounded-xl border border-primary/20 bg-primary/5 text-center space-y-4 backdrop-blur-sm"
              >
                <div
                  class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary"
                >
                  <PhLock :size="24" weight="bold" />
                </div>
                <div class="space-y-1">
                  <h3 class="text-lg font-bold text-foreground">
                    + {{ hiddenChaptersCount }} More Chapters Available
                  </h3>
                  <p class="text-sm text-muted-foreground max-w-md mx-auto">
                    Enroll now to unlock the complete curriculum, advanced strategies, and all remaining lessons.
                  </p>
                </div>
                <Button
                  @click="handleEnrollClick"
                  class="font-semibold px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground shadow-lg shadow-primary/20"
                >
                  Enroll to explore more
                </Button>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <div class="liquid-glass rounded-2xl p-6 border border-border/20 sticky top-0">
              <!-- Thumbnail -->
              <div
                class="relative w-full h-48 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center mb-6"
              >
                <img
                  v-if="course.thumbnail_url"
                  :src="course.thumbnail_url"
                  :alt="course.title"
                  class="w-full h-full object-cover"
                />
                <svg
                  v-else
                  class="absolute inset-0 w-full h-full opacity-30"
                  viewBox="0 0 100 100"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle
                    cx="50"
                    cy="50"
                    r="40"
                    stroke="rgba(34, 197, 94, 0.1)"
                    stroke-width="0.5"
                    stroke-dasharray="2 2"
                  />
                  <polygon
                    points="50,10 90,90 10,90"
                    stroke="var(--primary)"
                    stroke-width="0.5"
                    fill="none"
                  />
                </svg>
              </div>

              <!-- Price -->
              <div class="text-center mb-6">
                <div class="text-4xl font-bold text-primary mb-2">
                  {{ course.price === 0 ? 'Free' : `$${course.price}` }}
                </div>
                <p class="text-sm text-muted-foreground">One-time payment</p>
              </div>

              <!-- Action Button -->
              <div class="space-y-3">
                <Button
                  v-if="!course.is_enrolled"
                  @click="handleEnrollClick"
                  class="w-full text-base font-semibold py-6 rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground"
                >
                  Enroll Now
                </Button>

                <Button
                  v-else
                  @click="goToCoursePlayer"
                  class="w-full text-base font-semibold py-6 rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground"
                >
                  <PhPlay class="h-5 w-5 mr-2" weight="fill" />
                  Go to Course
                </Button>
              </div>

              <!-- What's Included -->
              <div
                v-if="course.what_is_included && course.what_is_included.length > 0"
                class="mt-8 pt-6 border-t border-border/20"
              >
                <h3 class="font-semibold text-foreground mb-4">What's included:</h3>
                <ul class="space-y-3 text-sm text-muted-foreground">
                  <li class="flex items-start gap-2">
                    <PhCheckCircle class="h-5 w-5 text-primary shrink-0 mt-0.5" weight="fill" />
                    <span>{{ course.totalLessons }} video lessons</span>
                  </li>
                  <li
                    v-for="(item, index) in course.what_is_included"
                    :key="index"
                    class="flex items-start gap-2"
                  >
                    <PhCheckCircle class="h-5 w-5 text-primary shrink-0 mt-0.5" weight="fill" />
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
  backdrop-filter: blur(10px);
}
</style>
