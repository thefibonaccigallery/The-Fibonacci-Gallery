<script setup lang="ts">
import { PhCheckCircle, PhClock, PhBookOpen } from '@phosphor-icons/vue'
import { computed } from 'vue'

const props = defineProps<{
  isLoading: boolean
  coursesEnrolled: number
  lessonsCompleted: number
  totalLessons: number
  overallProgress: number
}>()

const stats = computed(() => [
  {
    label: 'Lessons Completed',
    value: String(props.lessonsCompleted),
    sub: props.totalLessons > 0 ? `of ${props.totalLessons} total` : null,
    icon: PhCheckCircle,
    color: 'text-emerald-500',
    bg: 'bg-emerald-500/10 border-emerald-500/20',
  },
  {
    label: 'Overall Progress',
    value: `${props.overallProgress}%`,
    sub: null,
    icon: PhClock,
    color: 'text-cyan-500',
    bg: 'bg-cyan-500/10 border-cyan-500/20',
  },
  {
    label: 'Courses Enrolled',
    value: String(props.coursesEnrolled),
    sub: null,
    icon: PhBookOpen,
    color: 'text-primary',
    bg: 'bg-primary/10 border-primary/20',
  },
])
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <!-- Loading skeleton -->
    <template v-if="isLoading">
      <div
        v-for="i in 3"
        :key="i"
        class="animate-pulse liquid-glass rounded-2xl p-6 flex items-center justify-between border border-border/20"
      >
        <div class="space-y-2">
          <div class="h-3 w-28 rounded bg-muted-foreground/20" />
          <div class="h-8 w-16 rounded bg-muted-foreground/20" />
        </div>
        <div class="w-12 h-12 rounded-xl bg-muted-foreground/20" />
      </div>
    </template>

    <!-- Live stats -->
    <template v-else>
      <div
        v-for="(stat, index) in stats"
        :key="stat.label"
        class="animate-fade-in-up liquid-glass rounded-2xl p-6 flex items-center justify-between border border-border/20 transition-transform duration-300 hover:scale-[1.02]"
        :style="{ animationDelay: `${index * 150}ms` }"
      >
        <div class="space-y-1">
          <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
            {{ stat.label }}
          </p>
          <h3 class="text-3xl font-extrabold text-foreground tracking-tight">
            {{ stat.value }}
          </h3>
          <p v-if="stat.sub" class="text-[11px] text-muted-foreground">{{ stat.sub }}</p>
        </div>
        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center border', stat.bg]">
          <component :is="stat.icon" class="h-6 w-6" :class="stat.color" weight="duotone" />
        </div>
      </div>
    </template>
  </div>
</template>
