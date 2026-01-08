export const formatLCP = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return `${value.toFixed(2)} s`;
};

export const formatFID = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "N/A";
  return `${Math.round(value)} ms`;
};

export const formatCLS = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return value.toFixed(3);
};

export const formatTBT = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return `${value.toFixed(2)} s`;
};

export const formatFCP = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return `${value.toFixed(2)} s`;
};

export const formatSpeedIndex = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return `${value.toFixed(2)} s`;
};

