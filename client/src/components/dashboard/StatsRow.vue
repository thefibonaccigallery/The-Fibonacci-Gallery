<script setup lang="ts">
import { PhCheckCircle, PhClock, PhBookOpen } from '@phosphor-icons/vue'

const stats = [
  {
    label: 'Lessons Completed',
    value: '12',
    icon: PhCheckCircle,
    color: 'text-emerald-500',
    bg: 'bg-emerald-500/10 border-emerald-500/20',
  },
  {
    label: 'Total Watch Time',
    value: '4h 30m',
    icon: PhClock,
    color: 'text-cyan-500',
    bg: 'bg-cyan-500/10 border-cyan-500/20',
  },
  {
    label: 'Courses Enrolled',
    value: '2',
    icon: PhBookOpen,
    color: 'text-primary',
    bg: 'bg-primary/10 border-primary/20',
  },
]
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
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
      </div>
      <div :class="['w-12 h-12 rounded-xl flex items-center justify-center border', stat.bg]">
        <component :is="stat.icon" class="h-6 w-6" :class="stat.color" weight="duotone" />
      </div>
    </div>
  </div>
</template>
