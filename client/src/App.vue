<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
</script>

<template>
  <Toaster :closeButton="true" closeButtonPosition="top-right" richColors position="top-right" />
  <RouterView />
</template>
