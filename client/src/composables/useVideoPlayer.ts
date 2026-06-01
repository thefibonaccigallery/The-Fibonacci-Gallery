import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch, type Ref } from 'vue'
import Plyr from 'plyr'
import 'plyr/dist/plyr.css'
import type {
  PlyrWithYouTubeEmbed,
  VideoPlayerProgressPayload,
  VideoQualityOption,
  YouTubeQualityLevel,
} from '@/types/VideoPlayerTypes'
import type { VideoPlayerProps } from '@/types/VideoPlayerTypes'

const QUALITY_OPTIONS: VideoQualityOption[] = [
  { label: 'Auto', value: 'auto' },
  { label: 'Best', value: 'highres' },
  { label: '2160p', value: 'hd2160' },
  { label: '1440p', value: 'hd1440' },
  { label: '1080p', value: 'hd1080' },
  { label: '720p', value: 'hd720' },
  { label: '480p', value: 'large' },
  { label: '360p', value: 'medium' },
  { label: '240p', value: 'small' },
  { label: '144p', value: 'tiny' },
]

function normalizeQualityLevel(level: string | undefined): YouTubeQualityLevel {
  if (!level) return 'auto'

  const match = QUALITY_OPTIONS.find((option) => option.value === level)
  return match?.value ?? 'auto'
}

export function useVideoPlayer(
  video: Ref<VideoPlayerProps['video']>,
  emitProgress: (payload: VideoPlayerProgressPayload) => void,
  emitEnded: () => void,
) {
  const playerContainer = ref<HTMLDivElement | null>(null)
  const availableQualityLevels = ref<YouTubeQualityLevel[]>(['auto'])
  const selectedQuality = ref<YouTubeQualityLevel>('auto')

  let player: PlyrWithYouTubeEmbed | null = null
  let progressInterval: number | null = null
  let lastProgressUpdate = 0

  const qualityOptions = computed(() => {
    const supportedValues = new Set(availableQualityLevels.value)
    const options = QUALITY_OPTIONS.filter(
      (option) => option.value === 'auto' || supportedValues.has(option.value),
    )

    return options.length > 1 ? options : QUALITY_OPTIONS
  })

  const syncQualityState = () => {
    const embed = player?.embed
    const availableLevels = embed?.getAvailableQualityLevels?.() ?? []

    availableQualityLevels.value = QUALITY_OPTIONS.filter((option) => {
      if (option.value === 'auto') return true
      return availableLevels.includes(option.value)
    }).map((option) => option.value)

    selectedQuality.value = normalizeQualityLevel(embed?.getPlaybackQuality?.())
  }

  const applyQuality = (quality: YouTubeQualityLevel) => {
    selectedQuality.value = quality
    player?.embed?.setPlaybackQuality?.(quality)
  }

  const sendProgress = () => {
    if (!player || !video.value) return

    const currentTime = Math.floor(player.currentTime)
    const duration = player.duration

    if (currentTime === lastProgressUpdate) return
    lastProgressUpdate = currentTime

    if (duration > 0) {
      const watchPercentage = Math.min((currentTime / duration) * 100, 100)

      emitProgress({
        watchTime: currentTime,
        watchPercentage: Math.round(watchPercentage * 100) / 100,
      })
    }
  }

  const handleTimeUpdate = () => {
    // This fires frequently, we'll use the interval for actual updates
  }

  const handlePause = () => {
    sendProgress()
  }

  const handleEnded = () => {
    sendProgress()
    emitEnded()
  }

  const initializePlayer = async () => {
    if (!playerContainer.value || !video.value?.youtube_video_id) return

    if (player) {
      player.destroy()
    }

    player = new Plyr(playerContainer.value, {
      controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
      youtube: {
        noCookie: true,
        rel: 0,
        showinfo: 0,
        iv_load_policy: 3,
        modestbranding: 1,
      },
    }) as PlyrWithYouTubeEmbed

    player.on('ready', () => {
      syncQualityState()
      applyQuality(selectedQuality.value)
    })
    player.on('timeupdate', handleTimeUpdate)
    player.on('pause', handlePause)
    player.on('ended', handleEnded)

    if (progressInterval) {
      clearInterval(progressInterval)
    }

    progressInterval = window.setInterval(() => {
      if (player && player.playing) {
        sendProgress()
      }
    }, 10000)

    await nextTick()
  }

  const cleanup = () => {
    if (progressInterval) {
      clearInterval(progressInterval)
      progressInterval = null
    }

    if (player) {
      player.destroy()
      player = null
    }
  }

  const isQualitySelected = (quality: YouTubeQualityLevel) => selectedQuality.value === quality

  watch(
    () => video.value?.id,
    async () => {
      cleanup()
      selectedQuality.value = 'auto'
      availableQualityLevels.value = ['auto']

      if (video.value?.youtube_video_id) {
        await initializePlayer()
      }
    },
    { flush: 'post' },
  )

  onMounted(() => {
    if (video.value?.youtube_video_id) {
      initializePlayer()
    }
  })

  onBeforeUnmount(() => {
    cleanup()
  })

  return {
    playerContainer,
    qualityOptions,
    selectedQuality,
    applyQuality,
    isQualitySelected,
  }
}
