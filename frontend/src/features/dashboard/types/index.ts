export interface LighthouseScores {
  performance: number;
  accessibility: number;
  bestPractices: number;
  seo: number;
}

export interface CoreWebVitalsMetrics {
  lcp: number;
  fid: number;
  cls: number;
}

export interface AdditionalMetrics {
  fcp: number;
  tbt: number;
  speedIndex: number;
}

export interface LighthouseData extends LighthouseScores, CoreWebVitalsMetrics, AdditionalMetrics {}

export interface AuditJobResponse {
  audit_id: number;
  status: string;
  url: string;
}

export interface AuditResource {
  id: number;
  user_id: number;
  url: string;
  status: string;
  scores: {
    performance: number | null;
    accessibility: number | null;
    best_practices: number | null;
    seo: number | null;
  };
  core_web_vitals: {
    lcp: number | null;
    fid: number | null;
    cls: number | null;
  };
  additional_metrics: {
    fcp: number | null;
    tbt: number | null;
    speed_index: number | null;
  };
  error_message: string | null;
  audited_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface AuditStatusResponse {
  success: boolean;
  data: AuditResource;
}

export interface SecurityHeaders {
  [key: string]: boolean | string;
}

export interface SensitiveFiles {
  [key: string]: boolean;
}

export interface DirectoryListing {
  [key: string]: boolean;
}

export interface ScriptInfo {
  src: string;
  async?: boolean;
  defer?: boolean;
}

export interface SecurityAudit {
  headers: SecurityHeaders;
  sensitive_files: SensitiveFiles;
  directory_listing: DirectoryListing;
  robots_txt: boolean;
  sitemap_xml: boolean;
  scripts_info: ScriptInfo[];
}

export interface AnalyzeWebsiteResponse {
  success: boolean;
  message?: string;
  data: AuditJobResponse | LighthouseData;
}

export interface SecurityAuditResponse extends SecurityAudit {
  error?: string;
}

export interface ScoreDescriptions {
  performance: string;
  accessibility: string;
  bestPractices: string;
  seo: string;
}

export interface CoreWebVitalsDescriptions {
  section: string;
  lcp: string;
  fid: string;
  cls: string;
}

export interface SecurityDescriptions {
  headers: string;
  sensitiveFiles: string;
  directoryListing: string;
  additional: string;
}

export interface AuditDescriptions {
  scores: ScoreDescriptions;
  coreWebVitals: CoreWebVitalsDescriptions;
  security: SecurityDescriptions;
}

export type MetricStatus = 'good' | 'moderate' | 'poor';

export type ScoreDisplay = number | string;

export interface ApiError {
  response?: {
    status: number;
    data: {
      message?: string;
      error?: string;
      errors?: {
        [field: string]: string[];
      };
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

