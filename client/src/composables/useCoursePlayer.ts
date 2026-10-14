import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import type {
  CoursePlayerData,
  CoursePlayerChapter,
  CoursePlayerVideo,
  UpdateProgressPayload,
} from '@/types/CoursePlayerTypes'

export function useCoursePlayer(courseUuid: string) {
  const queryClient = useQueryClient()
  const activeVideoId = ref<string | null>(null)
  const activeChapterId = ref<string | null>(null)

  // Fetch course player data
  const {
    data: playerData,
    isLoading,
    error,
    refetch,
  } = useQuery<CoursePlayerData>({
    queryKey: ['course-player', courseUuid],
    queryFn: async () => {
      const response = await axiosInstance.get(`/courses/${courseUuid}/player`)
      return response.data
    },
  })

  const course = computed(() => playerData.value?.course)
  const chapters = computed(() => playerData.value?.chapters || [])

  // Get all videos in a flat array
  const allVideos = computed(() => {
    return chapters.value.flatMap((chapter) => chapter.videos)
  })

  // Get active video
  const activeVideo = computed(() => {
    if (!activeVideoId.value) return null
    return allVideos.value.find((v) => v.uuid === activeVideoId.value) || null
  })

  // Get active chapter
  const activeChapter = computed(() => {
    if (!activeChapterId.value) return null
    return chapters.value.find((c) => c.uuid === activeChapterId.value) || null
  })

  // Local watch percentage — updated immediately on every progress event so
  // canGoNext and the progress bar react without waiting for a server round-trip
  const currentWatchPercentage = ref(0)

  // Reset to the server value whenever the active video changes
  watch(activeVideoId, () => {
    currentWatchPercentage.value = activeVideo.value?.progress?.watch_percentage || 0
  })

  // Reactive ref for current time, updating every minute
  const now = ref(new Date())

  // Update time every minute
  onMounted(() => {
    const interval = setInterval(() => {
      now.value = new Date()
    }, 60000)

    onUnmounted(() => clearInterval(interval))
  })

  // Check if a video is in cooldown
  const isVideoInCooldown = (video: CoursePlayerVideo): boolean => {
    // Completed videos are never in cooldown
    if (video.progress?.is_completed) return false
    if (!video.progress?.cooldown_until) return false
    return new Date(video.progress.cooldown_until) > now.value
  }

  // Check if a video is unlocked
  const isVideoUnlocked = (chapterIndex: number, videoIndex: number): boolean => {
    const chapter = chapters.value[chapterIndex]
    if (!chapter) return false

    const video = chapter.videos[videoIndex]
    if (!video) return false

    // Already completed videos are always unlocked
    if (video.progress?.is_completed) return true

    // First video of first chapter is always unlocked
    if (chapterIndex === 0 && videoIndex === 0) return true

    // Locked by cooldown
    if (isVideoInCooldown(video)) return false

    // If it's not the first video in the chapter, check previous video
    if (videoIndex > 0) {
      const previousVideo = chapter.videos[videoIndex - 1]
      return previousVideo?.progress?.is_completed === true
    }

    // First video of a new chapter - check if all videos in previous chapter are completed
    const previousChapter = chapters.value[chapterIndex - 1]
    if (!previousChapter) return false

    return previousChapter.videos.every((v) => v.progress?.is_completed === true)
  }

  // Remaining cooldown time as a human readable string
  const getCooldownLabel = (video: CoursePlayerVideo): string => {
    if (video.progress?.is_completed) return ''
    if (!video.progress?.cooldown_until) return ''
    const diff = new Date(video.progress.cooldown_until).getTime() - now.value.getTime()
    if (diff <= 0) return ''
    const hours = Math.floor(diff / 1000 / 60 / 60)
    const minutes = Math.floor((diff / 1000 / 60) % 60)
    return `Available in ${hours}h ${minutes}m`
  }

  // Check if a chapter is unlocked
  const isChapterUnlocked = (chapterIndex: number): boolean => {
    // First chapter is always unlocked
    if (chapterIndex === 0) return true

    const chapter = chapters.value[chapterIndex]
    if (!chapter) return false

    // If any video in this chapter is completed or in progress, chapter is unlocked
    if (
      chapter.videos.some(
        (v) => v.progress?.is_completed || (v.progress?.watch_time_seconds ?? 0) > 0,
      )
    ) {
      return true
    }

    // Check if all videos in previous chapter are completed
    const previousChapter = chapters.value[chapterIndex - 1]
    if (!previousChapter) return false

    return previousChapter.videos.every((v) => v.progress?.is_completed === true)
  }

  // Get chapter completion stats
  const getChapterStats = (chapter: CoursePlayerChapter) => {
    const total = chapter.videos.length
    const completed = chapter.videos.filter((v) => v.progress?.is_completed === true).length
    return { completed, total }
  }

  // Set active video
  const setActiveVideo = (videoUuid: string, chapterUuid: string) => {
    activeVideoId.value = videoUuid
    activeChapterId.value = chapterUuid
  }

  // Get next video
  const getNextVideo = (): { video: CoursePlayerVideo; chapter: CoursePlayerChapter } | null => {
    if (!activeVideo.value || !activeChapter.value) return null

    const currentChapterIndex = chapters.value.findIndex((c) => c.uuid === activeChapter.value!.uuid)
    const currentVideoIndex = activeChapter.value.videos.findIndex(
      (v) => v.uuid === activeVideo.value!.uuid,
    )

    // Check if there's a next video in the current chapter
    if (currentVideoIndex < activeChapter.value.videos.length - 1) {
      const nextVideo = activeChapter.value.videos[currentVideoIndex + 1]
      if (nextVideo) return { video: nextVideo, chapter: activeChapter.value }
    }

    // Check if there's a next chapter
    if (currentChapterIndex < chapters.value.length - 1) {
      const nextChapter = chapters.value[currentChapterIndex + 1]
      if (nextChapter && nextChapter.videos.length > 0) {
        const firstVideo = nextChapter.videos[0]
        if (firstVideo) return { video: firstVideo, chapter: nextChapter }
      }
    }

    // Return first video of next chapter if previous is completed (wait, getNextVideo just seq matches)
    return null
  }

  // Get previous video
  const getPreviousVideo = (): {
    video: CoursePlayerVideo
    chapter: CoursePlayerChapter
  } | null => {
    if (!activeVideo.value || !activeChapter.value) return null

    const currentChapterIndex = chapters.value.findIndex((c) => c.uuid === activeChapter.value!.uuid)
    const currentVideoIndex = activeChapter.value.videos.findIndex(
      (v) => v.uuid === activeVideo.value!.uuid,
    )

    // Check if there's a previous video in the current chapter
    if (currentVideoIndex > 0) {
      const prevVideo = activeChapter.value.videos[currentVideoIndex - 1]
      if (prevVideo) return { video: prevVideo, chapter: activeChapter.value }
    }

    // Check if there's a previous chapter
    if (currentChapterIndex > 0) {
      const prevChapter = chapters.value[currentChapterIndex - 1]
      if (prevChapter && prevChapter.videos.length > 0) {
        const lastVideo = prevChapter.videos[prevChapter.videos.length - 1]
        if (lastVideo) return { video: lastVideo, chapter: prevChapter }
      }
    }

    return null
  }

  // Update progress mutation
  const { mutate: updateProgress, isPending: isUpdatingProgress } = useMutation({
    mutationFn: async ({
      videoUuid,
      payload,
    }: {
      videoUuid: string
      payload: UpdateProgressPayload
    }) => {
      const response = await axiosInstance.post(`/progress/${videoUuid}`, payload)
      return response.data
    },
    onSuccess: () => {
      // Refetch course data so lock/unlock states stay in sync with the server
      queryClient.invalidateQueries({ queryKey: ['course-player', courseUuid] })
    },
  })

  // Whether the user has watched enough of the current video to advance.
  // Uses the local percentage so it reacts instantly (no server round-trip needed).
  const canGoNext = computed(() => {
    const next = getNextVideo()
    if (!next) return false
    const serverPct = activeVideo.value?.progress?.watch_percentage || 0
    return Math.max(currentWatchPercentage.value, serverPct) >= 70
  })

  const canGoPrevious = computed(() => getPreviousVideo() !== null)

  // Report progress: update local percentage immediately for UI feedback,
  // then send only watch_time_seconds to the server (percentage is computed there).
  const reportProgress = (videoUuid: string, watchTimeSeconds: number, localPercentage: number) => {
    currentWatchPercentage.value = localPercentage
    updateProgress({ videoUuid, payload: { watch_time_seconds: watchTimeSeconds } })
  }

  // Initialize with first unlocked video
  const initializePlayer = () => {
    if (chapters.value.length === 0) return

    // Find the first incomplete video or default to first video
    let foundVideo = false

    for (let chapterIndex = 0; chapterIndex < chapters.value.length; chapterIndex++) {
      const chapter = chapters.value[chapterIndex]
      if (!chapter) continue

      for (let videoIndex = 0; videoIndex < chapter.videos.length; videoIndex++) {
        const video = chapter?.videos[videoIndex]
        if (!video) continue

        // Set to first incomplete unlocked video
        if (!video.progress?.is_completed && isVideoUnlocked(chapterIndex, videoIndex)) {
          setActiveVideo(video.uuid, chapter.uuid)
          foundVideo = true
          break
        }
      }

      if (foundVideo) break
    }

    // If all videos are completed or no incomplete found, set to first video
    if (!foundVideo && chapters.value[0]?.videos && chapters.value[0].videos.length > 0) {
      const firstVideo = chapters.value[0].videos[0]
      if (firstVideo) {
        setActiveVideo(firstVideo.uuid, chapters.value[0].uuid)
      }
    }
  }

  return {
    // Data
    course,
    chapters,
    allVideos,
    activeVideo,
    activeChapter,
    activeVideoId,
    activeChapterId,
    currentWatchPercentage,

    // Loading states
    isLoading,
    isUpdatingProgress,
    error,

    // Navigation state
    canGoNext,
    canGoPrevious,

    // Methods
    setActiveVideo,
    isVideoUnlocked,
    isChapterUnlocked,
    getChapterStats,
    getNextVideo,
    getPreviousVideo,
    reportProgress,
    initializePlayer,
    refetch,
    isVideoInCooldown,
    getCooldownLabel,
  }
}
