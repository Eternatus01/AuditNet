export interface SecurityAuditData {
  headers: Record<string, boolean | string>;
  sensitive_files: Record<string, boolean>;
  directory_listing: Record<string, boolean>;
  scripts_info: Array<{
    src: string;
    crossorigin?: boolean;
    integrity?: string;
  }>;
  robots_txt: boolean;
  sitemap_xml: boolean;
}

export interface AuditRecommendation {
  id: number;
  category: string;
  audit_id_key: string;
  title: string;
  description: string | null;
  score: number;
  score_display_mode: string | null;
  display_value: string | null;
  details: any;
  numeric_value: number | null;
  numeric_unit: string | null;
}

export interface Audit {
  id: number;
  url: string;
  status: string;
  performance: number | null;
  accessibility: number | null;
  best_practices: number | null;
  seo: number | null;
  lcp: number | null;
  fid: number | null;
  cls: number | null;
  fcp: number | null;
  tbt: number | null;
  speed_index: number | null;
  audited_at: string | null;
  created_at: string;
  updated_at: string;
  security_audit?: SecurityAuditData | null;
  recommendations?: AuditRecommendation[];
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  total: number;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

export interface AuditDetailResponse {
  success: boolean;
  data: Audit;
}

export type ScoreClass = 'good' | 'average' | 'poor' | 'unknown';

export interface ApiError {
  response?: {
    status: number;
    data: {
      message?: string;
      error?: string;
    };
  };
  message: string;
}

export function isApiError(error: unknown): error is ApiError {
  return (
    typeof error === "object" &&
    error !== null &&
    "message" in error &&
    typeof (error as ApiError).message === "string"
  );
}

