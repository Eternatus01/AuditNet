export interface AnalyticsDataPoint {
  date: string;
  performance: number | null;
  accessibility: number | null;
  best_practices: number | null;
  seo: number | null;
  lcp: number | null;
  fid: number | null;
  cls: number | null;
}

export interface SiteAnalytics {
  url: string;
  data: AnalyticsDataPoint[];
  totalAudits: number;
  firstAudit: string;
  lastAudit: string;
}

export interface AnalyticsFilters {
  url: string;
  dateFrom?: string;
  dateTo?: string;
  limit?: number;
}
