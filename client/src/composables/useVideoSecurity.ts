import { ref, onMounted, onUnmounted } from 'vue'
import { toast } from 'vue-sonner'

export interface VideoSecurityOptions {
  onDevToolsDetected?: () => void
  onSecurityTriggered?: () => void
}

/**
 * useVideoSecurity
 *
 * Provides comprehensive client-side security against capture and inspection:
 *  1. Keyboard blocking — prevents Ctrl combos, Windows (Meta) combos, PrintScreen, and F12.
 *  2. Anti-Snipping / Anti-Capture — Windows Snipping Tool (Win+Shift+S) is an OS-level
 *     global hook that steals window focus. When blur or visibilitychange fires, the player
 *     is immediately obscured with a black screen and paused, preventing any capture.
 *  3. DevTools detection via window-size delta heuristic.
 */
export function useVideoSecurity(options?: VideoSecurityOptions) {
  const isObscured = ref<boolean>(false)

  // Throttle the toast so it fires at most once every 2 seconds
  let lastToastAt = 0

  const dismissObscured = () => {
    isObscured.value = false
  }

  const triggerSecurityWarning = (message = 'This action is not allowed') => {
    isObscured.value = true
    options?.onSecurityTriggered?.()
    options?.onDevToolsDetected?.()

    // Blank the clipboard if possible to neutralize any taken screenshot
    try {
      if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText('')
      }
    } catch (_) {
      // Ignore clipboard permission errors
    }

    const now = Date.now()
    if (now - lastToastAt > 2000) {
      lastToastAt = now
      toast.error(message, {
        description: 'Screen capture and keyboard shortcuts are disabled.',
        duration: 2500,
      })
    }
  }

  // ------------------------------------------------------------------
  // 1. Keyboard shortcut blocking (keydown & keyup)
  // ------------------------------------------------------------------
  const checkSecurityKey = (e: KeyboardEvent): boolean => {
    const isCtrlCombo = e.ctrlKey || e.key === 'Control'
    const isWindowsKey =
      e.metaKey ||
      e.key === 'Meta' ||
      e.code === 'MetaLeft' ||
      e.code === 'MetaRight'
    const isPrintScreen =
      e.key === 'PrintScreen' ||
      e.code === 'PrintScreen' ||
      e.keyCode === 44
    const isF12 = e.key === 'F12'

    return isCtrlCombo || isWindowsKey || isPrintScreen || isF12
  }

  const handleKeyDown = (e: KeyboardEvent) => {
    if (checkSecurityKey(e)) {
      isObscured.value = true
      e.preventDefault()
      e.stopPropagation()
      triggerSecurityWarning()
    }
  }

  const handleKeyUp = (e: KeyboardEvent) => {
    if (checkSecurityKey(e)) {
      isObscured.value = true
      e.preventDefault()
      e.stopPropagation()
      triggerSecurityWarning()
    }
  }

  // ------------------------------------------------------------------
  // 2. Anti-Snipping / Window Blur Defense (Defeats Win + Shift + S)
  // ------------------------------------------------------------------
  // Win+Shift+S activates the Windows Snipping Tool overlay, causing
  // the browser window to instantly lose focus (blur).
  // Obscuring the video during blur guarantees the screenshot captures nothing.
  let focusTimeout: number | null = null

  const handleWindowBlur = () => {
    isObscured.value = true
    options?.onDevToolsDetected?.()
    options?.onSecurityTriggered?.()
  }

  const handleWindowFocus = () => {
    // Delay clearing slightly so any lingering screen capture closes first
    if (focusTimeout) clearTimeout(focusTimeout)
    focusTimeout = window.setTimeout(() => {
      isObscured.value = false
    }, 600)
  }

  const handleVisibilityChange = () => {
    if (document.hidden) {
      isObscured.value = true
      options?.onDevToolsDetected?.()
      options?.onSecurityTriggered?.()
    } else {
      if (focusTimeout) clearTimeout(focusTimeout)
      focusTimeout = window.setTimeout(() => {
        isObscured.value = false
      }, 600)
    }
  }

  // ------------------------------------------------------------------
  // 3. DevTools detection via window-size delta
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
    window.addEventListener('keyup', handleKeyUp, { capture: true })
    window.addEventListener('blur', handleWindowBlur)
    window.addEventListener('focus', handleWindowFocus)
    document.addEventListener('visibilitychange', handleVisibilityChange)
    window.addEventListener('resize', detectDevTools)
  })

  onUnmounted(() => {
    if (focusTimeout) clearTimeout(focusTimeout)
    window.removeEventListener('keydown', handleKeyDown, { capture: true })
    window.removeEventListener('keyup', handleKeyUp, { capture: true })
    window.removeEventListener('blur', handleWindowBlur)
    window.removeEventListener('focus', handleWindowFocus)
    document.removeEventListener('visibilitychange', handleVisibilityChange)
    window.removeEventListener('resize', detectDevTools)
  })

  return {
    isObscured,
    dismissObscured,
  }
}
