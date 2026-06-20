<template>
  <div class="metrics-section dashboard-results" :class="{ hidden: !isSecurityReady }">
    <h2 class="section-title">Проверка безопасности</h2>
    <p class="section-subtitle">
      Практический чеклист: HTTPS, качество заголовков, cookies, утечки файлов и внешние ресурсы.
    </p>

    <div class="info-notice security-notice">
      <IconLucideAlertCircle class="notice-icon" />
      <div class="notice-content">
        <strong>Обратите внимание:</strong> это быстрый HTTP-аудит, а не pentest. Он показывает
        реальные настройки, которые разработчик может проверить и исправить сразу.
      </div>
    </div>

    <div v-if="securityError" class="error-message mb-2">
      {{ securityError }}
    </div>

    <template v-if="securityAudit">
      <div class="security-score-panel" :class="overallClass">
        <div>
          <span class="score-eyebrow">Итог по безопасности</span>
          <h3>{{ overallTitle }}</h3>
          <p>{{ overallSubtitle }}</p>
        </div>
        <div class="risk-count">
          <span>{{ priorityRecommendations.length }}</span>
          <small>важных пункта</small>
        </div>
      </div>

      <div v-if="priorityRecommendations.length" class="recommendations-panel">
        <div
          v-for="item in priorityRecommendations"
          :key="`${item.severity}-${item.title}`"
          class="recommendation-row"
          :class="`severity-${item.severity}`"
        >
          <IconLucideAlertTriangle />
          <div>
            <strong>{{ item.title }}</strong>
            <p>{{ item.fix }}</p>
          </div>
        </div>
      </div>

      <div class="security-board">
        <article
          v-for="category in securityCategories"
          :key="category.key"
          class="security-panel"
          :class="`panel-${category.status}`"
        >
          <div class="panel-header">
            <div class="panel-icon">
              <IconLucideShieldCheck v-if="category.key === 'transport'" />
              <IconLucideLock v-else-if="category.key === 'headers'" />
              <IconLucideCookie v-else-if="category.key === 'cookies'" />
              <IconLucideFileText v-else-if="category.key === 'files'" />
              <IconLucideFolder v-else-if="category.key === 'listing'" />
              <IconLucideCode2 v-else />
            </div>
            <div class="panel-title">
              <h3>{{ category.title }}</h3>
              <p>{{ category.summary }}</p>
            </div>
            <span class="panel-status" :class="category.status">
              {{ statusLabel(category.status) }}
            </span>
          </div>

          <div v-if="category.meta.length" class="panel-meta">
            <span v-for="meta in category.meta" :key="meta">{{ meta }}</span>
          </div>

          <div v-if="category.issues.length" class="issue-list">
            <details
              v-for="issue in category.issues"
              :key="issue.title"
              class="issue-row"
              :class="`issue-${issue.status}`"
            >
              <summary>
                <span class="issue-title">{{ issue.title }}</span>
                <span class="issue-badge" :class="issue.status">
                  {{ statusLabel(issue.status) }}
                </span>
              </summary>
              <p>{{ issue.details }}</p>
              <div v-if="issue.fix" class="issue-fix">
                <strong>Как исправить:</strong>
                <span>{{ issue.fix }}</span>
              </div>
            </details>
          </div>

          <div v-else class="panel-ok">
            {{ category.okText }}
          </div>
        </article>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { formatHeaderName } from "@/shared/utils/security";
import IconLucideLock from "~icons/lucide/lock";
import IconLucideFileText from "~icons/lucide/file-text";
import IconLucideFolder from "~icons/lucide/folder";
import IconLucideAlertTriangle from "~icons/lucide/alert-triangle";
import IconLucideAlertCircle from "~icons/lucide/alert-circle";
import IconLucideShieldCheck from "~icons/lucide/shield-check";
import IconLucideCookie from "~icons/lucide/cookie";
import IconLucideCode2 from "~icons/lucide/code-2";

import type { SecurityAudit, AuditDescriptions, SecurityRecommendation } from "../types";

const props = defineProps<{
  securityAudit: SecurityAudit | null;
  securityError: string;
  isSecurityReady: boolean;
  descriptions: AuditDescriptions;
  isExpanded: (_key: string) => boolean;
}>();

type CheckStatus = "ok" | "warn" | "bad";
type SecurityIssue = {
  title: string;
  status: CheckStatus;
  details: string;
  fix?: string;
};

type SecurityCategory = {
  key: string;
  title: string;
  status: CheckStatus;
  summary: string;
  okText: string;
  meta: string[];
  issues: SecurityIssue[];
};

const statusLabel = (status: CheckStatus) => {
  if (status === "ok") return "OK";
  if (status === "warn") return "Внимание";
  return "Исправить";
};

const priorityRecommendations = computed<SecurityRecommendation[]>(() => {
  const items = props.securityAudit?.security_recommendations || [];
  const weight: Record<SecurityRecommendation["severity"], number> = {
    critical: 0,
    high: 1,
    medium: 2,
    low: 3,
  };
  return [...items].sort((a, b) => weight[a.severity] - weight[b.severity]).slice(0, 6);
});

const overallClass = computed(() => {
  const hasCritical = priorityRecommendations.value.some((item) => item.severity === "critical");
  const hasHigh = priorityRecommendations.value.some((item) => item.severity === "high");
  if (hasCritical || hasHigh) return "risk-high";
  if (priorityRecommendations.value.length > 0) return "risk-medium";
  return "risk-low";
});

const overallTitle = computed(() => {
  if (overallClass.value === "risk-high") return "Есть важные риски";
  if (overallClass.value === "risk-medium") return "Есть улучшения";
  return "Критичных проблем не найдено";
});

const overallSubtitle = computed(() => {
  if (overallClass.value === "risk-high") {
    return "Сначала исправьте пункты с высоким и критическим приоритетом.";
  }
  if (overallClass.value === "risk-medium") {
    return "Критичных находок нет, но есть настройки, которые стоит усилить.";
  }
  return "Базовые настройки выглядят хорошо. Проверьте рекомендации ниже для полноты.";
});

const redirectStatus = computed<CheckStatus>(() => {
  const value = props.securityAudit?.https?.http_to_https_redirect;
  if (value === true) return "ok";
  if (value === null || value === undefined) return "warn";
  return "bad";
});

const redirectDetails = computed(() => {
  const https = props.securityAudit?.https;
  if (https?.http_to_https_redirect === true) return "HTTP-версия перенаправляет на HTTPS.";
  if (https?.http_to_https_redirect === false) return `HTTP ответил статусом ${https.http_status || "без редиректа"}.`;
  return "Не удалось проверить HTTP-редирект.";
});

const mixedContentDetails = computed(() => {
  const mixed = props.securityAudit?.mixed_content;
  if (!mixed?.checked) return "Проверяется только для HTTPS-страниц.";
  if (!mixed.count) return "Небезопасные http:// ресурсы не найдены.";
  return `Найдено http:// ресурсов: ${mixed.count}. Пример: ${mixed.examples?.[0] || "ресурс не указан"}`;
});

const scriptIntegrityDetails = computed(() => {
  const sri = props.securityAudit?.script_integrity;
  if (!sri?.external_count) return "Внешние JS-скрипты не найдены.";
  if (!sri.without_integrity_count) return "У внешних JS есть integrity.";
  return `Без integrity: ${sri.without_integrity_count} из ${sri.external_count}.`;
});

const serverExposureDetails = computed(() => {
  const exposure = props.securityAudit?.server_exposure;
  if (!exposure?.issues?.length) return "Server/X-Powered-By не раскрывают лишние детали.";
  const values = [exposure.server, exposure.x_powered_by].filter(Boolean).join(", ");
  return values ? `Раскрывается: ${values}` : exposure.issues.join(", ");
});

const headerIssues = computed(() => {
  const headers = props.securityAudit?.header_analysis || {};
  return Object.entries(headers).filter(([, check]) => check.status !== "ok");
});

const headersCount = computed(() => {
  return Object.keys(props.securityAudit?.header_analysis || {}).length;
});

const weakCookies = computed(() => {
  return (props.securityAudit?.cookie_flags?.cookies || []).filter(
    (cookie) => cookie.issues.length > 0
  );
});

const leakedFiles = computed<[string, boolean][]>(() => {
  const files = props.securityAudit?.sensitive_files || {};
  return Object.entries(files).filter(([, found]) => found);
});

const sensitiveFilesCount = computed(() => {
  return Object.keys(props.securityAudit?.sensitive_files || {}).length;
});

const openDirectories = computed<[string, boolean][]>(() => {
  const directories = props.securityAudit?.directory_listing || {};
  return Object.entries(directories).filter(([, enabled]) => enabled);
});

const directoriesCount = computed(() => {
  return Object.keys(props.securityAudit?.directory_listing || {}).length;
});

const seoFilesDetails = computed(() => {
  const audit = props.securityAudit;
  const found = [
    audit?.robots_txt ? "robots.txt" : null,
    audit?.sitemap_xml ? "sitemap.xml" : null,
  ].filter(Boolean);
  return found.length === 2 ? "Оба файла найдены." : `Найдено: ${found.join(", ") || "ничего"}.`;
});

const categoryStatus = (issues: SecurityIssue[]): CheckStatus => {
  if (issues.some((issue) => issue.status === "bad")) return "bad";
  if (issues.some((issue) => issue.status === "warn")) return "warn";
  return "ok";
};

const securityCategories = computed<SecurityCategory[]>(() => {
  const audit = props.securityAudit;
  if (!audit) return [];

  const transportIssues: SecurityIssue[] = [
    !audit.https?.uses_https
      ? {
          title: "Сайт открыт без HTTPS",
          status: "bad",
          details: "Соединение не шифруется.",
          fix: "Подключите TLS-сертификат и отдавайте сайт только по HTTPS.",
        }
      : null,
    redirectStatus.value !== "ok"
      ? {
          title: "HTTP → HTTPS redirect",
          status: redirectStatus.value,
          details: redirectDetails.value,
          fix: "Настройте 301/308 редирект с http:// на https://.",
        }
      : null,
    (audit.mixed_content?.count || 0) > 0
      ? {
          title: "Mixed content",
          status: "bad",
          details: mixedContentDetails.value,
          fix: "Замените все http:// ресурсы на https://.",
        }
      : null,
  ].filter(Boolean) as SecurityIssue[];

  const headerIssueItems: SecurityIssue[] = headerIssues.value.map(([key, check]) => ({
    title: formatHeaderName(key),
    status: check.status,
    details: check.message,
    fix: check.recommendation,
  }));

  const cookieIssues: SecurityIssue[] = weakCookies.value.map((cookie) => ({
    title: cookie.name,
    status: "bad",
    details: cookie.issues.join(", "),
    fix: "Для сессионных cookies включите Secure, HttpOnly и SameSite=Lax/Strict.",
  }));

  const fileIssues: SecurityIssue[] = leakedFiles.value.map(([path]) => ({
    title: path,
    status: "bad",
    details: "Файл доступен публично. Это может раскрыть секреты или исходный код.",
    fix: "Закройте доступ к файлу на уровне nginx/apache и проверьте, не утекли ли ключи.",
  }));

  const listingIssues: SecurityIssue[] = openDirectories.value.map(([path]) => ({
    title: path,
    status: "bad",
    details: "Папка показывает список файлов.",
    fix: "Отключите autoindex/directory listing для этой директории.",
  }));

  const resourceIssues: SecurityIssue[] = [
    (audit.script_integrity?.without_integrity_count || 0) > 0
      ? {
          title: "Внешние JS без SRI",
          status: "warn",
          details: scriptIntegrityDetails.value,
          fix: "Для CDN-скриптов добавьте integrity и crossorigin.",
        }
      : null,
    audit.server_exposure?.issues?.length
      ? {
          title: "Раскрытие сервера",
          status: "warn",
          details: serverExposureDetails.value,
          fix: "Скройте X-Powered-By и минимизируйте Server в конфигурации web-сервера.",
        }
      : null,
  ].filter(Boolean) as SecurityIssue[];

  return [
    {
      key: "transport",
      title: "HTTPS",
      status: categoryStatus(transportIssues),
      summary: transportIssues.length ? `${transportIssues.length} проблем(ы) с транспортом` : "HTTPS и редиректы выглядят нормально",
      okText: "HTTPS включён, небезопасные ресурсы не найдены.",
      meta: [
        audit.https?.uses_https ? "HTTPS включён" : "HTTPS выключен",
        redirectStatus.value === "ok" ? "HTTP редиректит" : "редирект проверить",
      ],
      issues: transportIssues,
    },
    {
      key: "headers",
      title: "Заголовки",
      status: categoryStatus(headerIssueItems),
      summary: headerIssueItems.length ? `Нужно поправить: ${headerIssueItems.length}` : `Проверено ${headersCount.value} заголовков`,
      okText: "Явных слабых security headers не найдено.",
      meta: [`проверок: ${headersCount.value}`],
      issues: headerIssueItems,
    },
    {
      key: "cookies",
      title: "Cookies",
      status: categoryStatus(cookieIssues),
      summary: audit.cookie_flags?.total ? `Cookies: ${audit.cookie_flags.total}, слабых: ${audit.cookie_flags.weak}` : "Set-Cookie не найден",
      okText: audit.cookie_flags?.total ? "Слабые cookie-флаги не найдены." : "Для сайта без авторизации это нормально.",
      meta: [`всего: ${audit.cookie_flags?.total || 0}`],
      issues: cookieIssues,
    },
    {
      key: "files",
      title: "Файлы",
      status: categoryStatus(fileIssues),
      summary: fileIssues.length ? `Найдено утечек: ${fileIssues.length}` : `Проверено ${sensitiveFilesCount.value} опасных путей`,
      okText: "Публичных утечек файлов не найдено.",
      meta: [`путей: ${sensitiveFilesCount.value}`],
      issues: fileIssues,
    },
    {
      key: "listing",
      title: "Папки",
      status: categoryStatus(listingIssues),
      summary: listingIssues.length ? `Открыт listing: ${listingIssues.length}` : `Проверено директорий: ${directoriesCount.value}`,
      okText: "Публичный список файлов в директориях не найден.",
      meta: [`директорий: ${directoriesCount.value}`],
      issues: listingIssues,
    },
    {
      key: "resources",
      title: "Ресурсы",
      status: categoryStatus(resourceIssues),
      summary: resourceIssues.length ? `Есть предупреждения: ${resourceIssues.length}` : "Внешние ресурсы выглядят спокойно",
      okText: "Критичных проблем с внешними ресурсами не найдено.",
      meta: [
        `JS внешних: ${audit.script_integrity?.external_count || 0}`,
        audit.security_txt ? "security.txt есть" : "security.txt нет",
        seoFilesDetails.value,
      ],
      issues: resourceIssues,
    },
  ];
});
</script>

<style scoped>
.security-score-panel,
.recommendations-panel,
.security-panel {
  background: linear-gradient(135deg, rgba(24, 24, 38, 0.96) 0%, rgba(31, 31, 48, 0.96) 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 14px 34px rgba(0, 0, 0, 0.22);
}

.security-score-panel {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  border-radius: 22px;
  padding: 1.5rem;
  margin-bottom: 1.25rem;
}

.security-score-panel.risk-high {
  border-color: rgba(239, 68, 68, 0.35);
}

.security-score-panel.risk-medium {
  border-color: rgba(245, 158, 11, 0.35);
}

.security-score-panel.risk-low {
  border-color: rgba(16, 185, 129, 0.35);
}

.score-eyebrow {
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.security-score-panel h3 {
  color: #fff;
  font-size: 1.5rem;
  margin: 0.25rem 0;
}

.security-score-panel p {
  color: rgba(255, 255, 255, 0.68);
  margin: 0;
  line-height: 1.45;
}

.risk-count {
  min-width: 94px;
  height: 76px;
  border-radius: 18px;
  background: rgba(100, 108, 255, 0.14);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.risk-count span {
  color: #fff;
  font-size: 2rem;
  font-weight: 800;
  line-height: 1;
}

.risk-count small {
  color: rgba(255, 255, 255, 0.56);
  font-size: 0.72rem;
}

.recommendations-panel {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr));
  gap: 0.75rem;
  border-radius: 18px;
  padding: 1rem;
  margin-bottom: 1.25rem;
}

.recommendation-row {
  display: grid;
  grid-template-columns: 20px minmax(0, 1fr);
  gap: 0.75rem;
  padding: 0.85rem;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.045);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.recommendation-row svg {
  width: 18px;
  height: 18px;
  margin-top: 0.1rem;
}

.recommendation-row strong,
.recommendation-row p {
  overflow-wrap: anywhere;
}

.recommendation-row strong {
  display: block;
  color: #fff;
  font-size: 0.92rem;
}

.recommendation-row p {
  color: rgba(255, 255, 255, 0.66);
  margin: 0.25rem 0 0;
  font-size: 0.86rem;
  line-height: 1.45;
}

.severity-critical,
.severity-high {
  border-color: rgba(239, 68, 68, 0.28);
}

.severity-critical svg,
.severity-high svg {
  color: #ef4444;
}

.severity-medium svg {
  color: #f59e0b;
}

.severity-low svg {
  color: #60a5fa;
}

.security-board {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr));
  gap: 1rem;
}

.security-panel {
  border-radius: 20px;
  padding: 1.1rem;
  min-width: 0;
  overflow: hidden;
}

.security-panel.panel-bad {
  border-color: rgba(239, 68, 68, 0.28);
}

.security-panel.panel-warn {
  border-color: rgba(245, 158, 11, 0.28);
}

.security-panel.panel-ok {
  border-color: rgba(16, 185, 129, 0.18);
}

.panel-header {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) auto;
  gap: 0.8rem;
  align-items: start;
}

.panel-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(100, 108, 255, 0.14);
  color: #b9bdff;
}

.panel-icon svg {
  width: 22px;
  height: 22px;
}

.panel-title {
  min-width: 0;
}

.panel-title h3 {
  color: #fff;
  font-size: 1.08rem;
  line-height: 1.25;
  margin: 0 0 0.3rem;
}

.panel-title p {
  color: rgba(255, 255, 255, 0.58);
  font-size: 0.86rem;
  line-height: 1.4;
  margin: 0;
  overflow-wrap: anywhere;
}

.panel-status,
.issue-badge {
  padding: 0.25rem 0.58rem;
  border-radius: 999px;
  font-size: 0.67rem;
  font-weight: 800;
  line-height: 1.1;
  white-space: nowrap;
}

.panel-status.ok,
.issue-badge.ok {
  background: rgba(16, 185, 129, 0.14);
  color: #10b981;
}

.panel-status.warn,
.issue-badge.warn {
  background: rgba(245, 158, 11, 0.14);
  color: #f59e0b;
}

.panel-status.bad,
.issue-badge.bad {
  background: rgba(239, 68, 68, 0.14);
  color: #ef4444;
}

.panel-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  margin-top: 0.9rem;
}

.panel-meta span {
  max-width: 100%;
  padding: 0.32rem 0.6rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.055);
  color: rgba(255, 255, 255, 0.62);
  font-size: 0.75rem;
  overflow-wrap: anywhere;
}

.panel-ok {
  margin-top: 1rem;
  padding: 0.8rem 0.9rem;
  border-radius: 14px;
  background: rgba(16, 185, 129, 0.08);
  color: rgba(214, 255, 235, 0.88);
  font-size: 0.86rem;
  line-height: 1.45;
}

.issue-list {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  margin-top: 1rem;
}

.issue-row {
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.045);
  border: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
}

.issue-row[open] {
  background: rgba(255, 255, 255, 0.06);
}

.issue-row summary {
  list-style: none;
  cursor: pointer;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 0.75rem;
  align-items: center;
  padding: 0.78rem 0.85rem;
}

.issue-row summary::-webkit-details-marker {
  display: none;
}

.issue-title {
  color: #fff;
  font-size: 0.9rem;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.issue-row p,
.issue-fix {
  margin: 0;
  padding: 0 0.85rem 0.8rem;
  color: rgba(255, 255, 255, 0.66);
  font-size: 0.84rem;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.issue-fix {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  color: rgba(255, 255, 255, 0.82);
}

.issue-fix strong {
  color: #b9bdff;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

@media (max-width: 768px) {
  .security-score-panel {
    flex-direction: column;
    align-items: stretch;
  }

  .risk-count {
    width: 100%;
  }

  .panel-header {
    grid-template-columns: 40px minmax(0, 1fr);
  }

  .panel-status {
    grid-column: 2;
    width: fit-content;
  }
}
</style>
