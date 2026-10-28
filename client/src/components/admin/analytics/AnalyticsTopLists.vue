<script setup lang="ts">
import { computed } from 'vue'
import { Skeleton } from '@/components/ui/skeleton'
import { useLanguageStore } from '@/stores/languageStore'
import {
  PhFileText,
  PhGlobeHemisphereWest,
  PhShareNetwork,
} from '@phosphor-icons/vue'
import type {
  TopPageItem,
  TopCountryItem,
  TopSourceItem,
} from '@/types/analyticsType'

interface Props {
  topPages: TopPageItem[]
  topCountries: TopCountryItem[]
  topSources: TopSourceItem[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false,
})

const langStore = useLanguageStore()
const t = computed(() => langStore.translations.adminAnalytics)

function formatSource(source: string): string {
  if (!source || source === '(direct)') return 'Direct / None'
  if (source === '(not set)') return 'Not specified'
  return source
}

function formatPath(path: string): string {
  if (!path) return '/'
  return path.replace(/^https?:\/\/[^/]+/, '') || '/'
}
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
    <!-- 1. Top Pages -->
    <div
      class="liquid-glass rounded-xl p-5 border border-border/10 flex flex-col animate-fade-in-up"
    >
      <div class="flex items-center gap-2 mb-3">
        <div
          class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0"
        >
          <PhFileText :size="18" weight="bold" class="text-primary" />
        </div>
        <div>
          <h4 class="text-sm font-bold text-foreground">
            {{ t.topPages.title }}
          </h4>
          <p class="text-[11px] text-muted-foreground">
            {{ t.topPages.subtitle }}
          </p>
        </div>
      </div>

      <!-- Skeletons -->
      <div v-if="loading" class="space-y-2.5 py-2">
        <div v-for="i in 5" :key="i" class="flex items-center justify-between">
          <Skeleton class="h-4 w-36 bg-muted/60" />
          <Skeleton class="h-4 w-12 bg-muted/60" />
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="topPages.length === 0"
        class="flex-1 flex items-center justify-center py-8 text-center"
      >
        <p class="text-xs text-muted-foreground">
          {{ t.topPages.empty }}
        </p>
      </div>

      <!-- Scrollable List -->
      <div
        v-else
        class="max-h-60 overflow-y-auto space-y-2 pr-1 font-mono text-xs"
      >
        <div
          v-for="(page, idx) in topPages"
          :key="idx"
          class="flex items-center justify-between p-2 rounded-lg bg-background/30 hover:bg-background/50 transition-colors"
        >
          <div class="min-w-0 flex-1 mr-3">
            <p
              class="text-foreground text-xs truncate font-medium"
              :title="page.path"
            >
              {{ formatPath(page.path) }}
            </p>
            <p
              v-if="page.title"
              class="text-[10px] text-muted-foreground truncate"
            >
              {{ page.title }}
            </p>
          </div>
          <span
            class="px-2 py-0.5 rounded-md bg-muted text-[11px] font-semibold text-primary shrink-0"
          >
            {{ page.views.toLocaleString() }}
          </span>
        </div>
      </div>
    </div>

    <!-- 2. Top Countries -->
    <div
      class="liquid-glass rounded-xl p-5 border border-border/10 flex flex-col animate-fade-in-up"
    >
      <div class="flex items-center gap-2 mb-3">
        <div
          class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0"
        >
          <PhGlobeHemisphereWest :size="18" weight="bold" class="text-primary" />
        </div>
        <div>
          <h4 class="text-sm font-bold text-foreground">
            {{ t.topCountries.title }}
          </h4>
          <p class="text-[11px] text-muted-foreground">
            {{ t.topCountries.subtitle }}
          </p>
        </div>
      </div>

      <!-- Skeletons -->
      <div v-if="loading" class="space-y-2.5 py-2">
        <div v-for="i in 5" :key="i" class="flex items-center justify-between">
          <Skeleton class="h-4 w-32 bg-muted/60" />
          <Skeleton class="h-4 w-12 bg-muted/60" />
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="topCountries.length === 0"
        class="flex-1 flex items-center justify-center py-8 text-center"
      >
        <p class="text-xs text-muted-foreground">
          {{ t.topCountries.empty }}
        </p>
      </div>

      <!-- Scrollable List -->
      <div
        v-else
        class="max-h-60 overflow-y-auto space-y-2 pr-1 font-mono text-xs"
      >
        <div
          v-for="(country, idx) in topCountries"
          :key="idx"
          class="flex items-center justify-between p-2 rounded-lg bg-background/30 hover:bg-background/50 transition-colors"
        >
          <span
            class="text-foreground text-xs truncate font-medium mr-3 flex-1"
          >
            {{ country.country }}
          </span>
          <span
            class="px-2 py-0.5 rounded-md bg-muted text-[11px] font-semibold text-primary shrink-0"
          >
            {{ country.visitors.toLocaleString() }}
          </span>
        </div>
      </div>
    </div>

    <!-- 3. Top Traffic Sources -->
    <div
      class="liquid-glass rounded-xl p-5 border border-border/10 flex flex-col animate-fade-in-up"
    >
      <div class="flex items-center gap-2 mb-3">
        <div
          class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0"
        >
          <PhShareNetwork :size="18" weight="bold" class="text-primary" />
        </div>
        <div>
          <h4 class="text-sm font-bold text-foreground">
            {{ t.topSources.title }}
          </h4>
          <p class="text-[11px] text-muted-foreground">
            {{ t.topSources.subtitle }}
          </p>
        </div>
      </div>

      <!-- Skeletons -->
      <div v-if="loading" class="space-y-2.5 py-2">
        <div v-for="i in 5" :key="i" class="flex items-center justify-between">
          <Skeleton class="h-4 w-32 bg-muted/60" />
          <Skeleton class="h-4 w-12 bg-muted/60" />
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="topSources.length === 0"
        class="flex-1 flex items-center justify-center py-8 text-center"
      >
        <p class="text-xs text-muted-foreground">
          {{ t.topSources.empty }}
        </p>
      </div>

      <!-- Scrollable List -->
      <div
        v-else
        class="max-h-60 overflow-y-auto space-y-2 pr-1 font-mono text-xs"
      >
        <div
          v-for="(source, idx) in topSources"
          :key="idx"
          class="flex items-center justify-between p-2 rounded-lg bg-background/30 hover:bg-background/50 transition-colors"
        >
          <span
            class="text-foreground text-xs truncate font-medium mr-3 flex-1"
          >
            {{ formatSource(source.source) }}
          </span>
          <span
            class="px-2 py-0.5 rounded-md bg-muted text-[11px] font-semibold text-primary shrink-0"
          >
            {{ source.visitors.toLocaleString() }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
