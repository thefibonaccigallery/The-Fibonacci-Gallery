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
    course_id?: number
    search?: string
  }): Promise<PaginatedResponse<Chapter>> => {
    const response = await axiosInstance.get('/admin/chapters', { params })
    return response.data
  }

  // Fetch single chapter
  const fetchChapter = async (id: number): Promise<Chapter> => {
    const response = await axiosInstance.get(`/admin/chapters/${id}`)
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
    mutationFn: async ({ id, data }: { id: number; data: Partial<ChapterFormData> }) => {
      const response = await axiosInstance.put(`/admin/chapters/${id}`, data)
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
    mutationFn: async (id: number) => {
      const response = await axiosInstance.delete(`/admin/chapters/${id}`)
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
