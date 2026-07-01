<script setup lang="ts">
import { computed } from 'vue'
import { PhX, PhVideo } from '@phosphor-icons/vue'
import VideoPlayer from '@/components/course-player/VideoPlayer.vue'
import type { VideoType } from '@/types/VideoTypes'
import type { CoursePlayerVideo } from '@/types/CoursePlayerTypes'

interface Props {
  open: boolean
  video: VideoType | null
}

interface Emits {
  (e: 'update:open', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const close = () => emit('update:open', false)

// Adapt VideoType → CoursePlayerVideo so the existing VideoPlayer can consume it
const playerVideo = computed<CoursePlayerVideo | null>(() => {
  if (!props.video) return null
  return {
    uuid: props.video.uuid,
    title: props.video.title,
    youtube_video_id: props.video.youtube_video_id,
    level: props.video.level,
    order: props.video.order,
    duration_seconds: props.video.duration_seconds,
    cooldown_hours: props.video.cooldown_hours,
    progress: null,
  }
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        @click.self="close"
      >
        <Transition
          enter-active-class="transition-all duration-300"
          leave-active-class="transition-all duration-200"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            v-if="open"
            class="liquid-glass w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl"
          >
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-border/50">
              <div class="flex items-center gap-3 min-w-0">
                <div
                  class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 flex-shrink-0"
                >
                  <PhVideo :size="18" class="text-primary" />
                </div>
                <div class="min-w-0">
                  <p class="text-xs text-muted-foreground uppercase tracking-wider font-medium">
                    Admin Preview
                  </p>
                  <h2 class="text-sm font-semibold text-foreground truncate">
                    {{ video?.title ?? 'Video Preview' }}
                  </h2>
                </div>
              </div>
              <button
                id="admin-preview-close"
                @click="close"
                class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-muted/60 text-muted-foreground hover:text-foreground transition-colors flex-shrink-0"
                aria-label="Close preview"
              >
                <PhX :size="18" />
              </button>
            </div>

            <!-- Player -->
            <div class="p-4">
              <VideoPlayer :video="playerVideo" />
            </div>

            <!-- Footer metadata -->
            <div
              class="px-5 py-3 border-t border-border/50 flex flex-wrap items-center gap-4 text-xs text-muted-foreground"
            >
              <span v-if="video?.youtube_video_id">
                ID: <span class="font-mono text-foreground">{{ video.youtube_video_id }}</span>
              </span>
              <span v-if="video?.level" class="capitalize">
                Level: <span class="text-foreground">{{ video.level }}</span>
              </span>
              <span v-if="video?.cooldown_hours">
                Cooldown: <span class="text-foreground">{{ video.cooldown_hours }}h</span>
              </span>
              <span
                :class="[
                  'ml-auto inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-medium',
                  video?.is_published
                    ? 'bg-primary/10 text-primary'
                    : 'bg-muted text-muted-foreground',
                ]"
              >
                {{ video?.is_published ? 'Published' : 'Draft' }}
              </span>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
