<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
import { useColorMode } from '@vueuse/core'
import { onMounted, watchEffect } from 'vue'

const mode = useColorMode({ initialValue: 'dark' })

watchEffect(() => {
  if (mode.value !== 'dark') {
    mode.value = 'dark'
  }
  if (typeof document !== 'undefined') {
    if (!document.documentElement.classList.contains('dark')) {
      document.documentElement.classList.add('dark')
    }
    document.documentElement.style.colorScheme = 'dark'
  }
})

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.documentElement.classList.add('dark')
    document.documentElement.style.colorScheme = 'dark'
    try {
      localStorage.setItem('vueuse-color-scheme', 'dark')
    } catch {
      // ignore storage errors
    }
  }
})
</script>

<template>
  <Toaster :closeButton="true" closeButtonPosition="top-right" richColors position="top-right" theme="dark" />
  <RouterView :key="$route.fullPath" />
</template>

