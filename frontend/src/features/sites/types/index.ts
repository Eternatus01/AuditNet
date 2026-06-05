export interface MonitoredSiteLastAudit {
  id: number;
  status: string;
  performance: number | null;
  accessibility: number | null;
  best_practices: number | null;
  seo: number | null;
  audited_at: string | null;
}

export interface MonitoredSite {
  id: number;
  name: string | null;
  url: string;
  schedule_day: number;
  is_active: boolean;
  last_run_at: string | null;
  last_audit_id: number | null;
  created_at: string;
  last_audit?: MonitoredSiteLastAudit | null;
}

export interface CreateSitePayload {
  url: string;
  name?: string | null;
  schedule_day: number;
}

export interface UpdateSitePayload {
  name?: string | null;
  schedule_day?: number;
  is_active?: boolean;
}

export interface SitesListResponse {
  success: boolean;
  data: MonitoredSite[];
}

export interface SiteResponse {
  success: boolean;
  message?: string;
  data: MonitoredSite;
}

export interface SiteActionResponse {
  success: boolean;
  message?: string;
}

export const WEEKDAYS: { value: number; label: string; short: string }[] = [
  { value: 1, label: 'Понедельник', short: 'Пн' },
  { value: 2, label: 'Вторник', short: 'Вт' },
  { value: 3, label: 'Среда', short: 'Ср' },
  { value: 4, label: 'Четверг', short: 'Чт' },
  { value: 5, label: 'Пятница', short: 'Пт' },
  { value: 6, label: 'Суббота', short: 'Сб' },
  { value: 7, label: 'Воскресенье', short: 'Вс' },
];
