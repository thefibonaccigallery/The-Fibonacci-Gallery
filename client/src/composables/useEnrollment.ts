import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import axios from '@/api/axiosInstance'
import { useLanguageStore } from '@/stores/languageStore'

export const useEnrollment = () => {
  const router = useRouter()
  const queryClient = useQueryClient()
  const languageStore = useLanguageStore()

  const { mutate: enrollCourse, isPending: isEnrolling } = useMutation({
    mutationFn: (courseId: number) =>
      axios.post(`/enroll/${courseId}`).then(r => r.data),

    onSuccess: (data, courseId) => {
      // Invalidate queries to refresh enrollment states in UI
      queryClient.invalidateQueries({ queryKey: ['my-courses'] })
      queryClient.invalidateQueries({ queryKey: ['explore-courses'] })
      queryClient.invalidateQueries({ queryKey: ['course-detail', courseId] })
      queryClient.invalidateQueries({ queryKey: ['enrollment-status', courseId] })

      if (!data.requires_payment) {
        toast.success(languageStore.translations.enrollment.success)
        router.push(`/courses/${courseId}/player`)
      } else {
        router.push(`/checkout/${courseId}`)
      }
    },

    onError: (err: any) => {
      if (err.response?.status === 409) {
        toast.error(err.response.data.message || languageStore.translations.enrollment.already_enrolled)
      } else {
        toast.error(err.response?.data?.message || 'Failed to enroll in the course')
      }
    },
  })

  return { enrollCourse, isEnrolling }
}
