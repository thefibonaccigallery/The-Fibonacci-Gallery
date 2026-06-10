export type CourseLevel = 'beginner' | 'intermediate' | 'advanced'

export interface Course {
  uuid: string
  title: string
  slug: string
  description: string | null
  thumbnail: string | null
  thumbnail_url: string | null
  level: CourseLevel
  length_seconds: number
  price: number
  is_published: boolean
  published_at: string | null
  created_at: string
  updated_at: string
  chapters?: Chapter[]
}

export interface Chapter {
  uuid: string
  course_uuid?: string
  title: string
  level: CourseLevel | null
  length_seconds: number
  order: number
  created_at: string
  updated_at: string
  course?: Course
  videos?: Video[]
}

export interface Video {
  uuid: string
  chapter_uuid?: string
  title: string
  level: CourseLevel | null
  order: number
  duration_seconds: number
  is_published: boolean
  is_preview: boolean
  created_at: string
  updated_at: string
  chapter?: Chapter
}

export interface CourseFormData {
  title: string
  slug?: string
  description?: string
  level: CourseLevel
  price: number
  is_published: boolean
}

export interface ChapterFormData {
  course_uuid: string
  title: string
  level?: CourseLevel
  order?: number
}

export interface VideoFormData {
  chapter_uuid: string
  title: string
  level?: CourseLevel
  order?: number
  duration_seconds?: number
  is_published: boolean
  is_preview: boolean
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface ReorderItem {
  uuid: string
  order: number
}
