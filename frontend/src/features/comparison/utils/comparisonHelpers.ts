import {
  formatCLS,
  formatFCP,
  formatFID,
  formatLCP,
  formatSpeedIndex,
  formatTBT,
} from "@/shared/utils/formatters";
import type { GuestAuditData } from "@/features/dashboard/types";

export type ComparisonDirection = "higher" | "lower";

export interface ComparisonResult {
  url: string;
  data: GuestAuditData | null;
  error: string | null;
}

export interface ComparisonMetric {
  key: string;
  label: string;
  description: string;
  direction: ComparisonDirection;
  getValue: (_data: GuestAuditData) => number | null;
  format: (_value: number | null, _data: GuestAuditData) => string;
}

const getSecurityHeadersCount = (data: GuestAuditData): number | null => {
  const headers = data.security_audit?.headers;
  if (!headers) return null;
  return Object.values(headers).filter((value) => value === true || Boolean(value)).length;
};

const getSecurityHeadersTotal = (data: GuestAuditData): number => {
  return Object.keys(data.security_audit?.headers ?? {}).length;
};

const getSensitiveFilesCount = (data: GuestAuditData): number | null => {
  const sensitiveFiles = data.security_audit?.sensitive_files;
  if (!sensitiveFiles) return null;
  return Object.values(sensitiveFiles).filter(Boolean).length;
};

const formatScoreWithMax = (value: number | null): string => {
  if (value === null) return "--";
  return `${Math.round(value)} / 100`;
};

const formatSecurityHeaders = (value: number | null, data: GuestAuditData): string => {
  if (value === null) return "--";
  return `${value} / ${getSecurityHeadersTotal(data)}`;
};

const formatCount = (value: number | null): string => {
  if (value === null) return "--";
  return value.toString();
};

export const comparisonMetrics: ComparisonMetric[] = [
  {
    key: "performance",
    label: "Производительность",
    description: "Performance",
    direction: "higher",
    getValue: (data) => data.performance,
    format: formatScoreWithMax,
  },
  {
    key: "accessibility",
    label: "Доступность",
    description: "Accessibility",
    direction: "higher",
    getValue: (data) => data.accessibility,
    format: formatScoreWithMax,
  },
  {
    key: "best-practices",
    label: "Стандарты качества",
    description: "Best Practices",
    direction: "higher",
    getValue: (data) => data.best_practices,
    format: formatScoreWithMax,
  },
  {
    key: "seo",
    label: "SEO",
    description: "Search Engine Optimization",
    direction: "higher",
    getValue: (data) => data.seo,
    format: formatScoreWithMax,
  },
  {
    key: "lcp",
    label: "LCP",
    description: "Largest Contentful Paint",
    direction: "lower",
    getValue: (data) => data.lcp,
    format: (value) => formatLCP(value),
  },
  {
    key: "inp",
    label: "INP",
    description: "Interaction to Next Paint",
    direction: "lower",
    getValue: (data) => data.fid,
    format: (value) => formatFID(value),
  },
  {
    key: "cls",
    label: "CLS",
    description: "Cumulative Layout Shift",
    direction: "lower",
    getValue: (data) => data.cls,
    format: (value) => formatCLS(value),
  },
  {
    key: "fcp",
    label: "FCP",
    description: "First Contentful Paint",
    direction: "lower",
    getValue: (data) => data.fcp,
    format: (value) => formatFCP(value),
  },
  {
    key: "tbt",
    label: "TBT",
    description: "Total Blocking Time",
    direction: "lower",
    getValue: (data) => data.tbt,
    format: (value) => formatTBT(value),
  },
  {
    key: "speed-index",
    label: "Speed Index",
    description: "Скорость визуальной загрузки",
    direction: "lower",
    getValue: (data) => data.speed_index,
    format: (value) => formatSpeedIndex(value),
  },
  {
    key: "security-headers",
    label: "Security headers",
    description: "Найденные защитные заголовки",
    direction: "higher",
    getValue: getSecurityHeadersCount,
    format: formatSecurityHeaders,
  },
  {
    key: "sensitive-files",
    label: "Публичные sensitive files",
    description: "Чем меньше, тем лучше",
    direction: "lower",
    getValue: getSensitiveFilesCount,
    format: formatCount,
  },
];

export const getHostname = (url: string): string => {
  try {
    return new URL(url).hostname.replace(/^www\./, "");
  } catch {
    return url;
  }
};

export const getAverageScore = (data: GuestAuditData): number | null => {
  const scores = [data.performance, data.accessibility, data.best_practices, data.seo].filter(
    (score): score is number => typeof score === "number"
  );

  if (scores.length === 0) return null;
  return scores.reduce((sum, score) => sum + score, 0) / scores.length;
};

export const getScoreStatus = (value: number | null): "good" | "moderate" | "poor" | "unknown" => {
  if (value === null) return "unknown";
  if (value >= 90) return "good";
  if (value >= 50) return "moderate";
  return "poor";
};
