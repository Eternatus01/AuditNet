export const formatLCP = (value: number | null): string => {
  if (value === null) return "--";
  // LCP приходит в миллисекундах, конвертируем в секунды
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

