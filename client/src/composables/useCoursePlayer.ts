import { ref, computed } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import type {
  CoursePlayerData,
  CoursePlayerChapter,
  CoursePlayerVideo,
  UpdateProgressPayload,
} from '@/types/CoursePlayerTypes'

export function useCoursePlayer(courseId: number) {
  const queryClient = useQueryClient()
  const activeVideoId = ref<number | null>(null)
  const activeChapterId = ref<number | null>(null)

  // Fetch course player data
  const {
    data: playerData,
    isLoading,
    error,
    refetch,
  } = useQuery<CoursePlayerData>({
    queryKey: ['course-player', courseId],
    queryFn: async () => {
      const response = await axiosInstance.get(`/courses/${courseId}/player`)
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
    return allVideos.value.find((v) => v.id === activeVideoId.value) || null
  })

  // Get active chapter
  const activeChapter = computed(() => {
    if (!activeChapterId.value) return null
    return chapters.value.find((c) => c.id === activeChapterId.value) || null
  })

  // Check if a video is unlocked
  const isVideoUnlocked = (chapterIndex: number, videoIndex: number): boolean => {
    // First video of first chapter is always unlocked
    if (chapterIndex === 0 && videoIndex === 0) return true

    const chapter = chapters.value[chapterIndex]
    if (!chapter) return false

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

  // Check if a chapter is unlocked
  const isChapterUnlocked = (chapterIndex: number): boolean => {
    // First chapter is always unlocked
    if (chapterIndex === 0) return true

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
  const setActiveVideo = (videoId: number, chapterId: number) => {
    activeVideoId.value = videoId
    activeChapterId.value = chapterId
  }

  // Get next video
  const getNextVideo = (): { video: CoursePlayerVideo; chapter: CoursePlayerChapter } | null => {
    if (!activeVideo.value || !activeChapter.value) return null

    const currentChapterIndex = chapters.value.findIndex((c) => c.id === activeChapter.value!.id)
    const currentVideoIndex = activeChapter.value.videos.findIndex(
      (v) => v.id === activeVideo.value!.id,
    )

    // Check if there's a next video in the current chapter
    if (currentVideoIndex < activeChapter.value.videos.length - 1) {
      const nextVideo = activeChapter.value.videos[currentVideoIndex + 1]
      return { video: nextVideo, chapter: activeChapter.value }
    }

    // Check if there's a next chapter
    if (currentChapterIndex < chapters.value.length - 1) {
      const nextChapter = chapters.value[currentChapterIndex + 1]
      if (nextChapter.videos.length > 0) {
        return { video: nextChapter.videos[0], chapter: nextChapter }
      }
    }

    return null
  }

  // Get previous video
  const getPreviousVideo = ():
    | { video: CoursePlayerVideo; chapter: CoursePlayerChapter }
    | null => {
    if (!activeVideo.value || !activeChapter.value) return null

    const currentChapterIndex = chapters.value.findIndex((c) => c.id === activeChapter.value!.id)
    const currentVideoIndex = activeChapter.value.videos.findIndex(
      (v) => v.id === activeVideo.value!.id,
    )

    // Check if there's a previous video in the current chapter
    if (currentVideoIndex > 0) {
      const prevVideo = activeChapter.value.videos[currentVideoIndex - 1]
      return { video: prevVideo, chapter: activeChapter.value }
    }

    // Check if there's a previous chapter
    if (currentChapterIndex > 0) {
      const prevChapter = chapters.value[currentChapterIndex - 1]
      if (prevChapter.videos.length > 0) {
        const lastVideo = prevChapter.videos[prevChapter.videos.length - 1]
        return { video: lastVideo, chapter: prevChapter }
      }
    }

    return null
  }

  // Update progress mutation
  const { mutate: updateProgress, isPending: isUpdatingProgress } = useMutation({
    mutationFn: async ({
      videoId,
      payload,
    }: {
      videoId: number
      payload: UpdateProgressPayload
    }) => {
      const response = await axiosInstance.post(`/progress/${videoId}`, payload)
      return response.data
    },
    onSuccess: () => {
      // Invalidate and refetch course player data to update unlock states
      queryClient.invalidateQueries({ queryKey: ['course-player', courseId] })
    },
  })

  // Initialize with first unlocked video
  const initializePlayer = () => {
    if (chapters.value.length === 0) return

    // Find the first incomplete video or default to first video
    let foundVideo = false

    for (let chapterIndex = 0; chapterIndex < chapters.value.length; chapterIndex++) {
      const chapter = chapters.value[chapterIndex]

      for (let videoIndex = 0; videoIndex < chapter.videos.length; videoIndex++) {
        const video = chapter.videos[videoIndex]

        // Set to first incomplete unlocked video
        if (!video.progress?.is_completed && isVideoUnlocked(chapterIndex, videoIndex)) {
          setActiveVideo(video.id, chapter.id)
          foundVideo = true
          break
        }
      }

      if (foundVideo) break
    }

    // If all videos are completed or no incomplete found, set to first video
    if (!foundVideo && chapters.value[0]?.videos[0]) {
      setActiveVideo(chapters.value[0].videos[0].id, chapters.value[0].id)
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

    // Loading states
    isLoading,
    isUpdatingProgress,
    error,

    // Methods
    setActiveVideo,
    isVideoUnlocked,
    isChapterUnlocked,
    getChapterStats,
    getNextVideo,
    getPreviousVideo,
    updateProgress,
    initializePlayer,
    refetch,
  }
}
