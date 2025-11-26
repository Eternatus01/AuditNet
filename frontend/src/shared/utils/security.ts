/**
 * Словарь для преобразования технических названий заголовков в читаемые
 */
const HEADER_NAMES: Record<string, string> = {
  "strict-transport-security": "HSTS",
  "content-security-policy": "CSP",
  "x-frame-options": "X-Frame-Options",
  "referrer-policy": "Referrer-Policy",
  "permissions-policy": "Permissions-Policy",
  "x-content-type-options": "X-Content-Type-Options",
  "x-xss-protection": "X-XSS-Protection",
  "cache-control": "Cache-Control",
  "pragma": "Pragma",
  "expires": "Expires",
  "access-control-allow-origin": "CORS Origin",
};

export const formatHeaderName = (name: string): string => {
  return HEADER_NAMES[name] || name;
};

