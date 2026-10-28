<script setup lang="ts">
import { computed, ref } from 'vue'
import { Skeleton } from '@/components/ui/skeleton'
import { Button } from '@/components/ui/button'
import { useLanguageStore } from '@/stores/languageStore'
import { PhChartLineUp, PhClock, PhArrowSquareOut } from '@phosphor-icons/vue'
import type { DailyVisitorMetric } from '@/types/analyticsType'

interface Props {
  metrics: DailyVisitorMetric[]
  loading?: boolean
  hasData?: boolean
  propertyUrl?: string
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  hasData: false,
  propertyUrl: undefined,
})

const emit = defineEmits<{
  'open-ga': []
}>()

const langStore = useLanguageStore()
const t = computed(() => langStore.translations.adminAnalytics)

// Hovered point for interactive tooltip
const hoveredIndex = ref<number | null>(null)

// SVG viewBox dimensions
const width = 800
const height = 260
const padding = { top: 20, right: 20, bottom: 40, left: 45 }

const innerWidth = computed(() => width - padding.left - padding.right)
const innerHeight = computed(() => height - padding.top - padding.bottom)

const chartData = computed(() => {
  if (!props.metrics || props.metrics.length === 0) return []
  return props.metrics
})

const maxVisitors = computed(() => {
  if (chartData.value.length === 0) return 10
  const max = Math.max(...chartData.value.map((m) => Math.max(m.visitors, m.pageViews)))
  return max > 0 ? Math.ceil(max * 1.15) : 10
})

const yTicks = computed(() => {
  const max = maxVisitors.value
  return [
    0,
    Math.round(max * 0.25),
    Math.round(max * 0.5),
    Math.round(max * 0.75),
    max,
  ]
})

const points = computed(() => {
  const data = chartData.value
  if (data.length === 0) return []

  const stepX = data.length > 1 ? innerWidth.value / (data.length - 1) : innerWidth.value / 2

  return data.map((item, index) => {
    const x = padding.left + (data.length > 1 ? index * stepX : innerWidth.value / 2)
    const yVisitors =
      padding.top + innerHeight.value - (item.visitors / maxVisitors.value) * innerHeight.value
    const yPageViews =
      padding.top + innerHeight.value - (item.pageViews / maxVisitors.value) * innerHeight.value

    return {
      x,
      yVisitors,
      yPageViews,
      item,
      label: formatDateLabel(item.date),
    }
  })
})

function formatDateLabel(dateStr: string): string {
  if (!dateStr) return ''
  try {
    const parts = dateStr.split('-')
    if (parts.length === 3 && parts[0] && parts[1] && parts[2]) {
      const year = parseInt(parts[0], 10)
      const month = parseInt(parts[1], 10) - 1
      const day = parseInt(parts[2], 10)
      const date = new Date(year, month, day)
      return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' }).format(date)
    }
    return dateStr
  } catch {
    return dateStr
  }
}

// Generate SVG smooth path using catmull-rom or quadratic bezier
function generatePath(coords: { x: number; y: number }[]): string {
  if (coords.length === 0) return ''
  const first = coords[0]
  if (!first) return ''
  if (coords.length === 1) return `M ${first.x} ${first.y}`

  let path = `M ${first.x} ${first.y}`
  for (let i = 0; i < coords.length - 1; i++) {
    const p0 = coords[Math.max(0, i - 1)] ?? first
    const p1 = coords[i] ?? first
    const p2 = coords[i + 1] ?? p1
    const p3 = coords[Math.min(coords.length - 1, i + 2)] ?? p2

    const cp1x = p1.x + (p2.x - p0.x) / 6
    const cp1y = p1.y + (p2.y - p0.y) / 6
    const cp2x = p2.x - (p3.x - p1.x) / 6
    const cp2y = p2.y - (p3.y - p1.y) / 6

    path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`
  }
  return path
}

const visitorsLinePath = computed(() => {
  const coords = points.value.map((p) => ({ x: p.x, y: p.yVisitors }))
  return generatePath(coords)
})

const visitorsAreaPath = computed(() => {
  const line = visitorsLinePath.value
  if (!line || points.value.length === 0) return ''
  const first = points.value[0]
  const last = points.value[points.value.length - 1]
  if (!first || !last) return ''
  const bottomY = padding.top + innerHeight.value
  return `${line} L ${last.x} ${bottomY} L ${first.x} ${bottomY} Z`
})

const pageViewsLinePath = computed(() => {
  const coords = points.value.map((p) => ({ x: p.x, y: p.yPageViews }))
  return generatePath(coords)
})

const activeTooltipPoint = computed(() => {
  if (hoveredIndex.value === null) return null
  return points.value[hoveredIndex.value] || null
})
</script>

<template>
  <div class="liquid-glass rounded-xl p-5 sm:p-6 border border-border/10 space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h3 class="text-base font-bold text-foreground flex items-center gap-2">
          <PhChartLineUp :size="18" class="text-primary" />
          {{ t.chart.title }}
        </h3>
        <p class="text-xs text-muted-foreground mt-0.5">
          {{ t.chart.subtitle }}
        </p>
      </div>

      <!-- Legend -->
      <div v-if="!loading && hasData" class="flex items-center gap-4 text-xs font-medium">
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-primary" />
          <span class="text-muted-foreground">{{ t.chart.visitorsLegend }}</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-foreground/60" />
          <span class="text-muted-foreground">{{ t.chart.viewsLegend }}</span>
        </div>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="h-64 flex flex-col justify-between py-2 space-y-2">
      <Skeleton class="h-4 w-full bg-muted/60" />
      <Skeleton class="h-44 w-full rounded-lg bg-muted/40" />
      <div class="flex justify-between">
        <Skeleton class="h-3 w-16 bg-muted/50" />
        <Skeleton class="h-3 w-16 bg-muted/50" />
        <Skeleton class="h-3 w-16 bg-muted/50" />
        <Skeleton class="h-3 w-16 bg-muted/50" />
      </div>
    </div>

    <!-- Empty State (e.g. New GA4 Property with 0 historical rows) -->
    <div
      v-else-if="!hasData"
      class="py-12 px-4 rounded-lg bg-background/30 border border-border/30 text-center flex flex-col items-center justify-center space-y-3"
    >
      <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
        <PhClock :size="26" />
      </div>
      <div class="max-w-md space-y-1">
        <h4 class="text-sm font-semibold text-foreground">
          {{ t.emptyState.title }}
        </h4>
        <p class="text-xs text-muted-foreground leading-relaxed">
          {{ t.emptyState.description }}
        </p>
      </div>
      <Button
        variant="outline"
        size="sm"
        class="h-8 gap-1.5 text-xs border-border/50 text-foreground hover:bg-accent/40 mt-1"
        @click="emit('open-ga')"
      >
        <PhArrowSquareOut :size="14" class="text-primary" />
        <span>{{ t.emptyState.actionCheckRealtime }}</span>
      </Button>
    </div>

    <!-- Interactive SVG Chart -->
    <div v-else class="relative w-full overflow-hidden">
      <!-- Tooltip -->
      <div
        v-if="activeTooltipPoint"
        class="absolute pointer-events-none z-10 px-3 py-2 rounded-lg bg-popover/90 backdrop-blur-md border border-border/60 shadow-lg text-xs space-y-1 transition-all duration-75"
        :style="{
          left: `${Math.min(Math.max(activeTooltipPoint.x - 60, 10), width - 130)}px`,
          top: `${Math.max(activeTooltipPoint.yVisitors - 75, 10)}px`,
        }"
      >
        <p class="font-bold text-foreground text-[11px] pb-0.5 border-b border-border/40">
          {{ activeTooltipPoint.label }}
        </p>
        <div class="flex items-center justify-between gap-4">
          <span class="text-muted-foreground">{{ t.chart.visitorsLegend }}:</span>
          <span class="font-semibold text-primary">
            {{ activeTooltipPoint.item.visitors.toLocaleString() }}
          </span>
        </div>
        <div class="flex items-center justify-between gap-4">
          <span class="text-muted-foreground">{{ t.chart.viewsLegend }}:</span>
          <span class="font-semibold text-foreground">
            {{ activeTooltipPoint.item.pageViews.toLocaleString() }}
          </span>
        </div>
      </div>

      <svg
        :viewBox="`0 0 ${width} ${height}`"
        class="w-full h-auto select-none"
        @mouseleave="hoveredIndex = null"
      >
        <defs>
          <!-- Primary Gradient for Visitors Area -->
          <linearGradient id="primaryGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.3" />
            <stop offset="100%" stop-color="var(--primary)" stop-opacity="0.0" />
          </linearGradient>
        </defs>

        <!-- Horizontal Grid Lines & Y-Axis Labels -->
        <g class="grid-lines">
          <g v-for="tick in yTicks" :key="tick">
            <line
              :x1="padding.left"
              :y1="padding.top + innerHeight - (tick / maxVisitors) * innerHeight"
              :x2="width - padding.right"
              :y2="padding.top + innerHeight - (tick / maxVisitors) * innerHeight"
              stroke="currentColor"
              class="text-border/25"
              stroke-dasharray="3 3"
              stroke-width="1"
            />
            <text
              :x="padding.left - 8"
              :y="padding.top + innerHeight - (tick / maxVisitors) * innerHeight + 4"
              text-anchor="end"
              class="text-[10px] fill-muted-foreground font-mono"
            >
              {{ tick }}
            </text>
          </g>
        </g>

        <!-- Area fill for Visitors -->
        <path :d="visitorsAreaPath" fill="url(#primaryGradient)" />

        <!-- Page Views Line (Secondary) -->
        <path
          :d="pageViewsLinePath"
          fill="none"
          stroke="currentColor"
          class="text-foreground/40"
          stroke-width="2"
          stroke-dasharray="4 4"
        />

        <!-- Visitors Line (Primary) -->
        <path
          :d="visitorsLinePath"
          fill="none"
          stroke="var(--primary)"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />

        <!-- Data Points & Hover Triggers -->
        <g
          v-for="(pt, idx) in points"
          :key="idx"
          class="cursor-pointer"
          @mouseenter="hoveredIndex = idx"
        >
          <!-- Vertical hover guide line -->
          <line
            v-if="hoveredIndex === idx"
            :x1="pt.x"
            :y1="padding.top"
            :x2="pt.x"
            :y2="padding.top + innerHeight"
            stroke="var(--primary)"
            stroke-width="1"
            stroke-dasharray="2 2"
            opacity="0.6"
          />

          <!-- Invisible wider hit area for hover -->
          <rect
            :x="pt.x - 14"
            :y="padding.top"
            width="28"
            :height="innerHeight"
            fill="transparent"
          />

          <!-- Visitors Point Circle -->
          <circle
            :cx="pt.x"
            :cy="pt.yVisitors"
            :r="hoveredIndex === idx ? 5 : 3"
            fill="var(--background)"
            stroke="var(--primary)"
            stroke-width="2"
            class="transition-all duration-150"
          />

          <!-- X-Axis Date Labels -->
          <text
            v-if="idx % Math.ceil(points.length / 8) === 0 || idx === points.length - 1"
            :x="pt.x"
            :y="height - 12"
            text-anchor="middle"
            class="text-[10px] fill-muted-foreground font-mono"
          >
            {{ pt.label }}
          </text>
        </g>
      </svg>
    </div>
  </div>
</template>
