export const formatLCP = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return `${(value / 1000).toFixed(2)} s`;
};

export const formatFID = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return `${Math.round(value)} ms`;
};

export const formatCLS = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return "--";
  return value.toFixed(3);
};

