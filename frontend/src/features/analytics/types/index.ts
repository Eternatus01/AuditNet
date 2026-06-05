export interface AnalyticsDataPoint {
  id: number;
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
  latestAuditId: number;
}

export interface AnalyticsFilters {
  url: string;
  dateFrom?: string;
  dateTo?: string;
  limit?: number;
}

export interface AuditScoreDelta {
  current: number;
  previous: number;
  delta: number;
}

export interface AuditMetricDelta extends AuditScoreDelta {
  unit: string;
}

export interface AuditDiff {
  has_previous: boolean;
  audit_id: number;
  previous_audit_id: number | null;
  previous_audited_at?: string;
  score_deltas: Record<string, AuditScoreDelta>;
  metric_deltas: Record<string, AuditMetricDelta>;
  explanations: string[];
}
