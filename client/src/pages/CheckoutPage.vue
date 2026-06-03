<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserCourses } from '@/composables/useUserCourses'
import { Button } from '@/components/ui/button'
import { useLanguageStore } from '@/stores/languageStore'
import { PhArrowLeft, PhSpinner } from '@phosphor-icons/vue'

const route = useRoute()
const router = useRouter()
const languageStore = useLanguageStore()
const lang = computed(() => languageStore.translations)

const courseId = computed(() => Number(route.params.courseId))
const { useCourseDetail } = useUserCourses()
const { data, isLoading, error } = useCourseDetail(courseId.value)

const course = computed(() => data.value?.course)

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
  <div class="min-h-screen bg-background flex flex-col">
    <!-- Header -->
    <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm">
      <div class="container mx-auto px-4 py-6">
        <Button
          @click="router.back()"
          variant="ghost"
          class="hover:bg-secondary/50 text-foreground"
        >
          <PhArrowLeft class="h-4 w-4 mr-2" />
          {{ lang.checkout.backToCourse }}
        </Button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 container mx-auto px-4 py-12 flex items-center justify-center">
      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-20">
        <PhSpinner class="h-8 w-8 animate-spin text-primary" />
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="w-full max-w-md text-center py-10 liquid-glass rounded-2xl border border-border/20 p-8"
      >
        <p class="text-destructive font-semibold">Failed to load course details. Please try again.</p>
      </div>

      <!-- Checkout Card -->
      <div
        v-else-if="course"
        class="w-full max-w-md liquid-glass rounded-2xl p-8 border border-border/20 space-y-6 animate-fade-in-up"
      >
        <div class="text-center">
          <h2 class="text-2xl font-bold text-foreground">
            {{ lang.checkout.title }}
          </h2>
          <div class="h-1 w-12 bg-primary mx-auto mt-3 rounded-full" />
        </div>

        <div class="space-y-4">
          <!-- Course Title -->
          <div class="space-y-1">
            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
              {{ lang.checkout.courseTitle }}
            </span>
            <p class="text-lg font-bold text-foreground line-clamp-2">
              {{ course.title }}
            </p>
          </div>

          <!-- Course Level -->
          <div class="space-y-1">
            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
              {{ lang.checkout.courseLevel }}
            </span>
            <div>
              <span
                :class="[
                  'px-3 py-1 rounded-lg text-sm font-bold border capitalize inline-block',
                  getLevelColor(course.level),
                ]"
              >
                {{ course.level }}
              </span>
            </div>
          </div>

          <!-- Divider -->
          <div class="border-t border-border/20 my-4" />

          <!-- Price -->
          <div class="flex justify-between items-center py-2">
            <span class="text-sm font-semibold text-muted-foreground">
              {{ lang.checkout.price }}
            </span>
            <span class="text-2xl font-bold text-primary">
              ${{ course.price }} USD
            </span>
          </div>
        </div>

        <!-- Buttons and Notes -->
        <div class="space-y-4 pt-4">
          <div class="relative w-full">
            <Button
              disabled
              class="w-full text-base font-semibold py-6 rounded-xl bg-muted text-muted-foreground border border-border cursor-not-allowed flex items-center justify-center gap-2"
            >
              {{ lang.checkout.completePayment }}
              <span class="bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider">
                {{ lang.checkout.comingSoon }}
              </span>
            </Button>
          </div>

          <p class="text-xs text-muted-foreground text-center italic bg-secondary/30 py-3 px-4 rounded-xl border border-border/10">
            {{ lang.checkout.comingSoonNote }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.02) 100%
  );
  backdrop-filter: blur(10px);
}
</style>
