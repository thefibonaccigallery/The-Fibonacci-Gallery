<script setup lang="ts">
import { PhHandWaving } from '@phosphor-icons/vue'

defineProps<{
  studentName: string
  currentDate: string
}>()
</script>

<template>
  <div
    class="animate-slide-in-left flex flex-col md:flex-row md:items-center justify-between gap-4 py-6 border-b border-border/10 mb-8"
  >
    <div>
      <div class="flex items-center gap-2">
        <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-foreground">
          Welcome back, {{ studentName }}
        </h2>
        <PhHandWaving class="h-7 w-7 text-amber-500 animate-float" weight="fill" />
      </div>
      <p class="text-sm text-muted-foreground mt-1">
        Ready to master the markets today? Keep up the great progress.
      </p>
    </div>
    <div
      class="flex items-center gap-2 px-4 py-2 rounded-xl bg-secondary/50 border border-border/50 text-xs md:text-sm font-medium text-muted-foreground self-start md:self-auto"
    >
      <span>{{ currentDate }}</span>
    </div>
  </div>
</template>
