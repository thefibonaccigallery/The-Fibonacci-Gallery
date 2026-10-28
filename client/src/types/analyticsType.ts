export type AnalyticsRange = 'today' | '7_days' | '30_days' | '90_days'

export interface DailyVisitorMetric {
  date: string
  visitors: number
  pageViews: number
}

export interface TopPageItem {
  path: string
  title?: string
  views: number
}

export interface TopCountryItem {
  country: string
  countryCode?: string
  visitors: number
}

export interface TopSourceItem {
  source: string
  visitors: number
}

export interface DeviceSplitItem {
  device: string
  visitors: number
  percentage: number
}

export interface AnalyticsData {
  totalVisitors: number
  totalPageViews: number
  dailyMetrics: DailyVisitorMetric[]
  topPages: TopPageItem[]
  topCountries: TopCountryItem[]
  topSources: TopSourceItem[]
  deviceSplit: DeviceSplitItem[]
  hasData: boolean
  propertyUrl?: string
}

export interface AnalyticsResponse {
  success: boolean
  range: AnalyticsRange
  data: AnalyticsData
}

export interface AnalyticsRangeOption {
  value: AnalyticsRange
  label: string
}
