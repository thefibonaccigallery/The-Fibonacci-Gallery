<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import { PhArrowRight } from '@phosphor-icons/vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useLanguageStore } from '@/stores/languageStore'

const languageStore = useLanguageStore()
const lang = computed(() => languageStore.translations)

// Dynamic 404 Text Y Scale
const text404Ref = ref<HTMLElement | null>(null)
const scaleY = ref(1.4)

const updateScale = () => {
  if (text404Ref.value) {
    const textHeight =
      text404Ref.value.offsetHeight || text404Ref.value.getBoundingClientRect().height
    if (textHeight > 0) {
      scaleY.value = (window.innerHeight / textHeight) * 1.4
    }
  }
}

onMounted(() => {
  updateScale()
  window.addEventListener('resize', updateScale)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateScale)
})
</script>

<template>
  <DashboardLayout>
    <div
      class="relative w-full min-h-[calc(100vh-180px)] overflow-hidden flex flex-col items-center justify-between font-primary bg-linear-to-b from-background via-background to-card text-foreground select-none py-12 px-4"
    >
      <!-- ═══════ BACKGROUND "404" TEXT EFFECT ═══════ -->
      <div
        class="absolute inset-0 pointer-events-none z-0 opacity-[0.20] flex items-center justify-center overflow-hidden"
        style="
          mask-image: linear-gradient(to bottom, black 60%, transparent 98%);
          -webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 98%);
        "
      >
        <!-- Oval shape over 404 -->
        <div
          class="absolute rounded-full bg-foreground h-[22vh] sm:h-[26vh] md:h-[50vh] w-[clamp(120px,20vw,400px)] transition-transform duration-300 ease-out"
          :style="{ transform: `scale(1, ${scaleY})` }"
        ></div>

        <!-- 404 Centered Text -->
        <span
          ref="text404Ref"
          class="text-foreground font-black leading-none tracking-tighter whitespace-nowrap select-none text-[clamp(200px,48vw,800px)] transition-transform duration-300 ease-out"
          :style="{ transform: `scale(1.15, ${scaleY})` }"
        >
          404
        </span>
      </div>

      <!-- ═══════ BOTTOM CONTENT ═══════ -->
      <div
        class="relative z-30 flex flex-col items-center text-center px-4 max-w-xl mx-auto mt-48 pb-4"
      >
        <h1
          class="text-2xl sm:text-4xl font-extrabold text-foreground tracking-tight mb-3 drop-shadow-md"
        >
          {{ lang.notFound.heading }}
        </h1>
        <p class="text-sm sm:text-base text-muted-foreground/90 max-w-md mb-6 leading-relaxed">
          {{ lang.notFound.subtext }}
        </p>
        <RouterLink
          to="/"
          class="inline-flex items-center gap-2 bg-primary text-primary-foreground font-semibold rounded-full px-6 py-3 text-sm sm:text-base hover:scale-105 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 cursor-pointer"
        >
          <span>{{ lang.notFound.backHome }}</span>
          <PhArrowRight class="w-4 h-4 sm:w-5 sm:h-5" />
        </RouterLink>
      </div>
    </div>
  </DashboardLayout>
</template>
