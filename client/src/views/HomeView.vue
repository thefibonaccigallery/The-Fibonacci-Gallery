<script setup lang="ts">
import { ArrowUpIcon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { toast } from 'vue-sonner'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue';
</script>

<template>
  <main class="flex flex-col items-center justify-center h-screen">
    <p class="text-4xl text-primary">The Fibonacci Gallery</p>
    <div class="flex mt-16 flex-wrap items-center gap-2 md:flex-row">
      <Button @click="toast.error('Tracking number copied to clipboard')"> Button </Button>
      <Button variant="outline" size="icon" aria-label="Submit">
        <ArrowUpIcon />
      </Button>
      <ThemeToggle/>
    </div>
    <h1 class="font-semibold mt-16">Payout Threshold</h1>
    <p class="mt-8">Set the minimum balance required before a payout is triggered.</p>
  </main>
</template>
