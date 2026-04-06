<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { toast } from 'vue-sonner'
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import { PhArrowDown, PhArrowUp } from '@phosphor-icons/vue'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
</script>

<template>
  <main class="flex flex-col items-center justify-center space-y-8 h-screen mx-auto max-w-lg">
    <p class="text-4xl text-primary">The Fibonacci Gallery</p>
    <div class="flex mt-16 flex-wrap items-center gap-2 md:flex-row">
      <Button
        @click="
          toast.success('Welcome To Fibonacci Gallery', {
            description: 'The Place way to get your chart art enhanced',
          })
        "
      >
        Button
      </Button>
      <Button variant="outline" size="icon" aria-label="Submit">
        <PhArrowUp
          class="h-[1.2rem] w-[1.2rem] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0"
        />
        <PhArrowDown
          class="absolute h-[1.2rem] w-[1.2rem] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100"
        />
      </Button>
      <ThemeToggle />
    </div>
    <div></div>

    <Accordion type="single" collapsible class="w-full" default-value="item-1">
      <AccordionItem value="item-1">
        <AccordionTrigger>Product Information</AccordionTrigger>
        <AccordionContent>
          <p>
            Our flagship product combines cutting-edge technology with sleek design. Built with
            premium materials, it offers unparalleled performance and reliability.
          </p>
          <p class="mt-2">
            Key features include advanced processing capabilities, and an intuitive user interface
            designed for both beginners and experts.
          </p>
        </AccordionContent>
      </AccordionItem>
      <AccordionItem value="item-2">
        <AccordionTrigger>Shipping Details</AccordionTrigger>
        <AccordionContent>
          <p>
            We offer worldwide shipping through trusted courier partners. Standard delivery takes
            3-5 business days, while express shipping ensures delivery within 1-2 business days.
          </p>
          <p class="mt-2">
            All orders are carefully packaged and fully insured. Track your shipment in real-time
            through our dedicated tracking portal.
          </p>
        </AccordionContent>
      </AccordionItem>
      <AccordionItem value="item-3">
        <AccordionTrigger>Return Policy</AccordionTrigger>
        <AccordionContent>
          <p>
            We stand behind our products with a comprehensive 30-day return policy. If you're not
            completely satisfied, simply return the item in its original condition.
          </p>
          <p class="mt-2">
            Our hassle-free return process includes free return shipping and full refunds processed
            within 48 hours of receiving the returned item.
          </p>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  </main>
</template>
