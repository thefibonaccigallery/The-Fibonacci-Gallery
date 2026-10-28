export type GtagCommand = 'config' | 'set' | 'js' | 'event'

export interface GtagEventParams {
  page_title?: string
  page_location?: string
  page_path?: string
  send_page_view?: boolean
  [key: string]: unknown
}

export type GtagFunction = (
  command: GtagCommand,
  targetIdOrEventName: string | Date,
  params?: GtagEventParams | Record<string, unknown>
) => void

declare global {
  interface Window {
    dataLayer?: unknown[]
    gtag?: GtagFunction
  }
}
