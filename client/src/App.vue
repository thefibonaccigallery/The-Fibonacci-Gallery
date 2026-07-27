<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
import { useColorMode } from '@vueuse/core'
import { computed } from 'vue'

const mode = useColorMode()
const toasterTheme = computed(() => {
  return mode.value === 'dark'
})
</script>

<template>
  <Toaster :closeButton="true" closeButtonPosition="top-right" richColors position="top-right" />
  <RouterView :key="$route.fullPath" />
</template>
