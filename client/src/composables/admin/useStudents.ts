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

  const banStudent = async (uuid: string, reason?: string): Promise<void> => {
    await axiosInstance.post(`/admin/students/${uuid}/ban`, { reason: reason || null })
  }

  const unbanStudent = async (uuid: string): Promise<void> => {
    await axiosInstance.post(`/admin/students/${uuid}/unban`)
  }

  return {
    fetchStudents,
    banStudent,
    unbanStudent,
  }
}
