<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import TraderviewWidget from '@/components/blocks/TraderviewWidget.vue'
import axiosInstance from '@/api/axiosInstance'
import {
  PhTrendUp,
  PhTrendDown,
  PhGlobe,
  PhCoins,
  PhChartBar,
  PhFlame,
  PhArrowClockwise,
  PhCircleNotch,
} from '@phosphor-icons/vue'

// ── Types ────────────────────────────────────────────────────────────────────
interface MarketItem {
  name: string
  symbol: string
  prefix: string
  value: string
  change: string
  up: boolean
  raw?: number
}

interface MarketData {
  indices: MarketItem[]
  forex: MarketItem[]
  crypto: MarketItem[]
  commodities: MarketItem[]
  fetched_at?: string
}

// ── Fallback static data (shown while loading or on error) ────────────────
const STATIC: MarketData = {
  indices: [
    { name: 'S&P 500',   symbol: 'SPX',    prefix: '', value: '—', change: '—', up: true },
    { name: 'Nasdaq 100',symbol: 'NDX',    prefix: '', value: '—', change: '—', up: true },
    { name: 'Dow Jones', symbol: 'DJI',    prefix: '', value: '—', change: '—', up: true },
    { name: 'FTSE 100',  symbol: 'FTSE',   prefix: '', value: '—', change: '—', up: true },
  ],
  forex: [
    { name: 'EUR / USD', symbol: 'EURUSD', prefix: '', value: '—', change: '—', up: true },
    { name: 'GBP / USD', symbol: 'GBPUSD', prefix: '', value: '—', change: '—', up: true },
    { name: 'USD / JPY', symbol: 'USDJPY', prefix: '', value: '—', change: '—', up: true },
    { name: 'AUD / USD', symbol: 'AUDUSD', prefix: '', value: '—', change: '—', up: true },
  ],
  crypto: [
    { name: 'Bitcoin',   symbol: 'BTCUSD', prefix: '$', value: '—', change: '—', up: true },
    { name: 'Ethereum',  symbol: 'ETHUSD', prefix: '$', value: '—', change: '—', up: true },
    { name: 'Solana',    symbol: 'SOLUSD', prefix: '$', value: '—', change: '—', up: true },
    { name: 'Cardano',   symbol: 'ADAUSD', prefix: '$', value: '—', change: '—', up: true },
  ],
  commodities: [
    { name: 'Gold',        symbol: 'XAUUSD', prefix: '$', value: '—', change: '—', up: true },
    { name: 'Silver',      symbol: 'XAGUSD', prefix: '$', value: '—', change: '—', up: true },
    { name: 'Crude Oil',   symbol: 'USOIL',  prefix: '$', value: '—', change: '—', up: true },
    { name: 'Natural Gas', symbol: 'NGAS',   prefix: '$', value: '—', change: '—', up: true },
  ],
}

// ── State ─────────────────────────────────────────────────────────────────
const markets = ref<MarketData>({ ...STATIC })
const isLoading = ref(true)
const hasError = ref(false)
const lastUpdated = ref<string | null>(null)
let pollInterval: ReturnType<typeof setInterval> | null = null

// ── Data fetching ──────────────────────────────────────────────────────────
async function fetchMarkets(showLoader = true) {
  if (showLoader) isLoading.value = true
  hasError.value = false
  try {
    const { data } = await axiosInstance.get<MarketData>('/market-data')

    // Merge with static placeholders so rows with no live data still render
    markets.value = {
      indices:     data.indices?.length     ? data.indices     : STATIC.indices,
      forex:       data.forex?.length       ? data.forex       : STATIC.forex,
      crypto:      data.crypto?.length      ? data.crypto      : STATIC.crypto,
      commodities: data.commodities?.length ? data.commodities : STATIC.commodities,
      fetched_at:  data.fetched_at,
    }

    lastUpdated.value = data.fetched_at ?? new Date().toISOString()
  } catch {
    hasError.value = true
    markets.value = { ...STATIC }
  } finally {
    isLoading.value = false
  }
}

const formattedLastUpdated = computed(() => {
  if (!lastUpdated.value) return null
  return new Date(lastUpdated.value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
})

onMounted(() => {
  fetchMarkets()
  // Re-fetch every 60 seconds silently (no spinner)
  pollInterval = setInterval(() => fetchMarkets(false), 60_000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background text-foreground flex flex-col justify-between font-primary">
      <div class="container mx-auto px-6 py-16 md:py-24 max-w-6xl">
        <!-- Hero Section -->
        <div class="text-center mb-16">
          <div class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-4 py-1.5 text-xs font-semibold text-primary mb-6 uppercase tracking-wider">
            Live Market Analytics
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-normal mb-6 tracking-tight">
            Real-Time Market <span class="text-primary">Intelligence</span>
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            Access live price feeds, charting metrics, and indices across major markets. Our TradingView integration provides professional-grade technical tools right inside your workspace.
          </p>
        </div>

        <!-- TradingView Chart Component -->
        <div class="rounded-3xl border border-border/20 overflow-hidden liquid-glass p-2 mb-16 shadow-2xl shadow-primary/5 w-full">
          <div class="min-h-[580px] w-full rounded-2xl overflow-hidden bg-card">
            <TraderviewWidget />
          </div>
        </div>

        <!-- Live Data Status Bar -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-2">
            <span
              :class="[
                'w-2 h-2 rounded-full shrink-0',
                hasError ? 'bg-red-500' : 'bg-green-500 animate-pulse',
              ]"
            />
            <span class="text-xs text-muted-foreground">
              {{ hasError ? 'Using cached data — refresh to retry' : 'Live prices · auto-refreshes every 60s' }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <span v-if="formattedLastUpdated" class="text-xs text-muted-foreground">
              Updated {{ formattedLastUpdated }}
            </span>
            <button
              @click="fetchMarkets(true)"
              :disabled="isLoading"
              class="flex items-center gap-1.5 text-xs text-primary hover:text-primary/80 transition-colors disabled:opacity-50"
              title="Refresh market data"
            >
              <PhArrowClockwise :class="['h-3.5 w-3.5', isLoading ? 'animate-spin' : '']" />
              Refresh
            </button>
          </div>
        </div>

        <!-- Asset Classes Grid -->
        <div class="grid md:grid-cols-2 gap-8">

          <!-- Indices -->
          <div class="border border-border/20 rounded-3xl p-6 md:p-8 liquid-glass space-y-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <PhChartBar class="h-5 w-5" />
              </div>
              <h2 class="text-xl font-semibold">World Indices</h2>
            </div>
            <div class="divide-y divide-border/10">
              <div
                v-for="item in markets.indices"
                :key="item.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ item.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ item.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm tabular-nums">
                    <PhCircleNotch v-if="isLoading && item.value === '—'" class="h-4 w-4 inline animate-spin text-muted-foreground" />
                    <span v-else>{{ item.prefix }}{{ item.value }}</span>
                  </p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5 tabular-nums',
                      item.value === '—' ? 'text-muted-foreground' : item.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component v-if="item.value !== '—'" :is="item.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ item.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Forex -->
          <div class="border border-border/20 rounded-3xl p-6 md:p-8 liquid-glass space-y-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <PhGlobe class="h-5 w-5" />
              </div>
              <h2 class="text-xl font-semibold">Foreign Exchange (FX)</h2>
            </div>
            <div class="divide-y divide-border/10">
              <div
                v-for="item in markets.forex"
                :key="item.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ item.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ item.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm tabular-nums">
                    <PhCircleNotch v-if="isLoading && item.value === '—'" class="h-4 w-4 inline animate-spin text-muted-foreground" />
                    <span v-else>{{ item.prefix }}{{ item.value }}</span>
                  </p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5 tabular-nums',
                      item.value === '—' ? 'text-muted-foreground' : item.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component v-if="item.value !== '—'" :is="item.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ item.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Crypto -->
          <div class="border border-border/20 rounded-3xl p-6 md:p-8 liquid-glass space-y-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <PhCoins class="h-5 w-5" />
              </div>
              <h2 class="text-xl font-semibold">Cryptocurrency</h2>
            </div>
            <div class="divide-y divide-border/10">
              <div
                v-for="item in markets.crypto"
                :key="item.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ item.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ item.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm tabular-nums">
                    <PhCircleNotch v-if="isLoading && item.value === '—'" class="h-4 w-4 inline animate-spin text-muted-foreground" />
                    <span v-else>{{ item.prefix }}{{ item.value }}</span>
                  </p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5 tabular-nums',
                      item.value === '—' ? 'text-muted-foreground' : item.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component v-if="item.value !== '—'" :is="item.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ item.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Commodities -->
          <div class="border border-border/20 rounded-3xl p-6 md:p-8 liquid-glass space-y-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <PhFlame class="h-5 w-5" />
              </div>
              <h2 class="text-xl font-semibold">Commodities</h2>
            </div>
            <div class="divide-y divide-border/10">
              <div
                v-for="item in markets.commodities"
                :key="item.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ item.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ item.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm tabular-nums">
                    <PhCircleNotch v-if="isLoading && item.value === '—'" class="h-4 w-4 inline animate-spin text-muted-foreground" />
                    <span v-else>{{ item.prefix }}{{ item.value }}</span>
                  </p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5 tabular-nums',
                      item.value === '—' ? 'text-muted-foreground' : item.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component v-if="item.value !== '—'" :is="item.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ item.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
  backdrop-filter: blur(10px);
}
</style>
