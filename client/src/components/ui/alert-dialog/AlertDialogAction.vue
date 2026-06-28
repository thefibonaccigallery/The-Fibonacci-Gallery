<script setup lang="ts">
import { AlertDialogAction } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<{ class?: string; disabled?: boolean }>()
</script>

<template>
  <AlertDialogAction
    :class="
      cn(
        'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2',
        props.class,
      )
    "
    :disabled="props.disabled"
  >
    <slot />
  </AlertDialogAction>
</template>
