import { onMounted, onUnmounted } from 'vue'

/**
 * useVideoSecurity
 *
 * Provides two protections that should be activated on any page that renders
 * the secure video player:
 *
 *  1. Keyboard shortcut blocking — prevents Ctrl+S (save), Ctrl+U (view source)
 *     and F12 (DevTools toggle) from working while the user is on the page.
 *
 *  2. DevTools detection — uses the well-known window-size delta heuristic to
 *     detect when the browser's DevTools panel has been opened. When detected:
 *       - console.clear() is called to wipe any logs the user may have read.
 *       - The optional `onDevToolsDetected` callback is invoked so the caller
 *         can pause the video player.
 *
 * Usage:
 *   useVideoSecurity({ onDevToolsDetected: () => player?.pause() })
 */
export function useVideoSecurity(options?: { onDevToolsDetected?: () => void }) {
  // ------------------------------------------------------------------
  // 1. Keyboard shortcut blocking
  // ------------------------------------------------------------------
  const handleKeyDown = (e: KeyboardEvent) => {
    const blocked =
      (e.ctrlKey && e.key === 's') || // Ctrl+S  — Save page
      (e.ctrlKey && e.key === 'u') || // Ctrl+U  — View source
      e.key === 'F12'                 // F12     — DevTools toggle

    if (blocked) {
      e.preventDefault()
      e.stopPropagation()
    }
  }

  // ------------------------------------------------------------------
  // 2. DevTools detection via window-size delta
  // ------------------------------------------------------------------
  const DEV_TOOLS_THRESHOLD = 160 // px

  const detectDevTools = () => {
    const widthDelta  = window.outerWidth  - window.innerWidth
    const heightDelta = window.outerHeight - window.innerHeight

    if (widthDelta > DEV_TOOLS_THRESHOLD || heightDelta > DEV_TOOLS_THRESHOLD) {
      console.clear()
      options?.onDevToolsDetected?.()
    }
  }

  // ------------------------------------------------------------------
  // Lifecycle
  // ------------------------------------------------------------------
  onMounted(() => {
    window.addEventListener('keydown', handleKeyDown, { capture: true })
    window.addEventListener('resize', detectDevTools)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown, { capture: true })
    window.removeEventListener('resize', detectDevTools)
  })
}
