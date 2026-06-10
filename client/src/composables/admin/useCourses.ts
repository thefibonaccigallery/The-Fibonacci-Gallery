import { ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type { Course, CourseFormData, PaginatedResponse } from '@/types/CourseTypes'

export function useCourses() {
  const queryClient = useQueryClient()

  // Fetch courses
  const fetchCourses = async (params?: {
    page?: number
    per_page?: number
    is_published?: boolean
    search?: string
  }): Promise<PaginatedResponse<Course>> => {
    const response = await axiosInstance.get('/admin/courses', { params })
    return response.data
  }

  // Fetch single course
  const fetchCourse = async (uuid: string): Promise<Course> => {
    const response = await axiosInstance.get(`/admin/courses/${uuid}`)
    return response.data
  }

  // Create course
  const { mutate: createCourse, isPending: isCreating } = useMutation({
    mutationFn: async (data: CourseFormData) => {
      const response = await axiosInstance.post('/admin/courses', data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Course created successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to create course')
    },
  })

  // Update course
  const { mutate: updateCourse, isPending: isUpdating } = useMutation({
    mutationFn: async ({ uuid, data }: { uuid: string; data: Partial<CourseFormData> }) => {
      const response = await axiosInstance.put(`/admin/courses/${uuid}`, data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Course updated successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update course')
    },
  })

  // Delete course
  const { mutate: deleteCourse, isPending: isDeleting } = useMutation({
    mutationFn: async (uuid: string) => {
      const response = await axiosInstance.delete(`/admin/courses/${uuid}`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Course deleted successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to delete course')
    },
  })

  // Upload thumbnail
  const { mutate: uploadThumbnail, isPending: isUploadingThumbnail } = useMutation({
    mutationFn: async ({ uuid, file }: { uuid: string; file: File }) => {
      const formData = new FormData()
      formData.append('thumbnail', file)
      const response = await axiosInstance.post(`/admin/courses/${uuid}/thumbnail`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Thumbnail uploaded successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to upload thumbnail')
    },
  })

  // Delete thumbnail
  const { mutate: deleteThumbnail, isPending: isDeletingThumbnail } = useMutation({
    mutationFn: async (uuid: string) => {
      const response = await axiosInstance.delete(`/admin/courses/${uuid}/thumbnail`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Thumbnail deleted successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to delete thumbnail')
    },
  })

  return {
    fetchCourses,
    fetchCourse,
    createCourse,
    updateCourse,
    deleteCourse,
    uploadThumbnail,
    deleteThumbnail,
    isCreating,
    isUpdating,
    isDeleting,
    isUploadingThumbnail,
    isDeletingThumbnail,
  }
}
