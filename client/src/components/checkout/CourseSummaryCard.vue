<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { PhSpinner, PhTag, PhCheckCircle, PhXCircle, PhX } from '@phosphor-icons/vue'
import axiosInstance from '@/api/axiosInstance'

interface Course {
  uuid: string
  title: string
  level: string
  price: number | string
  thumbnail_url: string | null
}

interface CouponResult {
  valid: boolean
  message?: string
  code?: string
  discount_percentage?: number
  discount_amount?: number
  final_price?: number
  is_free?: boolean
}

const props = defineProps<{
  course: Course
  loading?: boolean
}>()

const emit = defineEmits<{
  next: [couponCode: string | null, isFree: boolean]
}>()

const couponInput = ref('')
const isValidating = ref(false)
const couponResult = ref<CouponResult | null>(null)

const originalPrice = computed(() => Number(props.course.price))
const finalPrice = computed(() =>
  couponResult.value?.valid ? (couponResult.value.final_price ?? originalPrice.value) : originalPrice.value,
)
const isFree = computed(() => couponResult.value?.valid === true && couponResult.value.is_free === true)

const getLevelColor = (level: string | null) => {
  if (!level) return 'text-muted-foreground border-border/30 bg-secondary/50'
  switch (level.toLowerCase()) {
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

const handleApply = async () => {
  const code = couponInput.value.trim()
  if (!code) return

  isValidating.value = true
  couponResult.value = null

  try {
    const response = await axiosInstance.post('/coupons/validate', {
      code,
      course_uuid: props.course.uuid,
    })
    couponResult.value = response.data
  } catch (error: any) {
    couponResult.value = {
      valid: false,
      message: error.response?.data?.message || 'Invalid coupon code',
    }
  } finally {
    isValidating.value = false
  }
}

const handleClear = () => {
  couponInput.value = ''
  couponResult.value = null
}

const handlePay = () => {
  const appliedCode = couponResult.value?.valid ? (couponResult.value.code ?? couponInput.value.trim()) : null
  const courseIsFree = originalPrice.value === 0 || isFree.value
  emit('next', appliedCode, courseIsFree)
}
</script>

<template>
  <div class="animate-fade-in-up liquid-glass rounded-2xl p-6 border border-border/20">
    <h2 class="text-xl font-bold text-foreground mb-6">Course Summary</h2>

    <div class="space-y-6">
      <!-- Course Thumbnail -->
      <div
        class="relative w-full h-48 rounded-xl overflow-hidden bg-zinc-950 border border-primary/10 flex items-center justify-center"
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
          <circle cx="50" cy="63.3" r="26.7" stroke="var(--primary)" stroke-width="0.5" fill="none" />
        </svg>
      </div>

      <!-- Course Info -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold text-foreground">{{ course.title }}</h3>
          <div
            v-if="course.level"
            :class="['px-3 py-1 rounded-lg text-xs font-bold border capitalize', getLevelColor(course.level)]"
          >
            {{ course.level }}
          </div>
        </div>

        <!-- Price block -->
        <div class="pt-4 border-t border-border/20 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Course Price</span>
            <span
              :class="[
                'text-lg font-semibold',
                couponResult?.valid ? 'line-through text-muted-foreground' : 'text-foreground',
              ]"
            >
              ${{ originalPrice.toFixed(2) }}
            </span>
          </div>

          <!-- Discount row — shown only when coupon is valid -->
          <div v-if="couponResult?.valid" class="flex items-center justify-between">
            <span class="text-sm text-green-500 flex items-center gap-1">
              <PhTag :size="14" />
              {{ couponResult.discount_percentage }}% discount
            </span>
            <span class="text-sm text-green-500 font-medium">
              &minus;${{ couponResult.discount_amount?.toFixed(2) }}
            </span>
          </div>

          <!-- Final price -->
          <div v-if="couponResult?.valid" class="flex items-center justify-between pt-2 border-t border-border/20">
            <span class="text-sm font-semibold text-foreground">Total</span>
            <span class="text-2xl font-bold text-primary">
              {{ isFree ? 'Free' : `$${finalPrice.toFixed(2)}` }}
            </span>
          </div>
          <div v-else class="flex items-center justify-between">
            <span class="text-sm font-semibold text-foreground">Total</span>
            <span class="text-2xl font-bold text-primary">
              ${{ originalPrice.toFixed(2) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Coupon Section -->
      <div class="space-y-2">
        <p class="text-sm font-medium text-foreground flex items-center gap-2">
          <PhTag :size="16" class="text-muted-foreground" />
          Coupon Code
        </p>

        <!-- Applied coupon badge -->
        <div
          v-if="couponResult?.valid"
          class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-green-500/10 border border-green-500/30"
        >
          <div class="flex items-center gap-2">
            <PhCheckCircle :size="16" class="text-green-500" weight="fill" />
            <span class="font-mono text-sm font-semibold text-green-600 tracking-wider">
              {{ couponResult.code }}
            </span>
            <span class="text-xs text-green-500">applied</span>
          </div>
          <button
            type="button"
            @click="handleClear"
            class="text-muted-foreground hover:text-foreground transition-colors"
            title="Remove coupon"
          >
            <PhX :size="16" />
          </button>
        </div>

        <!-- Coupon input -->
        <div v-else class="flex gap-2">
          <Input
            v-model="couponInput"
            placeholder="Enter coupon code"
            class="uppercase tracking-wider flex-1"
            @keyup.enter="handleApply"
          />
          <Button
            variant="outline"
            size="sm"
            class="shrink-0 px-4"
            :disabled="isValidating || !couponInput.trim()"
            @click="handleApply"
          >
            <PhSpinner v-if="isValidating" :size="14" class="animate-spin" />
            <span v-else>Apply</span>
          </Button>
        </div>

        <!-- Error message -->
        <div
          v-if="couponResult && !couponResult.valid"
          class="flex items-center gap-2 text-xs text-destructive"
        >
          <PhXCircle :size="14" weight="fill" />
          {{ couponResult.message }}
        </div>
      </div>

      <!-- Pay / Enroll Button -->
      <Button
        @click="handlePay"
        :disabled="loading"
        class="w-full text-sm font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground flex items-center justify-center gap-2"
      >
        <PhSpinner v-if="loading" class="h-4 w-4 animate-spin" />
        <span v-if="isFree">Enroll for Free</span>
        <span v-else-if="couponResult?.valid">
          Pay ${{ finalPrice.toFixed(2) }} with USDT 
        </span>
        <span v-else>Pay with USDT </span>
      </Button>
    </div>
  </div>
</template>
