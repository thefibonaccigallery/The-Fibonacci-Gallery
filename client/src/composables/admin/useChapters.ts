import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type { Chapter, ChapterFormData, PaginatedResponse, ReorderItem } from '@/types/CourseTypes'

export function useChapters() {
  const queryClient = useQueryClient()

  // Fetch chapters
  const fetchChapters = async (params?: {
    page?: number
    per_page?: number
    course_uuid?: string
    search?: string
  }): Promise<PaginatedResponse<Chapter>> => {
    const response = await axiosInstance.get('/admin/chapters', { params })
    return response.data
  }

  // Fetch single chapter
  const fetchChapter = async (uuid: string): Promise<Chapter> => {
    const response = await axiosInstance.get(`/admin/chapters/${uuid}`)
    return response.data
  }

  // Create chapter
  const { mutate: createChapter, isPending: isCreating } = useMutation({
    mutationFn: async (data: ChapterFormData) => {
      const response = await axiosInstance.post('/admin/chapters', data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Chapter created successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to create chapter')
    },
  })

  // Update chapter
  const { mutate: updateChapter, isPending: isUpdating } = useMutation({
    mutationFn: async ({ uuid, data }: { uuid: string; data: Partial<ChapterFormData> }) => {
      const response = await axiosInstance.put(`/admin/chapters/${uuid}`, data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Chapter updated successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update chapter')
    },
  })

  // Delete chapter
  const { mutate: deleteChapter, isPending: isDeleting } = useMutation({
    mutationFn: async (uuid: string) => {
      const response = await axiosInstance.delete(`/admin/chapters/${uuid}`)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Chapter deleted successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to delete chapter')
    },
  })

  // Reorder chapters
  const { mutate: reorderChapters, isPending: isReordering } = useMutation({
    mutationFn: async (chapters: ReorderItem[]) => {
      const response = await axiosInstance.post('/admin/chapters/reorder', { chapters })
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['chapters'] })
      queryClient.invalidateQueries({ queryKey: ['courses'] })
      toast.success('Chapters reordered successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to reorder chapters')
    },
  })

  return {
    fetchChapters,
    fetchChapter,
    createChapter,
    updateChapter,
    deleteChapter,
    reorderChapters,
    isCreating,
    isUpdating,
    isDeleting,
    isReordering,
  }
}
