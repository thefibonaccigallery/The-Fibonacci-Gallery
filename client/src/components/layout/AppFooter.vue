<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <footer class="border-t border-border/20 py-12 px-6 md:px-12 lg:px-16 bg-card mt-auto w-full">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- Brand -->
        <div class="md:col-span-1">
          <p class="text-lg font-semibold tracking-tight text-foreground mb-3">
            The Fibonacci Gallery
          </p>
          <p class="text-sm text-muted-foreground leading-relaxed">
            Where elite strategy meets artistic precision. Learn to trade with precision and
            confidence.
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <p class="text-sm font-medium text-foreground mb-3">Platform</p>
          <ul class="space-y-2">
            <li>
              <RouterLink
                to="/explore"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Courses</RouterLink
              >
            </li>
            <li>
              <RouterLink
                to="/features"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Features</RouterLink
              >
            </li>
            <li>
              <RouterLink
                to="/markets"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Live Markets</RouterLink
              >
            </li>
          </ul>
        </div>

        <!-- Resources -->
        <div>
          <p class="text-sm font-medium text-foreground mb-3">Resources</p>
          <ul class="space-y-2">
            <li>
              <RouterLink
                to="/faq"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >FAQ</RouterLink
              >
            </li>
            <li>
              <RouterLink
                to="/testimonials"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Testimonials</RouterLink
              >
            </li>
          </ul>
        </div>

        <!-- Legal -->
        <div>
          <p class="text-sm font-medium text-foreground mb-3">Legal</p>
          <ul class="space-y-2">
            <li>
              <RouterLink
                to="/privacy"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Privacy Policy</RouterLink
              >
            </li>
            <li>
              <RouterLink
                to="/terms"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Terms of Service</RouterLink
              >
            </li>
            <li>
              <RouterLink
                to="/disclaimer"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Disclaimer</RouterLink
              >
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div
        class="border-t border-border/20 pt-8 flex flex-col md:flex-row items-center justify-between gap-4"
      >
        <p class="text-xs text-muted-foreground">
          &copy; {{ new Date().getFullYear() }} The Fibonacci Gallery. All rights reserved.
        </p>
        <p class="text-xs text-muted-foreground">
          Trading involves risk. Past performance is not indicative of future results.
        </p>
      </div>
      <div class="w-full flex justify-around mt-6">
        <p class="mx-auto text-sm">
          Made By
          <a
            href="https://linktr.ee/nahomrak"
            target="_blank"
            class="font-semibold text-accentGold hover:underline"
            >RAK</a
          >
          with ❤️
        </p>
      </div>
    </div>
  </footer>
</template>
