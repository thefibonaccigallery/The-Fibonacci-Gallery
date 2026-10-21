<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { PhX, PhTicket, PhClock, PhArrowRight } from '@phosphor-icons/vue'

interface CouponData {
  uuid: string
  code: string
  discount_percentage: number
  expires_at: string | null
  course: {
    uuid: string
    title: string
  }
}

const props = defineProps<{
  coupon: CouponData
}>()

const router = useRouter()
const isVisible = ref(true)

const expiryDate = computed(() => {
  if (!props.coupon.expires_at) return null
  const date = new Date(props.coupon.expires_at)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
})

const closeBanner = () => {
  isVisible.value = false
}

const goToCourse = () => {
  router.push(`/courses/${props.coupon.course.uuid}/details`)
}

const copyCode = () => {
  navigator.clipboard.writeText(props.coupon.code)
}
</script>

<template>
  <transition
    enter-active-class="transition-all duration-500 ease-out"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition-all duration-300 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <div
      v-if="isVisible"
      class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-2xl"
    >
      <div
        class="coupon-banner rounded-2xl border-2 border-primary/50 p-4 md:p-6 shadow-2xl shadow-primary/30"
      >
        <!-- Close Button -->
        <button
          @click="closeBanner"
          class="absolute top-3 right-3 p-1.5 rounded-lg hover:bg-secondary/50 transition-colors text-muted-foreground hover:text-foreground"
          aria-label="Close banner"
        >
          <PhX class="w-4 h-4" />
        </button>

        <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
          <!-- Icon -->
          <div
            class="w-12 h-12 rounded-xl bg-primary/20 border border-primary/30 flex items-center justify-center shrink-0"
          >
            <PhTicket class="w-6 h-6 text-primary" />
          </div>

          <!-- Content -->
          <div class="flex-1 space-y-2">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="text-lg font-bold text-foreground">
                Save {{ coupon.discount_percentage }}%
              </h3>
              <span class="text-sm text-muted-foreground">on</span>
              <span class="text-sm font-semibold text-primary">{{ coupon.course.title }}</span>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
              <!-- Coupon Code -->
              <button
                @click="copyCode"
                class="group flex items-center gap-2 px-4 py-2 rounded-lg bg-primary/10 border border-primary/30 hover:bg-primary/20 transition-colors"
              >
                <span class="text-sm font-mono font-bold text-primary tracking-wider">
                  {{ coupon.code }}
                </span>
                <span
                  class="text-xs text-muted-foreground group-hover:text-foreground transition-colors"
                >
                  Click to copy
                </span>
              </button>

              <!-- Expiry -->
              <div v-if="expiryDate" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <PhClock class="w-3.5 h-3.5" />
                <span>Expires {{ expiryDate }}</span>
              </div>
            </div>
          </div>

          <!-- CTA Button -->
          <Button
            @click="goToCourse"
            size="sm"
            class="shrink-0 w-full md:w-auto"
          >
            View Course
            <PhArrowRight class="ml-2 w-4 h-4" />
          </Button>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.coupon-banner {
  background: linear-gradient(
    135deg,
    rgba(var(--primary-rgb, 139, 92, 246), 0.25) 0%,
    rgba(var(--background-rgb, 0, 0, 0), 0.95) 50%,
    rgba(var(--primary-rgb, 139, 92, 246), 0.25) 100%
  );
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
}

/* Dark theme adjustment */
@media (prefers-color-scheme: dark) {
  .coupon-banner {
    background: linear-gradient(
      135deg,
      rgba(139, 92, 246, 0.3) 0%,
      rgba(10, 10, 15, 0.96) 50%,
      rgba(139, 92, 246, 0.3) 100%
    );
  }
}

/* Light theme adjustment */
@media (prefers-color-scheme: light) {
  .coupon-banner {
    background: linear-gradient(
      135deg,
      rgba(139, 92, 246, 0.2) 0%,
      rgba(255, 255, 255, 0.95) 50%,
      rgba(139, 92, 246, 0.2) 100%
    );
  }
}
</style>
