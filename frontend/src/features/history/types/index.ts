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

