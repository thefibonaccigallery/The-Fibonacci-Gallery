<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { PhX, PhClock, PhArrowRight, PhCopy, PhCheck, PhSparkle } from '@phosphor-icons/vue'

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
const copied = ref(false)

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

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.coupon.code)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy code:', err)
  }
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
      class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-1.5rem)] max-w-2xl select-none"
    >
      <div
        class="ticket-voucher relative bg-black rounded-xl border border-zinc-700/80 shadow-[0_12px_45px_rgba(0,0,0,0.95),0_0_25px_rgba(74,222,128,0.12)] p-2.5 sm:p-4 overflow-hidden"
      >
        <!-- Vintage Inner Guilloche / Dashed Inset Border -->
        <div
          class="absolute inset-1 sm:inset-1.5 rounded-lg border border-dashed border-zinc-800/80 pointer-events-none z-0"
        ></div>

        <!-- ═════════ WATERMARK ILLUSTRATIONS ═════════ -->
        <!-- Left Watermark: Trading Candlestick & Crescent Moon & Stars -->
        <svg
          class="absolute -bottom-2 left-28 sm:left-36 w-24 sm:w-28 h-24 sm:h-28 text-primary/10 pointer-events-none select-none z-0"
          viewBox="0 0 100 100"
          fill="none"
          stroke="currentColor"
          stroke-width="1.2"
        >
          <!-- Crescent Moon -->
          <path
            d="M 60 15 A 18 18 0 1 1 45 42 A 14 14 0 0 0 60 15 Z"
            fill="currentColor"
            fill-opacity="0.08"
          />
          <!-- Candlestick chart & wicks -->
          <line x1="28" y1="35" x2="28" y2="75" />
          <rect x="24" y="42" width="8" height="20" fill="currentColor" fill-opacity="0.15" />
          <line x1="42" y1="25" x2="42" y2="70" />
          <rect x="38" y="32" width="8" height="26" fill="currentColor" fill-opacity="0.15" />
          <!-- Stars -->
          <path
            d="M 72 20 L 73 23 L 76 24 L 73 25 L 72 28 L 71 25 L 68 24 L 71 23 Z"
            fill="currentColor"
          />
          <path
            d="M 22 28 L 23 30 L 25 31 L 23 32 L 22 34 L 21 32 L 19 31 L 21 30 Z"
            fill="currentColor"
          />
        </svg>

        <!-- Right Watermark: Fibonacci Golden Ratio Spiral & Sparkles -->
        <svg
          class="absolute -bottom-3 -right-3 w-28 sm:w-36 h-28 sm:h-36 text-primary/10 pointer-events-none select-none z-0"
          viewBox="0 0 120 120"
          fill="none"
          stroke="currentColor"
          stroke-width="1.2"
        >
          <!-- Golden Spiral Curve -->
          <path
            d="M 15 105 A 90 90 0 0 1 105 15 A 56 56 0 0 1 49 71 A 34 34 0 0 1 83 105 A 21 21 0 0 1 62 84"
          />
          <!-- Grid Lines -->
          <line x1="15" y1="71" x2="105" y2="71" stroke-dasharray="2 3" opacity="0.3" />
          <line x1="71" y1="15" x2="71" y2="105" stroke-dasharray="2 3" opacity="0.3" />
          <!-- Sparkles -->
          <path
            d="M 88 28 L 89 31 L 92 32 L 89 33 L 88 36 L 87 33 L 84 32 L 87 31 Z"
            fill="currentColor"
          />
        </svg>

        <!-- ═════════ TICKET NOTCHES & PERFORATED TEETH ═════════ -->
        <!-- Center-Left Semicircle Notch -->
        <div class="ticket-notch -left-3.5 top-1/2 -translate-y-1/2" aria-hidden="true"></div>

        <!-- Center-Right Semicircle Notch -->
        <div class="ticket-notch -right-3.5 top-1/2 -translate-y-1/2" aria-hidden="true"></div>

        <!-- Perforation Top Tear Notch -->
        <div
          class="ticket-notch-sm -top-2.5 left-[105px] sm:left-[145px] -translate-x-1/2"
          aria-hidden="true"
        ></div>

        <!-- Perforation Bottom Tear Notch -->
        <div
          class="ticket-notch-sm -bottom-2.5 left-[105px] sm:left-[145px] -translate-x-1/2"
          aria-hidden="true"
        ></div>

        <!-- Left Serrated Teeth Pattern -->
        <div
          class="absolute -left-1 top-2.5 bottom-2.5 w-1 flex flex-col justify-between overflow-hidden pointer-events-none z-10"
          aria-hidden="true"
        >
          <div
            v-for="i in 10"
            :key="'l-' + i"
            class="w-1.5 h-1.5 rounded-full bg-[#0a0a0a] border border-zinc-700/60 -translate-x-0.5"
          ></div>
        </div>

        <!-- Right Serrated Teeth Pattern -->
        <div
          class="absolute -right-1 top-2.5 bottom-2.5 w-1 flex flex-col justify-between overflow-hidden pointer-events-none z-10"
          aria-hidden="true"
        >
          <div
            v-for="i in 10"
            :key="'r-' + i"
            class="w-1.5 h-1.5 rounded-full bg-[#0a0a0a] border border-zinc-700/60 translate-x-0.5"
          ></div>
        </div>

        <!-- Close Button -->
        <button
          @click="closeBanner"
          class="absolute top-2 right-2 p-1.5 sm:p-2 rounded-full text-zinc-400 hover:text-white hover:bg-zinc-800/90 transition-colors z-30 cursor-pointer"
          aria-label="Close banner"
        >
          <PhX class="w-5 h-5 sm:w-5.5 sm:h-5.5" weight="bold" />
        </button>

        <!-- ═════════ TICKET CONTENT ═════════ -->
        <div class="relative z-10 flex items-stretch">
          <!-- ─── LEFT STUB ─── -->
          <div
            class="w-[95px] sm:w-[135px] shrink-0 flex items-center justify-between pr-2.5 sm:pr-3 pl-1 sm:pl-2"
          >
            <!-- Vertical "DISCOUNT" Label -->
            <span
              class="text-[7.5px] sm:text-[9px] font-bold tracking-[0.25em] text-zinc-400 uppercase [writing-mode:vertical-rl] rotate-180 font-mono select-none"
            >
              DISCOUNT
            </span>

            <!-- Center Discount Percentage -->
            <div class="flex flex-col items-center justify-center">
              <span
                class="font-serif font-black text-2xl sm:text-4xl text-white tracking-tight leading-none"
              >
                {{ coupon.discount_percentage }}%
              </span>
              <span
                class="text-[8px] sm:text-[9px] font-mono font-bold text-primary tracking-widest uppercase mt-0.5"
              >
                OFF
              </span>
            </div>

            <!-- Vertical Barcode (Horizontal Bars) -->
            <div
              class="flex flex-col justify-between h-16 sm:h-20 w-3.5 sm:w-4.5 py-0.5 opacity-80 hover:opacity-100 transition-opacity"
              title="Official Fibonacci Voucher"
            >
              <svg class="w-full h-full text-zinc-200" viewBox="0 0 20 100" fill="currentColor">
                <rect y="0" width="20" height="3" />
                <rect y="5" width="20" height="1.5" />
                <rect y="8" width="20" height="4" />
                <rect y="14" width="20" height="2" />
                <rect y="18" width="20" height="1" />
                <rect y="21" width="20" height="4.5" />
                <rect y="28" width="20" height="2" />
                <rect y="32" width="20" height="3.5" />
                <rect y="37" width="20" height="1" />
                <rect y="40" width="20" height="4" />
                <rect y="46" width="20" height="2" />
                <rect y="50" width="20" height="1.5" />
                <rect y="53" width="20" height="3" />
                <rect y="58" width="20" height="4.5" />
                <rect y="65" width="20" height="2" />
                <rect y="69" width="20" height="3.5" />
                <rect y="74" width="20" height="1" />
                <rect y="77" width="20" height="3" />
                <rect y="82" width="20" height="2" />
                <rect y="86" width="20" height="4.5" />
                <rect y="92" width="20" height="2" />
                <rect y="96" width="20" height="3" />
              </svg>
            </div>
          </div>

          <!-- ─── PERFORATION DIVIDER ─── -->
          <div class="relative flex items-center shrink-0">
            <div class="w-px h-full border-r-2 border-dashed border-zinc-700/80 my-1"></div>
          </div>

          <!-- ─── RIGHT MAIN VOUCHER BODY ─── -->
          <div
            class="flex-1 min-w-0 pl-3 sm:pl-4 pr-1 sm:pr-2 flex flex-col justify-between py-0.5"
          >
            <!-- Top Brand Badge -->
            <div class="flex items-center justify-center gap-1.5 pt-0.5">
              <PhSparkle class="w-3 h-3 text-primary animate-pulse" weight="fill" />
              <span
                class="text-[8px] sm:text-[9.5px] font-mono uppercase tracking-[0.22em] text-zinc-400 font-semibold"
              >
                THE FIBONACCI GALLERY
              </span>
            </div>

            <!-- Headline & Script Subtitle -->
            <div class="text-center my-0.5 sm:my-1">
              <h2
                class="font-playfair font-black text-lg sm:text-2xl md:text-3xl text-white uppercase tracking-[0.16em] sm:tracking-[0.2em] leading-tight"
              >
                DISCOUNT COUPON
              </h2>
              <div
                class="font-script text-base sm:text-xl text-primary font-normal tracking-wide -mt-0.5 sm:-mt-1"
              >
                Special Enrollment
              </div>
              <p
                class="text-[10.5px] sm:text-xs text-zinc-300 font-medium truncate max-w-xs sm:max-w-md mx-auto mt-0.5"
                :title="coupon.course.title"
              >
                {{ coupon.course.title }}
              </p>
            </div>

            <!-- Bottom Row: Date, Code, CTA -->
            <div
              class="flex flex-wrap items-center justify-between gap-1.5 sm:gap-2 pt-1 border-t border-zinc-800/80"
            >
              <!-- Valid Until Date -->
              <div
                class="text-[9px] sm:text-[10.5px] font-mono text-zinc-400 flex items-center gap-1 shrink-0"
              >
                <PhClock class="w-3 h-3 text-zinc-500" />
                <span>
                  Valid Until:
                  <strong class="text-zinc-200 font-semibold">
                    {{ expiryDate || 'Limited Time' }}
                  </strong>
                </span>
              </div>

              <!-- Action Controls -->
              <div class="flex items-center gap-1.5 sm:gap-2 ml-auto">
                <!-- Coupon Code with Copy -->
                <button
                  @click="copyCode"
                  class="group flex items-center gap-1 px-2 py-0.5 sm:py-1 rounded bg-zinc-950/90 border border-dashed border-primary/50 hover:border-primary text-primary transition-all text-[10px] sm:text-xs font-mono font-bold"
                  title="Click to copy coupon code"
                >
                  <span class="tracking-wider">{{ coupon.code }}</span>
                  <PhCheck
                    v-if="copied"
                    class="w-3 h-3 text-primary animate-in fade-in"
                    weight="bold"
                  />
                  <PhCopy
                    v-else
                    class="w-3 h-3 text-primary/70 group-hover:text-primary transition-colors"
                  />
                  <span
                    class="text-[8.5px] sm:text-[9.5px] text-zinc-400 font-sans font-normal ml-0.5"
                  >
                    {{ copied ? 'Copied!' : 'Copy' }}
                  </span>
                </button>

                <!-- Claim / View Course Button -->
                <Button
                  @click="goToCourse"
                  size="sm"
                  class="h-6 sm:h-7 px-2.5 sm:px-3 text-[10px] sm:text-xs font-bold bg-primary text-black hover:bg-primary/90 rounded shadow cursor-pointer shrink-0"
                >
                  <span>View Course</span>
                  <PhArrowRight class="ml-1 w-3 h-3" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Alex+Brush&family=Playfair+Display:ital,wght@0,700;0,900;1,600&display=swap');

.font-playfair {
  font-family: 'Playfair Display', Georgia, serif;
}

.font-script {
  font-family: 'Alex Brush', cursive, serif;
}

.ticket-voucher {
  background-color: #000000;
}

.ticket-notch {
  position: absolute;
  width: 22px;
  height: 22px;
  border-radius: 9999px;
  background-color: #0a0a0a;
  border: 1px solid rgba(113, 113, 122, 0.7);
  box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.95);
  z-index: 20;
}

.ticket-notch-sm {
  position: absolute;
  width: 16px;
  height: 16px;
  border-radius: 9999px;
  background-color: #0a0a0a;
  border: 1px solid rgba(113, 113, 122, 0.7);
  box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.95);
  z-index: 20;
}
</style>
