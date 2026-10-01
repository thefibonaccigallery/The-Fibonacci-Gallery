<script setup lang="ts">
import { PhWarning } from '@phosphor-icons/vue'

interface Props {
  compact?: boolean
}

defineProps<Props>()
</script>

<template>
  <div
    class="rounded-xl border border-amber-500/40 bg-amber-500/10 backdrop-blur-sm shadow-sm transition-all animate-fade-in"
    :class="compact ? 'p-4 text-xs' : 'p-5 text-sm'"
  >
    <div class="flex items-start gap-3.5">
      <div
        class="rounded-lg bg-amber-500/20 text-amber-500 shrink-0 mt-0.5 flex items-center justify-center"
        :class="compact ? 'p-1.5' : 'p-2'"
      >
        <PhWarning :size="compact ? 20 : 24" weight="fill" />
      </div>
      <div class="space-y-2 text-foreground flex-1 min-w-0">
        <h3
          class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5 tracking-wide"
          :class="compact ? 'text-sm' : 'text-base'"
        >
          ⚠️ IMPORTANT NOTICE
        </h3>
        <p class="font-medium text-foreground/95 leading-relaxed">
          Do not start live trading before completing all 5 parts of the Big 5 Forex Course.
        </p>
        <p class="text-muted-foreground leading-relaxed">
          Each part covers essential knowledge you need to understand the Forex market, analyze
          trades, manage risk, and develop a proper trading plan.
        </p>
        <p class="font-semibold text-foreground/95 leading-relaxed">
          Finish the Big 5. Build your knowledge backtest the strategy. Then trade responsibly.
        </p>
      </div>
    </div>
  </div>
</template>
