<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { Button } from '@/components/ui/button'
import { PhSpinner } from '@phosphor-icons/vue'
import { useEnrollment } from '@/composables/useEnrollment'
import { useLanguageStore } from '@/stores/languageStore'
import axios from '@/api/axiosInstance'

const props = defineProps<{
  courseUuid: string
  course: {
    uuid: string
    price: number | string
    is_enrolled?: boolean
  }
}>()

const router = useRouter()
const languageStore = useLanguageStore()
const lang = computed(() => languageStore.translations)

const { enrollCourse, isEnrolling } = useEnrollment()

// Fetch enrollment status using the status check endpoint
const { data: statusData } = useQuery({
  queryKey: ['enrollment-status', props.courseUuid],
  queryFn: async () => {
    const res = await axios.get(`/enroll/${props.courseUuid}/status`)
    return res.data
  },
  initialData: props.course.is_enrolled !== undefined ? { is_enrolled: props.course.is_enrolled } : undefined
})

const isEnrolled = computed(() => statusData.value?.is_enrolled)
</script>

<template>
  <Button
    v-if="isEnrolled"
    variant="outline"
    class="w-full text-base font-semibold py-6 rounded-xl border border-primary/30 text-foreground hover:bg-secondary/50"
    @click="router.push(`/courses/${courseUuid}/player`)"
  >
    {{ lang.enrollment.go_to_course }}
  </Button>

  <Button
    v-else
    :disabled="isEnrolling"
    class="w-full text-base font-semibold py-6 rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground"
    @click="enrollCourse(courseUuid)"
  >
    <PhSpinner v-if="isEnrolling" class="w-4 h-4 animate-spin mr-2" />
    <span v-if="Number(course.price) === 0">{{ lang.enrollment.enroll_free }}</span>
    <span v-else>{{ lang.enrollment.buy_now }} — ${{ course.price }}</span>
  </Button>
</template>
