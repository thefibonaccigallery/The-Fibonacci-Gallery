<script setup lang="ts">
import { toRef } from 'vue'
import { Button } from '@/components/ui/button'
import { useVideoPlayer } from '@/composables/useVideoPlayer'
import type { VideoPlayerEmits, VideoPlayerProps } from '@/types/VideoPlayerTypes'

const props = defineProps<VideoPlayerProps>()
const emit = defineEmits<VideoPlayerEmits>()

const { playerContainer, qualityOptions, selectedQuality, applyQuality, isQualitySelected } =
  useVideoPlayer(
    toRef(props, 'video'),
    (payload) => emit('progress', payload),
    () => emit('ended'),
  )
</script>

<template>
  <div class="video-player-wrapper">
    <div v-if="video?.youtube_video_id" class="space-y-3">
      <div class="aspect-video overflow-hidden rounded-lg bg-black">
        <div
          ref="playerContainer"
          :data-plyr-provider="'youtube'"
          :data-plyr-embed-id="video.youtube_video_id"
        ></div>
      </div>
      <div
        class="liquid-glass flex flex-col gap-3 rounded-lg border border-border/70 px-3 py-3 sm:flex-row sm:items-center sm:justify-between"
      >
        <span class="text-sm font-medium text-foreground">Quality</span>
        <div class="flex flex-wrap gap-2">
          <Button
            v-for="option in qualityOptions"
            :key="option.value"
            :variant="isQualitySelected(option.value) ? 'default' : 'outline'"
            size="sm"
            @click="applyQuality(option.value)"
          >
            {{ option.label }}
          </Button>
        </div>
      </div>
    </div>
    <div
      v-else
      class="flex aspect-video items-center justify-center rounded-lg bg-muted text-muted-foreground"
    >
      <p>No video available</p>
    </div>
  </div>
</template>

<style scoped>
.video-player-wrapper :deep(.plyr) {
  --plyr-color-main: var(--color-primary);
}

.video-player-wrapper :deep(.plyr--video) {
  height: 100%;
}

.video-player-wrapper :deep(.plyr__control--overlaid) {
  background: var(--color-primary);
}

.video-player-wrapper :deep(.plyr__control:hover) {
  background: var(--color-primary);
}
</style>
