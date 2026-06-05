<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { PhMagnifyingGlass, PhArrowRight, PhArrowLeft, PhSpinner } from '@phosphor-icons/vue'

interface Currency {
  id: string
  name: string
  symbol: string
  icon?: string
}

const props = defineProps<{
  currencies: Currency[]
  isLoading?: boolean
}>()

const emit = defineEmits<{
  back: []
  next: [currency: Currency]
}>()

const searchQuery = ref('')
const selectedCurrency = ref<Currency | null>(null)

const filteredCurrencies = computed(() => {
  const query = searchQuery.value.toLowerCase()
  if (!query) return props.currencies

  return props.currencies.filter(
    (currency) =>
      currency.name.toLowerCase().includes(query) ||
      currency.symbol.toLowerCase().includes(query) ||
      currency.id.toLowerCase().includes(query),
  )
})

const selectCurrency = (currency: Currency) => {
  selectedCurrency.value = currency
}

const handleNext = () => {
  if (selectedCurrency.value) {
    emit('next', selectedCurrency.value)
  }
}
</script>

<template>
  <div class="animate-fade-in-up liquid-glass rounded-2xl p-6 border border-border/20">
    <h2 class="text-xl font-bold text-foreground mb-6">Select Cryptocurrency</h2>

    <div class="space-y-6">
      <!-- Search -->
      <div class="relative">
        <PhMagnifyingGlass
          class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
        />
        <Input
          v-model="searchQuery"
          placeholder="Search cryptocurrencies..."
          class="pl-10 bg-background/50 border-border/40"
        />
      </div>

      <!-- Currency List -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-96 overflow-y-auto">
        <button
          v-for="currency in filteredCurrencies"
          :key="currency.id"
          @click="selectCurrency(currency)"
          class="p-4 rounded-xl border-2 transition-all duration-200 text-left hover:scale-[1.02]"
          :class="{
            'border-primary bg-primary/10': selectedCurrency?.id === currency.id,
            'border-border bg-background/50 hover:border-primary/50':
              selectedCurrency?.id !== currency.id,
          }"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2"
              :class="{
                'bg-primary/20 border-primary text-primary': selectedCurrency?.id === currency.id,
                'bg-secondary border-border text-muted-foreground':
                  selectedCurrency?.id !== currency.id,
              }"
            >
              {{ currency.symbol.slice(0, 2) }}
            </div>
            <div class="flex-1">
              <div class="font-semibold text-sm text-foreground">{{ currency.name }}</div>
              <div class="text-xs text-muted-foreground">{{ currency.symbol }}</div>
            </div>
          </div>
        </button>

        <div
          v-if="filteredCurrencies.length === 0"
          class="col-span-2 text-center py-8 text-muted-foreground text-sm"
        >
          No cryptocurrencies found
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 pt-4 border-t border-border/20">
        <Button
          @click="emit('back')"
          variant="outline"
          class="flex-1 text-sm font-semibold rounded-xl border-border/40 hover:bg-secondary/50 flex items-center justify-center gap-2"
        >
          <PhArrowLeft class="h-4 w-4" weight="bold" />
          Back
        </Button>
        <Button
          @click="handleNext"
          :disabled="!selectedCurrency || isLoading"
          class="flex-1 text-sm font-semibold rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground flex items-center justify-center gap-2"
        >
          <PhSpinner v-if="isLoading" class="h-4 w-4 animate-spin" />
          <template v-else>
            Confirm and Pay
            <PhArrowRight class="h-4 w-4" weight="bold" />
          </template>
        </Button>
      </div>
    </div>
  </div>
</template>
