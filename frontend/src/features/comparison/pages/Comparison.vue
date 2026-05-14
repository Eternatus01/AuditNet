<template>
  <div class="comparison-page">
    <div class="dashboard-header">
      <h1>Сравнение сайтов</h1>
      <p class="dashboard-subtitle">
        Добавьте несколько URL и сравните их ключевые показатели в одном отчёте
      </p>
      <div class="examples-hint">
        Доступно только авторизованным пользователям, результаты сравнения сохраняются в историю
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

    <ComparisonResults :results="results" />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAuthStore } from "@/features/auth/stores/auth";
import { useComparisonApi } from "../composables/useComparisonApi";
import { Button } from "@/shared/ui/atoms";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import type { AuditComparison, ComparisonSite } from "@/features/history/types";
import ComparisonResults from "../components/ComparisonResults.vue";
import type { ComparisonResult } from "../utils/comparisonHelpers";
import { mapComparisonSiteToResult } from "../utils/mappers";

const MIN_SITES = 2;
const MAX_SITES = 5;

const comparisonApi = useComparisonApi();
const authStore = useAuthStore();
const siteInputs = ref<string[]>(["", ""]);
const results = ref<ComparisonResult[]>([]);
const currentComparison = ref<AuditComparison | null>(null);
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
    currentComparison.value = null;
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
  currentComparison.value = null;

  try {
    const response = await comparisonApi.analyzeComparison(urls);
    currentComparison.value = response.data;
    results.value = response.data.sites.map(mapComparisonSiteToResult);

    if (!results.value.some((result) => result.data !== null)) {
      errorMessage.value = "Не удалось проанализировать сайты. Попробуйте позже.";
    }
  } finally {
    isLoading.value = false;
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
