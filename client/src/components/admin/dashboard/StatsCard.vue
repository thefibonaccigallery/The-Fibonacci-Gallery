<script setup lang="ts">
import { computed, type Component } from 'vue'

interface Props {
  icon: Component
  label: string
  value: number | string
  trend?: string
  formatAsCurrency?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  formatAsCurrency: false,
})

const formattedValue = computed(() => {
  if (props.formatAsCurrency && typeof props.value === 'number') {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(props.value)
  }
  return props.value.toLocaleString()
})
</script>

<template>
  <div class="liquid-glass rounded-xl p-6 animate-fade-in-up">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <p class="text-sm text-muted-foreground mb-2">{{ label }}</p>
        <h3 class="text-3xl font-bold text-foreground mb-1">{{ formattedValue }}</h3>
        <p v-if="trend" class="text-xs text-primary font-medium">{{ trend }}</p>
      </div>
      <div
        class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0"
      >
        <component :is="icon" :size="24" weight="bold" class="text-primary" />
      </div>
    </div>
  </div>
</template>
