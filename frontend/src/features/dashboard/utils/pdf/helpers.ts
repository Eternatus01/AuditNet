import { COLORS } from "./constants";
import type { MetricStatus } from "./types";

export const getScoreColor = (score: number | null | undefined): string => {
  if (score === null || score === undefined) return COLORS.muted;
  if (score >= 90) return COLORS.good;
  if (score >= 50) return COLORS.moderate;
  return COLORS.poor;
};

export const getScoreLabel = (score: number | null | undefined): string => {
  if (score === null || score === undefined) return "Нет данных";
  if (score >= 90) return "Отлично";
  if (score >= 50) return "Требует улучшения";
  return "Плохо";
};

export const getLcpStatus = (v: number | null): MetricStatus => {
  if (v === null) return { color: COLORS.muted, label: "Нет данных" };
  if (v < 2.5) return { color: COLORS.good, label: "Хорошо" };
  if (v <= 4.0) return { color: COLORS.moderate, label: "Требует улучшения" };
  return { color: COLORS.poor, label: "Плохо" };
};

export const getFidStatus = (v: number | null): MetricStatus => {
  if (v === null) return { color: COLORS.muted, label: "Нет данных" };
  if (v < 200) return { color: COLORS.good, label: "Хорошо" };
  if (v <= 500) return { color: COLORS.moderate, label: "Требует улучшения" };
  return { color: COLORS.poor, label: "Плохо" };
};

export const getClsStatus = (v: number | null): MetricStatus => {
  if (v === null) return { color: COLORS.muted, label: "Нет данных" };
  if (v < 0.1) return { color: COLORS.good, label: "Хорошо" };
  if (v <= 0.25) return { color: COLORS.moderate, label: "Требует улучшения" };
  return { color: COLORS.poor, label: "Плохо" };
};

export const getFcpStatus = (v: number | null): MetricStatus => {
  if (v === null) return { color: COLORS.muted, label: "Нет данных" };
  if (v < 1.8) return { color: COLORS.good, label: "Хорошо" };
  if (v <= 3.0) return { color: COLORS.moderate, label: "Требует улучшения" };
  return { color: COLORS.poor, label: "Плохо" };
};

export const getTbtStatus = (v: number | null): MetricStatus => {
  if (v === null) return { color: COLORS.muted, label: "Нет данных" };
  if (v < 0.2) return { color: COLORS.good, label: "Хорошо" };
  if (v <= 0.6) return { color: COLORS.moderate, label: "Требует улучшения" };
  return { color: COLORS.poor, label: "Плохо" };
};

export const getSpeedIndexStatus = (v: number | null): MetricStatus => {
  if (v === null) return { color: COLORS.muted, label: "Нет данных" };
  if (v < 3.4) return { color: COLORS.good, label: "Хорошо" };
  if (v <= 5.8) return { color: COLORS.moderate, label: "Требует улучшения" };
  return { color: COLORS.poor, label: "Плохо" };
};

export const formatSeconds = (v: number | null | undefined): string =>
  v === null || v === undefined ? "—" : `${v.toFixed(2)} с`;

export const formatMs = (v: number | null | undefined): string =>
  v === null || v === undefined ? "—" : `${Math.round(v)} мс`;

export const formatCls = (v: number | null | undefined): string =>
  v === null || v === undefined ? "—" : v.toFixed(3);

export const formatDate = (iso?: string | null): string => {
  if (!iso) return new Date().toLocaleString("ru-RU");
  const d = new Date(iso);
  if (isNaN(d.getTime())) return iso;
  return d.toLocaleString("ru-RU", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

export const extractDomain = (url: string): string => {
  try {
    return new URL(url.startsWith("http") ? url : `https://${url}`).hostname;
  } catch {
    return url.replace(/^https?:\/\//, "").split("/")[0] || url;
  }
};

export const sanitizeFilename = (value: string): string =>
  value.replace(/[^a-zA-Zа-яА-Я0-9_.-]/g, "_").slice(0, 60);
