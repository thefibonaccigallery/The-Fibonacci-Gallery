import type Plyr from 'plyr'
import type { CoursePlayerVideo } from '@/types/CoursePlayerTypes'

export type YouTubeQualityLevel =
  | 'auto'
  | 'highres'
  | 'hd2160'
  | 'hd1440'
  | 'hd1080'
  | 'hd720'
  | 'large'
  | 'medium'
  | 'small'
  | 'tiny'

export interface VideoQualityOption {
  label: string
  value: YouTubeQualityLevel
}

export interface VideoPlayerProps {
  video: CoursePlayerVideo | null
}

export interface VideoPlayerProgressPayload {
  watchTime: number
  watchPercentage: number
}

export interface VideoPlayerEmits {
  (e: 'progress', payload: VideoPlayerProgressPayload): void
  (e: 'ended'): void
}

export interface YouTubePlayerEmbed {
  getAvailableQualityLevels?: () => string[]
  getPlaybackQuality?: () => string
  setPlaybackQuality?: (quality: string) => void
}

export type PlyrWithYouTubeEmbed = Plyr & {
  embed?: YouTubePlayerEmbed
}
