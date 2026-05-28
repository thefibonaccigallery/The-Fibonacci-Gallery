import { ref } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'
import axiosInstance from '@/api/axiosInstance'
import { validateFormWithZod, normalizeErrors, type NormalizedErrors } from '@/utils/errorFormatter'
import { videoSchema, type VideoFormField, type VideoFormState } from '@/schemas/videoSchemas'
import type { VideoType, VideoListParams, VideoPaginatedResponse } from '@/types/VideoTypes'

export function useVideos() {
  const queryClient = useQueryClient()
  const errors = ref<NormalizedErrors<VideoFormField>>({})

  // Fetch videos
  const fetchVideos = async (params?: VideoListParams): Promise<VideoPaginatedResponse> => {
    try {
      const response = await axiosInstance.get('/admin/videos', { params })
      return response.data
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to fetch videos')
      throw err
    }
  }

  // Fetch single video
  const fetchVideo = async (id: number): Promise<VideoType> => {
    try {
      const response = await axiosInstance.get(`/admin/videos/${id}`)
      return response.data
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Failed to fetch video')
      throw err
    }
  }

  // Create video mutation
  const { mutate: createVideo, isPending: isCreating } = useMutation({
    mutationFn: async (data: VideoFormState) => {
      const response = await axiosInstance.post('/admin/videos', data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['videos'] })
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      toast.success('Video created successfully')
    },
    onError: (err: any) => {
      if (err.response?.data?.errors) {
        errors.value = normalizeErrors<VideoFormField>(err.response.data.errors)
      } else {
        toast.error(err.response?.data?.message || 'Failed to create video')
      }
    },
  })

  // Update video mutation
  const { mutate: updateVideo, isPending: isUpdating } = useMutation({
    mutationFn: async ({ id, data }: { id: number; data: Partial<VideoFormState> }) => {
      const response = await axiosInstance.put(`/admin/videos/${id}`, data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['videos'] })
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      toast.success('Video updated successfully')
    },
    onError: (err: any) => {
      if (err.response?.data?.errors) {
        errors.value = normalizeErrors<VideoFormField>(err.response.data.errors)
      } else {
        toast.error(err.response?.data?.message || 'Failed to update video')
      }
    },
  })

  // Delete video mutation
  const { mutate: deleteVideo, isPending: isDeleting } = useMutation({
    mutationFn: async (id: number) => {
      const response = await axiosInstance.delete(`/admin/videos/${id}`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['videos'] })
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      toast.success('Video deleted successfully')
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || 'Failed to delete video')
    },
  })

  // Clear error for a specific field
  const clearError = (field: VideoFormField) => {
    if (errors.value && errors.value[field]) {
      delete errors.value[field]
    }
  }

  // Validate form with Zod
  const validateForm = (data: VideoFormState) => {
    errors.value = {}
    const error = validateFormWithZod<VideoFormField>(videoSchema, data)
    if (error) {
      errors.value = error
      return false
    }
    return true
  }

  return {
    // Data
    errors,

    // Methods
    fetchVideos,
    fetchVideo,
    createVideo,
    updateVideo,
    deleteVideo,
    clearError,
    validateForm,

    // Loading states
    isCreating,
    isUpdating,
    isDeleting,
  }
}
