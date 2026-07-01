<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { PhCheckCircle, PhPlay, PhSpinner } from '@phosphor-icons/vue'
import axiosInstance from '@/api/axiosInstance'

const router = useRouter()
const route = useRoute()

// Get course UUID from query parameter (passed from payment flow)
const courseUuid = computed(() => (route.query.courseUuid as string) || '')
const courseTitle = ref<string>('')
const isLoadingCourse = ref(false)

// Fetch course details to get the title
onMounted(async () => {
  if (courseUuid.value) {
    isLoadingCourse.value = true
    try {
      const response = await axiosInstance.get(`/courses/${courseUuid.value}`)
      courseTitle.value = response.data.course?.title || 'the course'
    } catch (error) {
      console.error('Failed to fetch course details:', error)
      courseTitle.value = 'the course'
    } finally {
      isLoadingCourse.value = false
    }
  }
})

const handleStartLearning = () => {
  if (courseUuid.value) {
    router.push(`/courses/${courseUuid.value}/player`)
  } else {
    // Fallback to dashboard if no course UUID
    router.push('/dashboard')
  }
}

const handleBackToDashboard = () => {
  router.push('/dashboard')
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background flex items-center justify-center p-4">
      <div class="w-full max-w-lg">
        <div
          class="animate-fade-in-up liquid-glass rounded-2xl p-8 md:p-12 border border-border/20 text-center"
        >
          <!-- Success Icon -->
          <div class="flex justify-center mb-6">
            <div
              class="w-24 h-24 rounded-full bg-green-500/20 border-4 border-green-500 flex items-center justify-center animate-scale-in"
            >
              <PhCheckCircle class="h-16 w-16 text-green-500" weight="fill" />
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-3">Payment Successful!</h1>

          <!-- Subtitle -->
          <p class="text-muted-foreground text-base mb-2">
            Congratulations! You are now enrolled in the course.
          </p>
          <p class="text-sm text-muted-foreground mb-8">Start your learning journey today.</p>

          <!-- Course Title -->
          <div class="mb-8 p-4 bg-secondary/50 rounded-xl border border-border/40">
            <p class="text-xs text-muted-foreground mb-1">You're enrolled in</p>
            <div v-if="isLoadingCourse" class="flex items-center justify-center py-2">
              <PhSpinner class="h-5 w-5 animate-spin text-primary" />
            </div>
            <p v-else class="text-lg font-bold text-foreground">{{ courseTitle }}</p>
          </div>

          <!-- Actions -->
          <div class="space-y-3">
            <Button
              @click="handleStartLearning"
              class="w-full text-base font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground py-6 flex items-center justify-center gap-2"
            >
              <PhPlay class="h-5 w-5" weight="fill" />
              Start Learning
            </Button>

            <Button
              @click="handleBackToDashboard"
              variant="outline"
              class="w-full text-sm font-semibold rounded-xl border-border/40 hover:bg-secondary/50"
            >
              Back to Dashboard
            </Button>
          </div>

          <!-- Footer Note -->
          <p class="text-xs text-muted-foreground mt-8">
            A confirmation email has been sent to your registered email address.
          </p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
@keyframes scale-in {
  from {
    transform: scale(0);
  }
  to {
    transform: scale(1);
  }
}

.animate-scale-in {
  animation: scale-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
</style>
