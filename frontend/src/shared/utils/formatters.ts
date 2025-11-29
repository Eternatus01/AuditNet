export const formatLCP = (value: number | null): string => {
  if (value === null) return "--";
  return `${(value / 1000).toFixed(2)} s`;
};

export const formatFID = (value: number | null): string => {
  if (value === null) return "--";
  return `${Math.round(value)} ms`;
};

export const formatCLS = (value: number | null): string => {
  if (value === null) return "--";
  return value.toFixed(3);
};

