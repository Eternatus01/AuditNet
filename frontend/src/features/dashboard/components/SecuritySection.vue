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

      <div class="security-grid">
        <SecurityCard
          title="HTTPS и транспорт"
          description="Проверяет, доступен ли сайт по HTTPS, перенаправляет ли HTTP на HTTPS и нет ли небезопасных http:// ресурсов на странице."
          :is-expanded="isExpanded('transport')"
          @toggle-info="onToggle('transport')"
        >
          <template #icon>
            <IconLucideShieldCheck />
          </template>
          <CheckRow
            label="Сайт открыт по HTTPS"
            :status="securityAudit.https?.uses_https ? 'ok' : 'bad'"
            :details="securityAudit.https?.uses_https ? 'Соединение шифруется.' : 'Откройте сайт по HTTPS и настройте TLS-сертификат.'"
          />
          <CheckRow
            label="HTTP → HTTPS redirect"
            :status="redirectStatus"
            :details="redirectDetails"
          />
          <CheckRow
            label="Mixed content"
            :status="(securityAudit.mixed_content?.count || 0) > 0 ? 'bad' : 'ok'"
            :details="mixedContentDetails"
          />
        </SecurityCard>

        <SecurityCard
          title="Качество заголовков"
          :description="descriptions.security.headers"
          :is-expanded="isExpanded('headers')"
          @toggle-info="onToggle('headers')"
        >
          <template #icon>
            <IconLucideLock />
          </template>
          <CheckRow
            v-if="headerIssues.length === 0"
            label="Security headers"
            status="ok"
            :details="`Проверено ${headersCount} важных заголовков, явных проблем не найдено.`"
          />
          <CheckRow
            v-for="[key, check] in headerIssues"
            :key="key"
            :label="formatHeaderName(key)"
            :status="check.status"
            :details="check.message"
            :fix="check.status !== 'ok' ? check.recommendation : undefined"
          />
        </SecurityCard>

        <SecurityCard
          title="Cookies сессии"
          description="Проверяет Set-Cookie: Secure, HttpOnly и SameSite. Эти флаги снижают риск кражи сессий через XSS и небезопасные соединения."
          :is-expanded="isExpanded('cookies')"
          @toggle-info="onToggle('cookies')"
        >
          <template #icon>
            <IconLucideCookie />
          </template>
          <div v-if="!securityAudit.cookie_flags?.total" class="empty-check">
            Set-Cookie не найден. Если на сайте нет авторизации — это нормально.
          </div>
          <CheckRow
            v-else-if="weakCookies.length === 0"
            label="Cookie-флаги"
            status="ok"
            :details="`Проверено cookies: ${securityAudit.cookie_flags?.total || 0}. Слабые флаги не найдены.`"
          />
          <CheckRow
            v-for="cookie in weakCookies"
            :key="cookie.name"
            :label="cookie.name"
            status="bad"
            :details="cookie.issues.join(', ')"
            fix="Для сессионных cookies включите Secure, HttpOnly и SameSite=Lax/Strict."
          />
        </SecurityCard>

        <SecurityCard
          title="Утечки файлов"
          :description="descriptions.security.sensitiveFiles"
          :is-expanded="isExpanded('files')"
          @toggle-info="onToggle('files')"
        >
          <template #icon>
            <IconLucideFileText />
          </template>
          <CheckRow
            v-if="leakedFiles.length === 0"
            label="Проверка публичных файлов"
            status="ok"
            :details="`Проверено ${sensitiveFilesCount} опасных путей, публичных утечек не найдено.`"
          />
          <CheckRow
            v-for="[path, found] in leakedFiles"
            :key="path"
            :label="path"
            :status="found ? 'bad' : 'ok'"
            :details="found ? 'Файл доступен публично. Это может раскрыть секреты или исходный код.' : 'Не найден публично.'"
            :fix="found ? 'Закройте доступ к файлу на уровне nginx/apache и проверьте, не утекли ли ключи.' : undefined"
          />
        </SecurityCard>

        <SecurityCard
          title="Directory Listing"
          :description="descriptions.security.directoryListing"
          :is-expanded="isExpanded('listing')"
          @toggle-info="onToggle('listing')"
        >
          <template #icon>
            <IconLucideFolder />
          </template>
          <CheckRow
            v-if="openDirectories.length === 0"
            label="Проверка листинга директорий"
            status="ok"
            :details="`Проверено ${directoriesCount} директорий, публичный список файлов не найден.`"
          />
          <CheckRow
            v-for="[path, enabled] in openDirectories"
            :key="path"
            :label="path"
            :status="enabled ? 'bad' : 'ok'"
            :details="enabled ? 'Папка показывает список файлов.' : 'Список файлов не раскрывается.'"
            :fix="enabled ? 'Отключите autoindex/directory listing для этой директории.' : undefined"
          />
        </SecurityCard>

        <SecurityCard
          title="Внешние ресурсы"
          description="Проверяет внешние JS, SRI, security.txt и раскрытие Server/X-Powered-By."
          :is-expanded="isExpanded('resources')"
          @toggle-info="onToggle('resources')"
        >
          <template #icon>
            <IconLucideCode2 />
          </template>
          <CheckRow
            label="Внешние JS без SRI"
            :status="(securityAudit.script_integrity?.without_integrity_count || 0) > 0 ? 'warn' : 'ok'"
            :details="scriptIntegrityDetails"
            :fix="(securityAudit.script_integrity?.without_integrity_count || 0) > 0 ? 'Добавьте integrity и crossorigin для CDN-скриптов.' : undefined"
          />
          <CheckRow
            label="Раскрытие сервера"
            :status="securityAudit.server_exposure?.issues?.length ? 'warn' : 'ok'"
            :details="serverExposureDetails"
            :fix="securityAudit.server_exposure?.issues?.length ? 'Скройте X-Powered-By и минимизируйте Server в конфигурации web-сервера.' : undefined"
          />
          <CheckRow
            label="security.txt"
            :status="securityAudit.security_txt ? 'ok' : 'warn'"
            :details="securityAudit.security_txt ? 'Файл найден.' : 'Файл не найден. Это не ошибка, но полезно для зрелого процесса безопасности.'"
            fix="Добавьте /.well-known/security.txt с контактом для сообщений об уязвимостях."
          />
          <CheckRow
            label="robots.txt / sitemap.xml"
            :status="securityAudit.robots_txt && securityAudit.sitemap_xml ? 'ok' : 'warn'"
            :details="seoFilesDetails"
          />
        </SecurityCard>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, PropType } from "vue";
import SecurityCard from "./SecurityCard.vue";
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

const emit = defineEmits<{
  toggle: [key: string];
}>();

const onToggle = (_key: string) => {
  emit("toggle", _key);
};

type CheckStatus = "ok" | "warn" | "bad";

const CheckRow = defineComponent({
  name: "CheckRow",
  props: {
    label: { type: String, required: true },
    status: { type: String as PropType<CheckStatus>, required: true },
    details: { type: String, required: true },
    fix: { type: String, required: false },
  },
  setup(rowProps) {
    return () =>
      h("div", { class: ["practical-check", `check-${rowProps.status}`] }, [
        h("div", { class: "check-main" }, [
          h("span", { class: "check-title" }, rowProps.label),
          h("span", { class: ["status-pill", rowProps.status] }, statusLabel(rowProps.status)),
        ]),
        h("p", { class: "check-details" }, rowProps.details),
        rowProps.fix
          ? h("div", { class: "check-fix" }, [
              h("span", { class: "fix-label" }, "Как исправить"),
              h("span", rowProps.fix),
            ])
          : null,
      ]);
  },
});

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
</script>

<style scoped>
.security-score-panel,
.recommendations-panel {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.security-score-panel {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
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
  font-size: 0.85rem;
  font-weight: 600;
}

.security-score-panel h3 {
  color: #fff;
  font-size: 1.5rem;
  margin: 0.25rem 0;
}

.security-score-panel p {
  color: rgba(255, 255, 255, 0.68);
  margin: 0;
}

.risk-count {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  background: rgba(100, 108, 255, 0.14);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.risk-count span {
  color: #fff;
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

.risk-count small {
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.75rem;
  text-align: center;
}

.recommendations-panel {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr));
  gap: 0.75rem;
}

.recommendation-row {
  display: grid;
  grid-template-columns: 22px minmax(0, 1fr);
  gap: 0.75rem;
  padding: 0.9rem 1rem;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  min-width: 0;
}

.recommendation-row svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.recommendation-row strong {
  display: block;
  color: #fff;
  overflow-wrap: anywhere;
}

.recommendation-row p {
  color: rgba(255, 255, 255, 0.68);
  margin: 0.25rem 0 0;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.severity-critical,
.severity-high {
  border-color: rgba(239, 68, 68, 0.3);
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

:deep(.security-grid) {
  grid-template-columns: 1fr;
  gap: 1.25rem;
  align-items: start;
}

:deep(.security-card) {
  min-width: 0;
  padding: 1.5rem;
  border-radius: 22px;
  overflow: hidden;
}

:deep(.security-card:hover) {
  transform: translateY(-2px);
}

:deep(.security-items) {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
  gap: 0.75rem;
  margin-top: 1rem;
}

:deep(.score-header) {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) auto;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

:deep(.score-header h3) {
  min-width: 0;
  overflow-wrap: anywhere;
  font-size: 1.15rem;
}

:deep(.score-icon.security) {
  width: 44px;
  height: 44px;
}

:deep(.info-description) {
  overflow-wrap: anywhere;
}

.practical-check {
  padding: 1rem;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-left: 3px solid rgba(255, 255, 255, 0.1);
  min-width: 0;
  overflow: hidden;
  min-height: 100%;
}

.practical-check + .practical-check {
  margin-top: 0;
}

.practical-check.check-ok {
  border-left-color: #10b981;
}

.practical-check.check-warn {
  border-left-color: #f59e0b;
}

.practical-check.check-bad {
  border-left-color: #ef4444;
}

.check-main {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: start;
  gap: 0.75rem;
  min-width: 0;
}

.check-title {
  color: #fff;
  font-size: 0.95rem;
  font-weight: 700;
  min-width: 0;
  overflow-wrap: anywhere;
  word-break: break-word;
  line-height: 1.35;
}

.status-pill {
  flex-shrink: 0;
  padding: 0.24rem 0.6rem;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-pill.ok {
  background: rgba(16, 185, 129, 0.14);
  color: #10b981;
}

.status-pill.warn {
  background: rgba(245, 158, 11, 0.14);
  color: #f59e0b;
}

.status-pill.bad {
  background: rgba(239, 68, 68, 0.14);
  color: #ef4444;
}

.check-details,
.check-fix,
.empty-check {
  margin: 0.45rem 0 0;
  color: rgba(255, 255, 255, 0.62);
  font-size: 0.86rem;
  line-height: 1.45;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.check-fix {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  color: rgba(255, 255, 255, 0.86);
  background: rgba(100, 108, 255, 0.08);
  border: 1px solid rgba(100, 108, 255, 0.12);
  border-radius: 10px;
  padding: 0.65rem 0.75rem;
}

.fix-label {
  color: #b9bdff;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

@media (max-width: 768px) {
  .security-score-panel {
    flex-direction: column;
    align-items: flex-start;
  }

  :deep(.security-grid) {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  :deep(.security-card) {
    padding: 1rem;
  }

  :deep(.score-header) {
    grid-template-columns: 40px minmax(0, 1fr) auto;
  }

  .check-main {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .status-pill {
    width: fit-content;
  }
}
</style>
