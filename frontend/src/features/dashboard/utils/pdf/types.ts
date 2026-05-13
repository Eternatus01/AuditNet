export interface PdfScores {
  performance: number | null;
  accessibility: number | null;
  bestPractices: number | null;
  seo: number | null;
}

export interface PdfVitals {
  lcp: number | null;
  fid: number | null;
  cls: number | null;
  fcp: number | null;
  tbt: number | null;
  speedIndex: number | null;
}

export interface PdfSecurityAudit {
  checked_url?: string;
  host?: string;
  headers?: Record<string, boolean | string>;
  sensitive_files?: Record<string, boolean>;
  directory_listing?: Record<string, boolean>;
  robots_txt?: boolean | string | null;
  sitemap_xml?: boolean;
  scripts_info?: unknown;
}

export interface PdfRecommendation {
  id: number;
  category: string;
  audit_id_key: string;
  title: string;
  description: string | null;
  score: number;
  score_display_mode: string | null;
  display_value: string | null;
  details?: unknown;
  numeric_value: number | null;
  numeric_unit: string | null;
}

export interface AuditReportData {
  url: string;
  auditedAt?: string | null;
  scores: PdfScores;
  vitals: PdfVitals;
  securityAudit?: PdfSecurityAudit | null;
  recommendations?: PdfRecommendation[];
}

export interface MetricStatus {
  color: string;
  label: string;
}
