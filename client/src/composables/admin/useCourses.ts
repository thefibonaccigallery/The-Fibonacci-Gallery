import { ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type {
  Course,
  CourseFormData,
  PaginatedResponse,
} from '@/types/CourseTypes'

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
  const fetchCourse = async (id: number): Promise<Course> => {
    const response = await axiosInstance.get(`/admin/courses/${id}`)
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
    mutationFn: async ({ id, data }: { id: number; data: Partial<CourseFormData> }) => {
      const response = await axiosInstance.put(`/admin/courses/${id}`, data)
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
    mutationFn: async (id: number) => {
      const response = await axiosInstance.delete(`/admin/courses/${id}`)
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
    mutationFn: async ({ id, file }: { id: number; file: File }) => {
      const formData = new FormData()
      formData.append('thumbnail', file)
      const response = await axiosInstance.post(`/admin/courses/${id}/thumbnail`, formData, {
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
    mutationFn: async (id: number) => {
      const response = await axiosInstance.delete(`/admin/courses/${id}/thumbnail`)
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
