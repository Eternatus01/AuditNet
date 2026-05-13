export const COLORS = {
  primary: "#7c3aed",
  primaryDark: "#5b21b6",
  good: "#10b981",
  moderate: "#f59e0b",
  poor: "#ef4444",
  text: "#1f2937",
  muted: "#6b7280",
  border: "#d1d5db",
  headerBg: "#f3f4f6",
  subtle: "#f9fafb",
} as const;

export const CATEGORY_LABELS: Record<string, string> = {
  performance: "Производительность",
  accessibility: "Доступность",
  "best-practices": "Стандарты качества",
  seo: "SEO",
};

export const SECURITY_HEADER_LABELS: Record<string, string> = {
  "strict-transport-security": "Strict-Transport-Security (HSTS)",
  "content-security-policy": "Content-Security-Policy",
  "x-frame-options": "X-Frame-Options",
  "x-content-type-options": "X-Content-Type-Options",
  "referrer-policy": "Referrer-Policy",
  "permissions-policy": "Permissions-Policy",
};

export const RECOMMENDATION_GROUP_ORDER = [
  "performance",
  "accessibility",
  "best-practices",
  "seo",
];
