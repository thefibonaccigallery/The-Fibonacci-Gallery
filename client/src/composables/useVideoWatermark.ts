import { watch, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { Ref } from 'vue'

/**
 * useVideoWatermark
 *
 * Manages a persistent, semi-transparent UUID watermark on the video player.
 * It is implemented with direct DOM manipulation so the element can be
 * physically relocated into the browser's fullscreen layer on fullscreenchange.
 *
 * Key design: uses watch(containerRef) instead of onMounted so the watermark
 * is created the moment the wrapper div actually appears in the DOM (which is
 * delayed because it sits behind a v-else-if that waits for the video token).
 */
export function useVideoWatermark(
  containerRef: Ref<HTMLElement | null>,
  repositionInterval = 8000,
) {
  const authStore = useAuthStore()
  let el: HTMLSpanElement | null = null
  let timer: ReturnType<typeof setInterval> | null = null
  let initialized = false

  // ── Helpers ──────────────────────────────────────────────────────────────

  const randomPct = (min: number, max: number) =>
    `${Math.floor(Math.random() * (max - min + 1)) + min}%`

  const applyBaseStyles = (span: HTMLSpanElement) => {
    Object.assign(span.style, {
      position:      'absolute',
      pointerEvents: 'none',
      userSelect:    'none',
      zIndex:        '9999',
      opacity:       '0.18',
      color:         '#ffffff',
      fontSize:      '12px',
      fontFamily:    'monospace',
      fontWeight:    '600',
      letterSpacing: '0.05em',
      whiteSpace:    'nowrap',
      textShadow:    '0 1px 3px rgba(0,0,0,0.9)',
      transition:    'top 0.6s ease, left 0.6s ease',
    })
  }

  const reposition = () => {
    if (!el) return
    el.style.top  = randomPct(5, 70)
    el.style.left = randomPct(5, 65)
  }

  // ── Mount / move helpers ─────────────────────────────────────────────────

  const mountTo = (parent: Element) => {
    if (!el) return
    el.parentElement?.removeChild(el)
    parent.appendChild(el)
  }

  // ── Fullscreen handler ───────────────────────────────────────────────────

  const handleFullscreenChange = () => {
    if (!el) return
    if (document.fullscreenElement) {
      mountTo(document.fullscreenElement)
    } else {
      if (containerRef.value) mountTo(containerRef.value)
    }
  }

  // ── Initialise once the container div renders ────────────────────────────
  // containerRef is behind a v-else-if so it is null at component mount time.
  // watch() fires as soon as Vue sets the template ref (after the condition
  // becomes true and the element is inserted into the DOM).

  const stopWatch = watch(
    containerRef,
    (container) => {
      if (!container || initialized) return
      const uuid = authStore.user?.uuid
      if (!uuid) return

      initialized = true

      el = document.createElement('span')
      el.setAttribute('aria-hidden', 'true')
      el.textContent = uuid
      applyBaseStyles(el)
      container.appendChild(el)

      reposition()
      timer = setInterval(reposition, repositionInterval)

      document.addEventListener('fullscreenchange',       handleFullscreenChange)
      document.addEventListener('webkitfullscreenchange', handleFullscreenChange)
    },
    { immediate: true },
  )

  // ── Cleanup ──────────────────────────────────────────────────────────────

  onUnmounted(() => {
    stopWatch()
    if (timer !== null) { clearInterval(timer); timer = null }
    el?.parentElement?.removeChild(el)
    el = null
    document.removeEventListener('fullscreenchange',       handleFullscreenChange)
    document.removeEventListener('webkitfullscreenchange', handleFullscreenChange)
  })
}


