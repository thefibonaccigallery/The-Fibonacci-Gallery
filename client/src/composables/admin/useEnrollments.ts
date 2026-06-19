import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type { EnrollmentsResponse } from '@/types/EnrollmentTypes'

export function useEnrollments() {
  const queryClient = useQueryClient()

  const fetchEnrollments = async (params?: {
    page?: number
    per_page?: number
    search?: string
    is_completed?: boolean
  }): Promise<EnrollmentsResponse> => {
    const response = await axiosInstance.get('/admin/enrollments', { params })
    return response.data
  }

  const { mutate: enrollStudent, isPending: isEnrolling } = useMutation({
    mutationFn: async ({ userUuid, courseUuid }: { userUuid: string; courseUuid: string }) => {
      const response = await axiosInstance.post('/admin/enrollments', {
        user_uuid: userUuid,
        course_uuid: courseUuid,
      })
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-enrollments'] })
      queryClient.invalidateQueries({ queryKey: ['admin-students'] })
      toast.success('Student enrolled successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to enroll student')
    },
  })

  const { mutate: removeEnrollment, isPending: isRemoving } = useMutation({
    mutationFn: async (uuid: string) => {
      const response = await axiosInstance.delete(`/admin/enrollments/${uuid}`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-enrollments'] })
      queryClient.invalidateQueries({ queryKey: ['admin-students'] })
      toast.success('Enrollment removed successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to remove enrollment')
    },
  })

  return {
    fetchEnrollments,
    enrollStudent,
    removeEnrollment,
    isEnrolling,
    isRemoving,
  }
}
