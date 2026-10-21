<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { PhCaretDown, PhMagnifyingGlass } from '@phosphor-icons/vue'

interface CountryCode {
  code: string
  country: string
  dialCode: string
}

const props = defineProps<{
  modelValue?: string
  countryCodes: CountryCode[]
  error?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const isOpen = ref(false)
const searchQuery = ref('')
const selectedCountry = ref<CountryCode | null>(null)

// Initialize selected country if modelValue exists
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue && !selectedCountry.value) {
      selectedCountry.value =
        props.countryCodes.find((cc) => cc.dialCode === newValue) || null
    }
  },
  { immediate: true }
)

const filteredCountries = computed(() => {
  if (!searchQuery.value) return props.countryCodes

  const query = searchQuery.value.toLowerCase()
  return props.countryCodes.filter(
    (cc) =>
      cc.country.toLowerCase().includes(query) ||
      cc.dialCode.includes(query) ||
      cc.code.toLowerCase().includes(query)
  )
})

const selectCountry = (country: CountryCode) => {
  selectedCountry.value = country
  emit('update:modelValue', country.dialCode)
  isOpen.value = false
  searchQuery.value = ''
}

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (!isOpen.value) {
    searchQuery.value = ''
  }
}

// Close dropdown when clicking outside
const dropdownRef = ref<HTMLDivElement | null>(null)
const handleClickOutside = (e: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
    isOpen.value = false
    searchQuery.value = ''
  }
}

watch(isOpen, (newValue) => {
  if (newValue) {
    document.addEventListener('click', handleClickOutside)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

// Get flag emoji from country code
const getFlag = (countryCode: string) => {
  if (!countryCode || countryCode.length !== 2) return '🌐'
  const codePoints = countryCode
    .toUpperCase()
    .split('')
    .map((char) => 127397 + char.charCodeAt(0))
  return String.fromCodePoint(...codePoints)
}
</script>

<template>
  <div ref="dropdownRef" class="relative w-full">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggleDropdown"
      :class="[
        'flex items-center justify-between w-full h-10 px-3 py-2 text-sm rounded-md border transition-all duration-200',
        'bg-background/50 hover:bg-background/70',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
        error
          ? 'border-destructive focus-visible:ring-destructive'
          : 'border-border/40 focus-visible:ring-primary hover:border-border/60',
        isOpen && 'ring-2 ring-primary ring-offset-2 border-primary',
      ]"
    >
      <div class="flex items-center gap-2 flex-1 min-w-0">
        <span class="text-xl leading-none">
          {{ selectedCountry ? getFlag(selectedCountry.code) : '🌐' }}
        </span>
        <span class="text-foreground font-medium">
          {{ selectedCountry ? selectedCountry.dialCode : '+1' }}
        </span>
      </div>
      <PhCaretDown
        :class="[
          'w-4 h-4 text-muted-foreground transition-transform duration-200 shrink-0',
          isOpen && 'rotate-180',
        ]"
      />
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 -translate-y-2"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 -translate-y-2"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 w-full min-w-[280px] mt-2 rounded-xl border border-border/40 bg-background/95 backdrop-blur-lg shadow-2xl overflow-hidden"
      >
        <!-- Search Input -->
        <div class="p-3 border-b border-border/30 bg-muted/20">
          <div class="relative">
            <PhMagnifyingGlass
              class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search country..."
              class="w-full h-10 pl-10 pr-3 text-sm rounded-lg border border-border/40 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
              @click.stop
            />
          </div>
        </div>

        <!-- Country List -->
        <div class="max-h-[280px] overflow-y-auto scrollbar-thin">
          <button
            v-for="country in filteredCountries"
            :key="country.code"
            type="button"
            @click="selectCountry(country)"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm transition-all duration-150',
              'hover:bg-primary/10',
              selectedCountry?.code === country.code
                ? 'bg-primary/15 border-l-2 border-l-primary'
                : 'border-l-2 border-l-transparent',
            ]"
          >
            <span class="text-2xl leading-none shrink-0">{{ getFlag(country.code) }}</span>
            <div class="flex-1 text-left min-w-0">
              <div
                :class="[
                  'truncate',
                  selectedCountry?.code === country.code
                    ? 'text-foreground font-semibold'
                    : 'text-foreground font-medium',
                ]"
              >
                {{ country.country }}
              </div>
            </div>
            <span
              :class="[
                'font-mono text-xs shrink-0 px-2 py-0.5 rounded-md',
                selectedCountry?.code === country.code
                  ? 'bg-primary/20 text-primary font-semibold'
                  : 'bg-muted/50 text-muted-foreground',
              ]"
            >
              {{ country.dialCode }}
            </span>
          </button>

          <!-- No Results -->
          <div
            v-if="filteredCountries.length === 0"
            class="px-4 py-12 text-center text-sm text-muted-foreground"
          >
            <div class="text-3xl mb-2">🔍</div>
            <div>No countries found</div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Custom scrollbar */
.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: hsl(var(--muted-foreground) / 0.3) transparent;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}
</style>
