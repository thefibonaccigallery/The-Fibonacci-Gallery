import type {
  SeoMetaOptions,
  CourseSchemaInput,
  FaqSchemaItem,
  BreadcrumbItem,
} from '@/types/seoType'

const SITE_NAME = 'The Fibonacci Gallery'
const SHORT_SITE_NAME = 'Fibonacci Gallery'
const BASE_URL = 'https://the-fibonacci-gallery.com'
const DEFAULT_OG_IMAGE = `${BASE_URL}/og-image.png`
const DEFAULT_TITLE = 'The Fibonacci Gallery | Elite Trading Academy'
const DEFAULT_DESCRIPTION =
  'Master institutional trading, The Big Five strategy, and risk management across Forex, Gold, and Crypto. Built for global traders.'

/**
 * Format page title to stay within the recommended 50-60 character limit
 */
function formatSeoTitle(rawTitle?: string): string {
  if (!rawTitle) return DEFAULT_TITLE

  if (rawTitle.includes('Fibonacci')) {
    return rawTitle.length <= 60 ? rawTitle : rawTitle.slice(0, 57) + '...'
  }

  const withFullBrand = `${rawTitle} | ${SITE_NAME}`
  if (withFullBrand.length <= 60) return withFullBrand

  const withShortBrand = `${rawTitle} | ${SHORT_SITE_NAME}`
  if (withShortBrand.length <= 60) return withShortBrand

  return rawTitle.length <= 60 ? rawTitle : rawTitle.slice(0, 57) + '...'
}

/**
 * Format description to stay within 120-140 characters for social card visibility
 */
function formatSeoDescription(rawDesc?: string): string {
  if (!rawDesc) return DEFAULT_DESCRIPTION
  if (rawDesc.length <= 140) return rawDesc
  return rawDesc.slice(0, 137) + '...'
}

/**
 * Helper to update or create an HTML meta tag
 */
function setMetaTag(attributeName: 'name' | 'property', attributeValue: string, content: string) {
  if (typeof document === 'undefined') return
  let element = document.querySelector(`meta[${attributeName}="${attributeValue}"]`)
  if (!element) {
    element = document.createElement('meta')
    element.setAttribute(attributeName, attributeValue)
    document.head.appendChild(element)
  }
  element.setAttribute('content', content)
}

/**
 * Helper to update or create a link tag (e.g. canonical)
 */
function setLinkTag(rel: string, href: string) {
  if (typeof document === 'undefined') return
  let element = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement | null
  if (!element) {
    element = document.createElement('link')
    element.setAttribute('rel', rel)
    document.head.appendChild(element)
  }
  element.setAttribute('href', href)
}

/**
 * Helper to update or inject JSON-LD script
 */
function setJsonLd(data: Record<string, unknown> | Array<Record<string, unknown>>) {
  if (typeof document === 'undefined') return
  const scriptId = 'seo-jsonld'
  let script = document.getElementById(scriptId) as HTMLScriptElement | null
  if (!script) {
    script = document.createElement('script')
    script.id = scriptId
    script.type = 'application/ld+json'
    document.head.appendChild(script)
  }
  script.textContent = JSON.stringify(data, null, 2)
}

export function useSeoMeta() {
  /**
   * Apply SEO metadata to the current document
   */
  const setSeoMeta = (options: SeoMetaOptions = {}) => {
    if (typeof document === 'undefined') return

    // 1. Page Title (strictly <= 60 chars)
    const titleText = formatSeoTitle(options.title)
    document.title = titleText

    // 2. Standard Meta (strictly <= 140 chars)
    const description = formatSeoDescription(options.description)
    setMetaTag('name', 'description', description)

    if (options.keywords && options.keywords.length > 0) {
      setMetaTag('name', 'keywords', options.keywords.join(', '))
    }

    // 3. Robots
    if (options.noIndex) {
      setMetaTag('name', 'robots', 'noindex, nofollow')
    } else {
      setMetaTag(
        'name',
        'robots',
        'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
      )
    }

    // 4. Canonical URL
    const canonical = options.canonicalUrl
      ? options.canonicalUrl.startsWith('http')
        ? options.canonicalUrl
        : `${BASE_URL}${options.canonicalUrl}`
      : `${BASE_URL}${window.location.pathname}`
    setLinkTag('canonical', canonical)

    // 5. Open Graph
    const ogImage = options.ogImage || DEFAULT_OG_IMAGE
    const ogType = options.ogType || 'website'

    setMetaTag('property', 'og:title', titleText)
    setMetaTag('property', 'og:description', description)
    setMetaTag('property', 'og:image', ogImage)
    setMetaTag('property', 'og:url', canonical)
    setMetaTag('property', 'og:type', ogType)
    setMetaTag('property', 'og:site_name', SITE_NAME)
    setMetaTag('property', 'og:locale', 'en_US')

    // 6. Twitter Card
    setMetaTag('name', 'twitter:card', 'summary_large_image')
    setMetaTag('name', 'twitter:title', titleText)
    setMetaTag('name', 'twitter:description', description)
    setMetaTag('name', 'twitter:image', ogImage)

    // 7. Dynamic JSON-LD Structured Data
    if (options.jsonLd) {
      setJsonLd(options.jsonLd)
    }
  }

  /**
   * Generate Educational Organization & WebSite JSON-LD Schema
   */
  const generateOrganizationSchema = () => {
    return {
      '@context': 'https://schema.org',
      '@graph': [
        {
          '@type': 'EducationalOrganization',
          '@id': `${BASE_URL}/#organization`,
          name: SITE_NAME,
          url: BASE_URL,
          logo: `${BASE_URL}/android-chrome-512x512.png`,
          image: DEFAULT_OG_IMAGE,
          description: DEFAULT_DESCRIPTION,
          areaServed: [
            { '@type': 'City', name: 'Dubai' },
            { '@type': 'Country', name: 'United Arab Emirates' },
            { '@type': 'Continent', name: 'Europe' },
            { '@type': 'Continent', name: 'Asia' },
            { '@type': 'AdministrativeArea', name: 'Worldwide' },
          ],
          knowsAbout: [
            'Technical Analysis',
            'Fibonacci Retracement and Confluence',
            'The Big Five Strategy',
            'Forex Trading',
            'Gold (XAU/USD) Price Action',
            'Risk Management',
            'Capital Preservation',
            'Institutional Market Structure',
            'Trading Psychology',
          ],
        },
        {
          '@type': 'WebSite',
          '@id': `${BASE_URL}/#website`,
          url: BASE_URL,
          name: SITE_NAME,
          description:
            'Where Elite Strategy Meets Artistic Precision. International Financial Education & Trading Academy.',
          publisher: {
            '@id': `${BASE_URL}/#organization`,
          },
          potentialAction: {
            '@type': 'SearchAction',
            target: `${BASE_URL}/explore?search={search_term_string}`,
            'query-input': 'required name=search_term_string',
          },
        },
      ],
    }
  }

  /**
   * Generate Course JSON-LD Schema
   */
  const generateCourseSchema = (course: CourseSchemaInput) => {
    return {
      '@context': 'https://schema.org',
      '@type': 'Course',
      name: course.title,
      description:
        course.description ||
        `Master trading with ${course.title} at ${SITE_NAME}. Professional curriculum designed for international financial market mastery.`,
      provider: {
        '@type': 'EducationalOrganization',
        name: SITE_NAME,
        sameAs: BASE_URL,
      },
      educationalLevel: course.level || 'Intermediate',
      offers: {
        '@type': 'Offer',
        price: course.price.toFixed(2),
        priceCurrency: 'USD',
        category: 'Financial Education',
        availability: 'https://schema.org/InStock',
        url: `${BASE_URL}/courses/${course.uuid}/details`,
      },
      hasCourseInstance: {
        '@type': 'CourseInstance',
        courseMode: 'online',
        inLanguage: 'en',
      },
      image: course.thumbnail_url || DEFAULT_OG_IMAGE,
    }
  }

  /**
   * Generate FAQPage JSON-LD Schema
   */
  const generateFaqSchema = (faqs: FaqSchemaItem[]) => {
    return {
      '@context': 'https://schema.org',
      '@type': 'FAQPage',
      mainEntity: faqs.map((faq) => ({
        '@type': 'Question',
        name: faq.question,
        acceptedAnswer: {
          '@type': 'Answer',
          text: faq.answer,
        },
      })),
    }
  }

  /**
   * Generate BreadcrumbList JSON-LD Schema
   */
  const generateBreadcrumbSchema = (items: BreadcrumbItem[]) => {
    return {
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: items.map((item, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        name: item.name,
        item: item.url.startsWith('http') ? item.url : `${BASE_URL}${item.url}`,
      })),
    }
  }

  return {
    setSeoMeta,
    generateOrganizationSchema,
    generateCourseSchema,
    generateFaqSchema,
    generateBreadcrumbSchema,
    SITE_NAME,
    BASE_URL,
    DEFAULT_OG_IMAGE,
    DEFAULT_DESCRIPTION,
  }
}
