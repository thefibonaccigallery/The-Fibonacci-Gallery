import type { Chapter } from './CourseTypes'

export interface VideoType {
  uuid: string
  chapter_uuid?: string
  title: string
  youtube_video_id: string | null
  level: 'beginner' | 'intermediate' | 'advanced' | null
  order: number
  duration_seconds: number
  cooldown_hours: number
  is_published: boolean
  is_preview: boolean
  is_big_five: boolean
  isBigFive?: boolean
  created_at: string
  updated_at: string
  chapter?: Chapter
}

export interface VideoListParams {
  page?: number
  per_page?: number
  search?: string
  course_uuid?: string
  chapter_uuid?: string
  is_published?: boolean
  sort_by?: string
  sort_order?: 'asc' | 'desc'
}

export interface VideoFormData {
  chapter_uuid: string
  title: string
  youtube_video_id: string | null
  level: 'beginner' | 'intermediate' | 'advanced' | null
  order: number
  duration_seconds: number
  cooldown_hours: number
  is_published: boolean
  is_preview: boolean
  is_big_five: boolean
  isBigFive?: boolean
}

export interface VideoPaginatedResponse {
  data: VideoType[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
