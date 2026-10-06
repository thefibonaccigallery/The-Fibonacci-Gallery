export interface SeoMetaOptions {
  title?: string
  description?: string
  keywords?: string[]
  canonicalUrl?: string
  ogImage?: string
  ogType?: 'website' | 'article' | 'profile'
  noIndex?: boolean
  jsonLd?: Record<string, unknown> | Array<Record<string, unknown>>
}

export interface BreadcrumbItem {
  name: string
  url: string
}

export interface CourseSchemaInput {
  uuid: string
  title: string
  description: string | null
  thumbnail_url: string | null
  price: number
  level: string
  published_at?: string
  totalLessons?: number
  length_seconds?: number
}

export interface FaqSchemaItem {
  question: string
  answer: string
}

declare module 'vue-router' {
  interface RouteMeta {
    title?: string
    description?: string
    keywords?: string[]
    noIndex?: boolean
    requiresAuth?: boolean
    requiresAdmin?: boolean
  }
}
