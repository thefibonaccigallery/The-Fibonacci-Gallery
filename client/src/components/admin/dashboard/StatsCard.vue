<script setup lang="ts">
import { computed, type Component } from 'vue'

interface Props {
  icon: Component
  label: string
  value: number | string
  trend?: string
  formatAsCurrency?: boolean
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  formatAsCurrency: false,
  loading: false,
})

const formattedValue = computed(() => {
  if (props.formatAsCurrency) {
    const num = typeof props.value === 'string' ? parseFloat(props.value) || 0 : props.value
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    }).format(num)
  }
  if (typeof props.value === 'number') {
    return props.value.toLocaleString()
  }
  return props.value
})
</script>

<template>
  <div class="liquid-glass rounded-xl p-6 animate-fade-in-up">
    <div v-if="loading" class="animate-pulse flex items-start justify-between">
      <div class="flex-1 space-y-2">
        <div class="h-4 w-24 bg-muted rounded"></div>
        <div class="h-8 w-28 bg-muted rounded"></div>
        <div class="h-3 w-20 bg-muted rounded"></div>
      </div>
      <div class="w-12 h-12 rounded-lg bg-muted flex-shrink-0"></div>
    </div>
    <div v-else class="flex items-start justify-between">
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
