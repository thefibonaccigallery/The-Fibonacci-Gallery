import { computed, unref, type Ref } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'

export interface EnrolledCourse {
  id: number
  title: string
  description: string | null
  thumbnail_url: string | null
  level: string
  totalLessons: number
  completedLessons: number
  progress_percentage: number
  enrolled_at: string
  last_watched_video: {
    id: number
    title: string
  } | null
}

export interface ExploreCourse {
  id: number
  title: string
  description: string | null
  thumbnail_url: string | null
  level: string
  price: number
  totalLessons: number
  length_seconds: number
  published_at: string
}

export interface CourseDetail {
  id: number
  title: string
  description: string | null
  thumbnail_url: string | null
  level: string
  price: number
  totalLessons: number
  length_seconds: number
  published_at: string
  is_enrolled: boolean
  chapters: Array<{
    id: number
    title: string
    level: string | null
    order: number
    video_count: number
  }>
}

export interface ContinueLearningCourse {
  id: number
  title: string
  description: string | null
  thumbnail_url: string | null
  totalLessons: number
  completedLessons: number
  next_video_title: string
}

export function useUserCourses() {
  const queryClient = useQueryClient()
  type ExploreParams = {
    search?: string
    level?: string
    sort_by?: string
    sort_order?: string
  }
  type ExploreParamsInput = ExploreParams | Ref<ExploreParams | undefined>

  // Fetch enrolled courses
  const useMyEnrolledCourses = () => {
    return useQuery<{ success: boolean; courses: EnrolledCourse[] }>({
      queryKey: ['my-courses'],
      queryFn: async () => {
        const response = await axiosInstance.get('/my-courses')
        return response.data
      },
    })
  }

  // Fetch explore courses
  const useExploreCourses = (params?: ExploreParamsInput) => {
    const resolvedParams = computed(() => (params ? unref(params) : undefined))

    return useQuery<{ success: boolean; courses: ExploreCourse[] }>({
      queryKey: ['explore-courses', resolvedParams],
      queryFn: async () => {
        const response = await axiosInstance.get('/explore-courses', {
          params: resolvedParams.value,
        })
        return response.data
      },
    })
  }

  // Fetch single course detail
  const useCourseDetail = (courseId: number) => {
    return useQuery<{ success: boolean; course: CourseDetail }>({
      queryKey: ['course-detail', courseId],
      queryFn: async () => {
        const response = await axiosInstance.get(`/courses/${courseId}`)
        return response.data
      },
      enabled: !!courseId,
    })
  }

  // Fetch continue learning suggestion
  const useContinueLearning = () => {
    return useQuery<{ success: boolean; course: ContinueLearningCourse | null }>({
      queryKey: ['continue-learning'],
      queryFn: async () => {
        const response = await axiosInstance.get('/continue-learning')
        return response.data
      },
    })
  }

  // Enroll in a course
  const useEnrollCourse = () => {
    return useMutation({
      mutationFn: async (courseId: number) => {
        const response = await axiosInstance.post(`/courses/${courseId}/enroll`)
        return response.data
      },
      onSuccess: (data) => {
        queryClient.invalidateQueries({ queryKey: ['my-courses'] })
        queryClient.invalidateQueries({ queryKey: ['explore-courses'] })
        queryClient.invalidateQueries({ queryKey: ['continue-learning'] })
        toast.success(data.message || 'Successfully enrolled in the course!')
      },
      onError: (error: any) => {
        toast.error(error.response?.data?.message || 'Failed to enroll in the course')
      },
    })
  }

  return {
    useMyEnrolledCourses,
    useExploreCourses,
    useCourseDetail,
    useContinueLearning,
    useEnrollCourse,
  }
}
