<script setup lang="ts">
import { RouterLink } from 'vue-router'
import logo from '@/assets/images/white_final.webp'
</script>

<template>
  <footer class="border-t border-border/20 py-12 px-6 md:px-12 lg:px-16 bg-card mt-auto w-full">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-8 mb-8">
        <!-- Brand -->
        <div class="md:col-span-1">
          <RouterLink to="/" class="flex items-center gap-2.5 group mb-3 w-fit">
            <div class="w-12 h-12 flex items-center justify-center shrink-0">
              <img :src="logo" alt="The Fibonacci Gallery" class="w-12 h-12 object-contain" />
            </div>
            <p class="text-base font-bold tracking-tight text-foreground group-hover:text-primary transition-colors">
              The Fibonacci Gallery
            </p>
          </RouterLink>
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

        <!-- Contact -->
        <div>
          <p class="text-sm font-medium text-foreground mb-3">Contact</p>
          <ul class="space-y-2">
            <li>
              <a
                href="https://t.me/thefibonaccigallerysupport"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Telegram Support</a
              >
            </li>
            <li>
              <a
                href="mailto:thefibonaccigallery@gmail.com"
                class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >Email Us</a
              >
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div
        class="border-t border-border/20 pt-8 flex flex-col md:flex-row items-center justify-between gap-4"
      >
        <div class="flex items-center gap-2">
          <img :src="logo" alt="Logo" class="w-5 h-5 object-contain opacity-60" />
          <p class="text-xs text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} The Fibonacci Gallery. All rights reserved.
          </p>
        </div>
        <p class="text-xs text-muted-foreground">
          Trading involves risk. Past performance is not indicative of future results.
        </p>
      </div>
      <!-- <div class="w-full flex justify-around mt-6">
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
      </div> -->
    </div>
  </footer>
</template>
