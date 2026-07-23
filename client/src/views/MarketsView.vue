<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import TraderviewWidget from '@/components/blocks/TraderviewWidget.vue'
import {
  PhTrendUp,
  PhTrendDown,
  PhGlobe,
  PhCoins,
  PhChartBar,
  PhFlame,
} from '@phosphor-icons/vue'

const authStore = useAuthStore()

const markets = {
  indices: [
    { name: 'S&P 500', symbol: 'SPX', value: '5,137.08', change: '+0.80%', up: true },
    { name: 'Nasdaq 100', symbol: 'NDX', value: '18,302.91', change: '+1.14%', up: true },
    { name: 'Dow Jones', symbol: 'DJI', value: '39,087.38', change: '-0.18%', up: false },
    { name: 'FTSE 100', symbol: 'FTSE', value: '7,682.50', change: '+0.69%', up: true },
  ],
  forex: [
    { name: 'EUR / USD', symbol: 'EURUSD', value: '1.0841', change: '+0.12%', up: true },
    { name: 'GBP / USD', symbol: 'GBPUSD', value: '1.2655', change: '+0.08%', up: true },
    { name: 'USD / JPY', symbol: 'USDJPY', value: '150.12', change: '-0.24%', up: false },
    { name: 'AUD / USD', symbol: 'AUDUSD', value: '0.6521', change: '+0.15%', up: true },
  ],
  crypto: [
    { name: 'Bitcoin', symbol: 'BTCUSD', value: '62,450.00', change: '+4.82%', up: true },
    { name: 'Ethereum', symbol: 'ETHUSD', value: '3,430.50', change: '+3.15%', up: true },
    { name: 'Solana', symbol: 'SOLUSD', value: '131.25', change: '+8.44%', up: true },
    { name: 'Cardano', symbol: 'ADAUSD', value: '0.6850', change: '-1.05%', up: false },
  ],
  commodities: [
    { name: 'Gold', symbol: 'XAUUSD', value: '2,082.90', change: '+0.95%', up: true },
    { name: 'Silver', symbol: 'XAGUSD', value: '23.12', change: '+1.40%', up: true },
    { name: 'Crude Oil', symbol: 'USOIL', value: '78.26', change: '+2.10%', up: true },
    { name: 'Natural Gas', symbol: 'NGAS', value: '1.835', change: '-3.20%', up: false },
  ],
}
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
        <div class="rounded-3xl border border-border/20 overflow-hidden liquid-glass p-2 mb-16 shadow-2xl shadow-primary/5">
          <div class="h-[500px] w-full rounded-2xl overflow-hidden bg-zinc-950">
            <TraderviewWidget />
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
                v-for="index in markets.indices"
                :key="index.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ index.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ index.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm">{{ index.value }}</p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5',
                      index.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component :is="index.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ index.change }}
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
                v-for="pair in markets.forex"
                :key="pair.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ pair.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ pair.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm">{{ pair.value }}</p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5',
                      pair.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component :is="pair.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ pair.change }}
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
                v-for="coin in markets.crypto"
                :key="coin.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ coin.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ coin.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm">${{ coin.value }}</p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5',
                      coin.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component :is="coin.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ coin.change }}
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
                v-for="comm in markets.commodities"
                :key="comm.symbol"
                class="py-4 flex justify-between items-center first:pt-0 last:pb-0"
              >
                <div>
                  <p class="font-medium text-sm">{{ comm.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ comm.symbol }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm">${{ comm.value }}</p>
                  <span
                    :class="[
                      'inline-flex items-center text-xs font-medium gap-1 mt-0.5',
                      comm.up ? 'text-green-500' : 'text-red-500',
                    ]"
                  >
                    <component :is="comm.up ? PhTrendUp : PhTrendDown" class="h-3 w-3" />
                    {{ comm.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Public Footer (only shown if not authenticated) -->
      <footer v-if="!authStore.isAuthenticated" class="border-t border-border/20 py-12 px-6 bg-card mt-24">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
          <p class="text-xs text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} The Fibonacci Gallery. All rights reserved.
          </p>
          <div class="flex gap-6">
            <RouterLink to="/privacy" class="text-xs text-muted-foreground hover:text-foreground">Privacy Policy</RouterLink>
            <RouterLink to="/terms" class="text-xs text-muted-foreground hover:text-foreground">Terms of Service</RouterLink>
            <RouterLink to="/disclaimer" class="text-xs text-muted-foreground hover:text-foreground">Disclaimer</RouterLink>
          </div>
        </div>
      </footer>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
  backdrop-filter: blur(10px);
}
</style>
