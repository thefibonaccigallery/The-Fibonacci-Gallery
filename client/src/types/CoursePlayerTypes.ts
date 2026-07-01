export interface VideoProgress {
  watch_time_seconds: number
  watch_percentage: number
  is_completed: boolean
  last_watched_at: string | null
  cooldown_until: string | null
  is_in_cooldown: boolean
}

export interface CoursePlayerVideo {
  uuid: string
  title: string
  youtube_video_id?: string | null
  level: 'beginner' | 'intermediate' | 'advanced' | null
  order: number
  duration_seconds: number
  cooldown_hours: number
  progress: VideoProgress | null
}

export interface CoursePlayerChapter {
  uuid: string
  title: string
  level: 'beginner' | 'intermediate' | 'advanced' | null
  order: number
  length_seconds: number
  videos: CoursePlayerVideo[]
}

export interface CoursePlayerData {
  course: {
    uuid: string
    title: string
    description: string | null
    thumbnail_url: string | null
  }
  chapters: CoursePlayerChapter[]
}

export interface UpdateProgressPayload {
  watch_time_seconds: number
}
