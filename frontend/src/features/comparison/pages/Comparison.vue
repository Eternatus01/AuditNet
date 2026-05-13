<template>
  <div class="comparison-page">
    <div class="dashboard-header">
      <h1>Сравнение сайтов</h1>
      <p class="dashboard-subtitle">
        Добавьте несколько URL и сравните их ключевые показатели в одном отчёте
      </p>
      <div class="examples-hint">
        Доступно только авторизованным пользователям, результаты сравнения не сохраняются в историю
      </div>
    </div>

    <section class="comparison-form">
      <div class="comparison-form-header">
        <div>
          <h2>Сайты для сравнения</h2>
          <p>Минимум 2 сайта, максимум 5 за один запуск.</p>
        </div>
        <Button
          variant="secondary"
          size="md"
          :disabled="siteInputs.length >= MAX_SITES || isLoading"
          @click="addSiteInput"
        >
          Добавить сайт
        </Button>
      </div>

      <div class="comparison-inputs">
        <div
          v-for="(_, index) in siteInputs"
          :key="index"
          class="comparison-input-row"
        >
          <span class="input-index">{{ index + 1 }}</span>
          <input
            v-model="siteInputs[index]"
            type="url"
            class="comparison-url-input"
            placeholder="https://example.com"
            autocomplete="url"
            :disabled="isLoading"
            @keyup.enter="analyzeSites"
          />
          <Button
            variant="ghost"
            size="md"
            aria-label="Удалить сайт"
            :disabled="siteInputs.length <= MIN_SITES || isLoading"
            @click="removeSiteInput(index)"
          >
            Удалить
          </Button>
        </div>
      </div>

      <div class="comparison-actions">
        <Button
          variant="primary"
          size="lg"
          :loading="isLoading"
          :disabled="isAnalyzeButtonDisabled"
          show-text-while-loading
          @click="analyzeSites"
        >
          {{ isLoading ? "Сравниваем сайты..." : "Сравнить сайты" }}
        </Button>
      </div>
    </section>

    <LoadingState
      v-if="isLoading"
      text="Анализируем сайты... Это может занять 30–60 секунд"
      size="lg"
    />

    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>

    <section v-if="hasSuccessfulResults" class="comparison-results">
      <div class="section-header">
        <h2 class="section-title">Сравнение показателей</h2>
        <span class="comparison-count">{{ successfulResults.length }} сайта</span>
      </div>
      <p class="section-subtitle">
        Лучшие значения подсвечены. Для оценок выше значит лучше, для временных метрик ниже значит лучше.
      </p>

      <div class="comparison-summary">
        <article
          v-for="result in successfulResults"
          :key="result.url"
          class="site-summary-card"
        >
          <div class="site-summary-top">
            <h3>{{ getHostname(result.url) }}</h3>
            <a :href="result.url" target="_blank" rel="noopener noreferrer">
              {{ result.url }}
            </a>
          </div>
          <div class="site-summary-score" :class="getScoreStatus(getAverageScore(result.data))">
            {{ formatScore(getAverageScore(result.data)) }}
          </div>
          <p>Средняя оценка Lighthouse</p>
        </article>
      </div>

      <div class="comparison-table-card">
        <div class="comparison-table-wrapper">
          <table class="comparison-table">
            <thead>
              <tr>
                <th>Показатель</th>
                <th
                  v-for="result in successfulResults"
                  :key="result.url"
                >
                  {{ getHostname(result.url) }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="metric in comparisonMetrics"
                :key="metric.key"
              >
                <td>
                  <strong>{{ metric.label }}</strong>
                  <span>{{ metric.description }}</span>
                </td>
                <td
                  v-for="result in successfulResults"
                  :key="`${metric.key}-${result.url}`"
                  :class="[
                    'metric-cell',
                    getMetricCellClass(metric, result),
                    { 'is-best': isBestMetricValue(metric, result) },
                  ]"
                >
                  {{ metric.format(metric.getValue(result.data), result.data) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="failedResults.length > 0" class="failed-sites">
        <h3>Не удалось проанализировать</h3>
        <ul>
          <li
            v-for="result in failedResults"
            :key="result.url"
          >
            <strong>{{ result.url }}</strong>
            <span>{{ result.error }}</span>
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAuditApi } from "@/features/dashboard/composables/useAuditApi";
import { useAuthStore } from "@/features/auth/stores/auth";
import { Button } from "@/shared/ui/atoms";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import {
  formatCLS,
  formatFCP,
  formatFID,
  formatLCP,
  formatSpeedIndex,
  formatTBT,
} from "@/shared/utils/formatters";
import type { GuestAuditData } from "@/features/dashboard/types";

const MIN_SITES = 2;
const MAX_SITES = 5;

type ComparisonDirection = "higher" | "lower";

interface ComparisonResult {
  url: string;
  data: GuestAuditData | null;
  error: string | null;
}

interface ComparisonMetric {
  key: string;
  label: string;
  description: string;
  direction: ComparisonDirection;
  getValue: (_data: GuestAuditData) => number | null;
  format: (_value: number | null, _data: GuestAuditData) => string;
}

const auditApi = useAuditApi();
const authStore = useAuthStore();
const siteInputs = ref<string[]>(["", ""]);
const results = ref<ComparisonResult[]>([]);
const isLoading = ref(false);
const errorMessage = ref("");

const normalizeUrl = (url: string): string => {
  const trimmed = url.trim();
  if (!trimmed) return "";
  if (trimmed.startsWith("http://") || trimmed.startsWith("https://")) {
    return trimmed;
  }
  return `https://${trimmed}`;
};

const isValidUrl = (url: string): boolean => {
  try {
    const parsedUrl = new URL(normalizeUrl(url));
    return ["http:", "https:"].includes(parsedUrl.protocol) && parsedUrl.hostname.length >= 3;
  } catch {
    return false;
  }
};

const uniqueValidUrls = computed(() => {
  const normalizedUrls = siteInputs.value
    .map((url) => normalizeUrl(url))
    .filter((url) => url && isValidUrl(url));

  return [...new Set(normalizedUrls)];
});

const canAnalyze = computed(() => {
  return uniqueValidUrls.value.length >= MIN_SITES && !isLoading.value;
});

const isAnalyzeButtonDisabled = computed(() => {
  if (!authStore.isAuthenticated) return isLoading.value;
  return !canAnalyze.value;
});

const successfulResults = computed(() => {
  return results.value.filter((result): result is ComparisonResult & { data: GuestAuditData } => {
    return result.data !== null;
  });
});

const failedResults = computed(() => {
  return results.value.filter((result) => result.data === null);
});

const hasSuccessfulResults = computed(() => successfulResults.value.length > 0);

const getSecurityHeadersCount = (data: GuestAuditData): number | null => {
  const headers = data.security_audit?.headers;
  if (!headers) return null;
  return Object.values(headers).filter((value) => value === true).length;
};

const getSecurityHeadersTotal = (data: GuestAuditData): number => {
  return Object.keys(data.security_audit?.headers ?? {}).length;
};

const getSensitiveFilesCount = (data: GuestAuditData): number | null => {
  const sensitiveFiles = data.security_audit?.sensitive_files;
  if (!sensitiveFiles) return null;
  return Object.values(sensitiveFiles).filter(Boolean).length;
};

const formatScore = (value: number | null): string => {
  if (value === null) return "--";
  return Math.round(value).toString();
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

const comparisonMetrics: ComparisonMetric[] = [
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

const addSiteInput = (): void => {
  if (siteInputs.value.length < MAX_SITES) {
    siteInputs.value.push("");
  }
};

const removeSiteInput = (index: number): void => {
  if (siteInputs.value.length > MIN_SITES) {
    siteInputs.value.splice(index, 1);
  }
};

const analyzeSites = async (): Promise<void> => {
  if (isLoading.value) return;

  if (!authStore.isAuthenticated) {
    results.value = [];
    errorMessage.value =
      "Вы не авторизованы. Доступ к сравнению сайтов доступен только авторизованным пользователям.";
    return;
  }

  const urls = uniqueValidUrls.value;
  if (urls.length < MIN_SITES) {
    errorMessage.value = "Добавьте минимум два корректных URL для сравнения.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";
  results.value = [];

  try {
    results.value = await Promise.all(
      urls.map(async (url): Promise<ComparisonResult> => {
        try {
          const response = await auditApi.analyzeGuestWebsite(url);
          return {
            url,
            data: response.data,
            error: null,
          };
        } catch (error: unknown) {
          return {
            url,
            data: null,
            error: error instanceof Error ? error.message : "Ошибка анализа сайта",
          };
        }
      })
    );

    if (!results.value.some((result) => result.data !== null)) {
      errorMessage.value = "Не удалось проанализировать сайты. Попробуйте позже.";
    }
  } finally {
    isLoading.value = false;
  }
};

const getAverageScore = (data: GuestAuditData): number | null => {
  const scores = [data.performance, data.accessibility, data.best_practices, data.seo].filter(
    (score): score is number => typeof score === "number"
  );

  if (scores.length === 0) return null;
  return scores.reduce((sum, score) => sum + score, 0) / scores.length;
};

const getScoreStatus = (value: number | null): "good" | "moderate" | "poor" | "unknown" => {
  if (value === null) return "unknown";
  if (value >= 90) return "good";
  if (value >= 50) return "moderate";
  return "poor";
};

const getMetricBestValue = (metric: ComparisonMetric): number | null => {
  const values = successfulResults.value
    .map((result) => metric.getValue(result.data))
    .filter((value): value is number => typeof value === "number");

  if (values.length === 0) return null;
  return metric.direction === "higher" ? Math.max(...values) : Math.min(...values);
};

const isBestMetricValue = (metric: ComparisonMetric, result: ComparisonResult): boolean => {
  if (!result.data) return false;

  const value = metric.getValue(result.data);
  const bestValue = getMetricBestValue(metric);

  return value !== null && bestValue !== null && value === bestValue;
};

const getMetricCellClass = (
  metric: ComparisonMetric,
  result: ComparisonResult
): "good" | "moderate" | "poor" | "unknown" => {
  if (!result.data) return "unknown";

  const value = metric.getValue(result.data);
  if (value === null) return "unknown";

  if (["performance", "accessibility", "best-practices", "seo"].includes(metric.key)) {
    return getScoreStatus(value);
  }

  return isBestMetricValue(metric, result) ? "good" : "unknown";
};

const getHostname = (url: string): string => {
  try {
    return new URL(url).hostname.replace(/^www\./, "");
  } catch {
    return url;
  }
};
</script>

<style scoped>
.comparison-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2rem;
  box-sizing: border-box;
}

.comparison-form,
.comparison-table-card,
.site-summary-card,
.failed-sites {
  background: linear-gradient(135deg, var(--bg-secondary, #18181b) 0%, var(--bg-elevated, #1f1f23) 100%);
  border: 1px solid var(--border-color, #27272a);
  border-radius: var(--radius-xl, 24px);
  box-shadow: var(--shadow-lg);
}

.comparison-form {
  padding: 2rem;
  margin-bottom: 2rem;
}

.comparison-form-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.comparison-form-header h2 {
  margin: 0 0 0.35rem;
  font-size: 1.5rem;
}

.comparison-form-header p {
  margin: 0;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
}

.comparison-inputs {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comparison-input-row {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 0.75rem;
  align-items: center;
}

.input-index {
  width: 2.25rem;
  height: 2.25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  color: #fff;
  background: rgba(124, 58, 237, 0.2);
  border: 1px solid rgba(124, 58, 237, 0.35);
  font-weight: 700;
}

.comparison-url-input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.875rem 1.125rem;
  font-size: 1rem;
  font-family: inherit;
  color: inherit;
  background-color: #242424;
  border: 1px solid #3a3a3a;
  border-radius: var(--radius-md, 14px);
  outline: none;
  transition: border-color 0.25s, box-shadow 0.25s;
}

.comparison-url-input:focus {
  border-color: var(--primary-color, #7c3aed);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}

.comparison-url-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.comparison-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.comparison-results {
  margin-top: 3rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.comparison-count {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary, rgba(255, 255, 255, 0.6));
  padding: 0.375rem 0.875rem;
  background: rgba(124, 58, 237, 0.1);
  border-radius: var(--radius-md, 12px);
}

.comparison-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  margin: 2rem 0;
}

.site-summary-card {
  padding: 1.5rem;
}

.site-summary-top {
  min-height: 4.5rem;
}

.site-summary-top h3 {
  margin: 0 0 0.35rem;
  font-size: 1.15rem;
  word-break: break-word;
}

.site-summary-top a {
  display: block;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
  font-size: 0.875rem;
  word-break: break-all;
}

.site-summary-score {
  margin-top: 1rem;
  font-size: 2.5rem;
  line-height: 1;
  font-weight: 800;
}

.site-summary-score.good,
.metric-cell.good {
  color: #10b981;
}

.site-summary-score.moderate,
.metric-cell.moderate {
  color: #f59e0b;
}

.site-summary-score.poor,
.metric-cell.poor {
  color: #ef4444;
}

.site-summary-score.unknown,
.metric-cell.unknown {
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
}

.site-summary-card p {
  margin: 0.5rem 0 0;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
}

.comparison-table-card {
  overflow: hidden;
}

.comparison-table-wrapper {
  overflow-x: auto;
}

.comparison-table {
  width: 100%;
  min-width: 820px;
  border-collapse: collapse;
}

.comparison-table th,
.comparison-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-color, #27272a);
  text-align: left;
  vertical-align: middle;
}

.comparison-table th {
  color: #fff;
  background: rgba(124, 58, 237, 0.08);
  font-size: 0.9rem;
  letter-spacing: 0.01em;
}

.comparison-table tbody tr:last-child td {
  border-bottom: none;
}

.comparison-table td:first-child {
  min-width: 240px;
}

.comparison-table td:first-child strong {
  display: block;
  color: #fff;
}

.comparison-table td:first-child span {
  display: block;
  margin-top: 0.15rem;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
  font-size: 0.875rem;
}

.metric-cell {
  font-weight: 700;
  white-space: nowrap;
}

.metric-cell.is-best {
  background: rgba(16, 185, 129, 0.1);
}

.failed-sites {
  margin-top: 1.5rem;
  padding: 1.5rem;
  border-color: rgba(239, 68, 68, 0.25);
}

.failed-sites h3 {
  margin: 0 0 1rem;
}

.failed-sites ul {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.failed-sites li {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.failed-sites span {
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
}

@media (max-width: 768px) {
  .comparison-page {
    padding: 2rem 1rem;
  }

  .comparison-form {
    padding: 1.25rem;
  }

  .comparison-form-header,
  .section-header {
    flex-direction: column;
    align-items: stretch;
  }

  .comparison-input-row {
    grid-template-columns: auto minmax(0, 1fr);
  }

  .comparison-input-row > :last-child {
    grid-column: 2;
    justify-self: flex-end;
  }

  .comparison-actions {
    justify-content: stretch;
  }

  .comparison-actions > :deep(*) {
    width: 100%;
  }
}
</style>
