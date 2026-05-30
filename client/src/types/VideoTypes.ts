import type { Chapter } from './CourseTypes'

export interface VideoType {
  id: number
  uuid: string
  chapter_id: number
  title: string
  youtube_video_id: string | null
  level: 'beginner' | 'intermediate' | 'advanced' | null
  order: number
  duration_seconds: number
  cooldown_hours: number
  is_published: boolean
  is_preview: boolean
  created_at: string
  updated_at: string
  chapter?: Chapter
}

export interface VideoListParams {
  page?: number
  per_page?: number
  search?: string
  chapter_id?: number
  is_published?: boolean
  sort_by?: string
  sort_order?: 'asc' | 'desc'
}

export interface VideoFormData {
  chapter_id: number
  title: string
  youtube_video_id: string | null
  level: 'beginner' | 'intermediate' | 'advanced' | null
  order: number
  duration_seconds: number
  cooldown_hours: number
  is_published: boolean
  is_preview: boolean
}

export interface VideoPaginatedResponse {
  data: VideoType[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
