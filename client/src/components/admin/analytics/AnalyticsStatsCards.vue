<script setup lang="ts">
import { computed } from 'vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { useLanguageStore } from '@/stores/languageStore'
import {
  PhUsers,
  PhFileText,
  PhChartLineUp,
  PhDevices,
  PhArrowSquareOut,
  PhArrowsClockwise,
} from '@phosphor-icons/vue'
import type {
  AnalyticsRange,
  AnalyticsRangeOption,
  DeviceSplitItem,
} from '@/types/analyticsType'

interface Props {
  visitors: number
  pageViews: number
  deviceSplit: DeviceSplitItem[]
  currentRange: AnalyticsRange
  rangeOptions: AnalyticsRangeOption[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  'update:range': [range: AnalyticsRange]
  'open-ga': []
  refresh: []
}>()

const langStore = useLanguageStore()
const t = computed(() => langStore.translations.adminAnalytics)

const avgPagesPerVisitor = computed(() => {
  if (props.visitors <= 0) return '0.0'
  return (props.pageViews / props.visitors).toFixed(1)
})

const primaryDeviceLabel = computed(() => {
  if (!props.deviceSplit || props.deviceSplit.length === 0) return '-'
  const dominant = props.deviceSplit.reduce((prev, curr) =>
    curr.percentage > prev.percentage ? curr : prev
  )
  const deviceName =
    dominant.device.charAt(0).toUpperCase() + dominant.device.slice(1)
  return `${deviceName} (${dominant.percentage}%)`
})

const formattedVisitors = computed(() => props.visitors.toLocaleString())
const formattedPageViews = computed(() => props.pageViews.toLocaleString())
</script>

<template>
  <div class="space-y-4">
    <!-- Header with Range Selector & GA4 Link -->
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
    >
      <div>
        <h2 class="text-xl font-bold text-foreground">
          {{ t.sectionTitle }}
        </h2>
        <p class="text-xs text-muted-foreground mt-0.5">
          {{ t.sectionSubtitle }}
        </p>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <!-- Range Selector -->
        <div class="w-36">
          <Select
            :model-value="currentRange"
            @update:model-value="(val) => emit('update:range', val as AnalyticsRange)"
          >
            <SelectTrigger
              class="h-9 bg-background/50 border-border/50 text-xs font-medium"
            >
              <SelectValue :placeholder="t.range.label" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="opt in rangeOptions"
                :key="opt.value"
                :value="opt.value"
                class="text-xs"
              >
                {{ opt.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Refresh Button -->
        <Button
          variant="outline"
          size="sm"
          class="h-9 px-3 text-xs border-border/50 text-foreground hover:bg-accent/40"
          :disabled="loading"
          @click="emit('refresh')"
        >
          <PhArrowsClockwise
            :size="14"
            :class="{ 'animate-spin': loading }"
          />
        </Button>

        <!-- Open Full GA4 Dashboard -->
        <Button
          variant="outline"
          size="sm"
          class="h-9 gap-1.5 text-xs border-border/50 text-foreground hover:bg-accent/40"
          @click="emit('open-ga')"
        >
          <PhArrowSquareOut :size="14" class="text-primary" />
          <span>{{ t.openGaDashboard }}</span>
        </Button>
      </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Visitors Card -->
      <div
        class="liquid-glass rounded-xl p-5 animate-fade-in-up border border-border/10"
      >
        <div v-if="loading" class="space-y-3">
          <div class="flex items-center justify-between">
            <Skeleton class="h-3 w-20 bg-muted" />
            <Skeleton class="h-8 w-8 rounded-lg bg-muted" />
          </div>
          <Skeleton class="h-7 w-28 bg-muted" />
          <Skeleton class="h-3 w-36 bg-muted" />
        </div>
        <div v-else class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-muted-foreground mb-1">
              {{ t.stats.visitors }}
            </p>
            <h3 class="text-2xl font-bold text-foreground truncate">
              {{ formattedVisitors }}
            </h3>
            <p class="text-[11px] text-primary font-medium mt-1">
              {{ t.stats.visitorsTrend }}
            </p>
          </div>
          <div
            class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 ml-2"
          >
            <PhUsers :size="20" weight="bold" class="text-primary" />
          </div>
        </div>
      </div>

      <!-- Total Page Views Card -->
      <div
        class="liquid-glass rounded-xl p-5 animate-fade-in-up border border-border/10"
      >
        <div v-if="loading" class="space-y-3">
          <div class="flex items-center justify-between">
            <Skeleton class="h-3 w-20 bg-muted" />
            <Skeleton class="h-8 w-8 rounded-lg bg-muted" />
          </div>
          <Skeleton class="h-7 w-28 bg-muted" />
          <Skeleton class="h-3 w-36 bg-muted" />
        </div>
        <div v-else class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-muted-foreground mb-1">
              {{ t.stats.pageViews }}
            </p>
            <h3 class="text-2xl font-bold text-foreground truncate">
              {{ formattedPageViews }}
            </h3>
            <p class="text-[11px] text-primary font-medium mt-1">
              {{ t.stats.pageViewsTrend }}
            </p>
          </div>
          <div
            class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 ml-2"
          >
            <PhFileText :size="20" weight="bold" class="text-primary" />
          </div>
        </div>
      </div>

      <!-- Pages / Visitor Card -->
      <div
        class="liquid-glass rounded-xl p-5 animate-fade-in-up border border-border/10"
      >
        <div v-if="loading" class="space-y-3">
          <div class="flex items-center justify-between">
            <Skeleton class="h-3 w-20 bg-muted" />
            <Skeleton class="h-8 w-8 rounded-lg bg-muted" />
          </div>
          <Skeleton class="h-7 w-20 bg-muted" />
          <Skeleton class="h-3 w-32 bg-muted" />
        </div>
        <div v-else class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-muted-foreground mb-1">
              {{ t.stats.avgViewsPerVisitor }}
            </p>
            <h3 class="text-2xl font-bold text-foreground truncate">
              {{ avgPagesPerVisitor }}
            </h3>
            <p class="text-[11px] text-muted-foreground mt-1">
              {{ t.stats.pageViewsTrend }}
            </p>
          </div>
          <div
            class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 ml-2"
          >
            <PhChartLineUp :size="20" weight="bold" class="text-primary" />
          </div>
        </div>
      </div>

      <!-- Top Device Split Card -->
      <div
        class="liquid-glass rounded-xl p-5 animate-fade-in-up border border-border/10"
      >
        <div v-if="loading" class="space-y-3">
          <div class="flex items-center justify-between">
            <Skeleton class="h-3 w-20 bg-muted" />
            <Skeleton class="h-8 w-8 rounded-lg bg-muted" />
          </div>
          <Skeleton class="h-7 w-24 bg-muted" />
          <Skeleton class="h-3 w-28 bg-muted" />
        </div>
        <div v-else class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-muted-foreground mb-1">
              {{ t.stats.activeDevices }}
            </p>
            <h3 class="text-xl font-bold text-foreground truncate">
              {{ primaryDeviceLabel }}
            </h3>
            <div class="flex items-center gap-1.5 mt-2">
              <span
                v-for="item in deviceSplit"
                :key="item.device"
                class="text-[10px] px-1.5 py-0.5 rounded bg-muted/60 text-muted-foreground"
              >
                {{ item.device }}: {{ item.percentage }}%
              </span>
            </div>
          </div>
          <div
            class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 ml-2"
          >
            <PhDevices :size="20" weight="bold" class="text-primary" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
