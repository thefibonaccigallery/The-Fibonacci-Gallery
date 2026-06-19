import { useQuery } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import type { StudentsResponse } from '@/types/StudentTypes'

export function useStudents() {
  const fetchStudents = async (params?: {
    page?: number
    per_page?: number
    search?: string
    country?: string
  }): Promise<StudentsResponse> => {
    const response = await axiosInstance.get('/admin/students', { params })
    return response.data
  }

  return {
    fetchStudents,
  }
}
