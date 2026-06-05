<script setup lang="ts">
import { computed } from 'vue'
import { PhCheck } from '@phosphor-icons/vue'

const props = defineProps<{
  currentStep: number
}>()

const steps = [
  { number: 1, label: 'Course' },
  { number: 2, label: 'Payment' },
  { number: 3, label: 'Confirm' },
]

const getStepStatus = (stepNumber: number) => {
  if (stepNumber < props.currentStep) return 'completed'
  if (stepNumber === props.currentStep) return 'active'
  return 'upcoming'
}
</script>

<template>
  <div class="flex items-center justify-between w-full max-w-md mx-auto mb-8">
    <div
      v-for="(step, index) in steps"
      :key="step.number"
      class="flex items-center"
      :class="{ 'flex-1': index < steps.length - 1 }"
    >
      <!-- Step Circle -->
      <div class="flex flex-col items-center">
        <div
          class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 border-2"
          :class="{
            'bg-primary text-primary-foreground border-primary':
              getStepStatus(step.number) === 'completed',
            'bg-primary/20 text-primary border-primary':
              getStepStatus(step.number) === 'active',
            'bg-secondary text-muted-foreground border-border':
              getStepStatus(step.number) === 'upcoming',
          }"
        >
          <PhCheck
            v-if="getStepStatus(step.number) === 'completed'"
            class="h-5 w-5"
            weight="bold"
          />
          <span v-else>{{ step.number }}</span>
        </div>
        <span
          class="text-xs mt-2 font-medium"
          :class="{
            'text-primary': getStepStatus(step.number) !== 'upcoming',
            'text-muted-foreground': getStepStatus(step.number) === 'upcoming',
          }"
        >
          {{ step.label }}
        </span>
      </div>

      <!-- Connector Line -->
      <div
        v-if="index < steps.length - 1"
        class="flex-1 h-0.5 mx-2 transition-colors duration-300"
        :class="{
          'bg-primary': step.number < currentStep,
          'bg-border': step.number >= currentStep,
        }"
      />
    </div>
  </div>
</template>
