export interface Audit {
  id: number;
  url: string;
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
  created_at: string;
  updated_at: string;
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

