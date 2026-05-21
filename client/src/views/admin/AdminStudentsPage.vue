<script setup lang="ts">
import { PhUsers } from '@phosphor-icons/vue'
</script>

<template>
  <div class="space-y-6">
    <div class="liquid-glass rounded-xl p-12 text-center animate-fade-in-up">
      <div
        class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4"
      >
        <PhUsers :size="32" weight="bold" class="text-primary" />
      </div>
      <h2 class="text-2xl font-bold text-foreground mb-2">Students Management</h2>
      <p class="text-muted-foreground">This page will display and manage all students.</p>
    </div>
  </div>
</template>
