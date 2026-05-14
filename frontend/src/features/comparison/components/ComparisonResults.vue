<template>
  <section v-if="hasSuccessfulResults" class="comparison-results">
    <div class="section-header">
      <h2 class="section-title">{{ title }}</h2>
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
</template>

<script setup lang="ts">
import { computed } from "vue";
import {
  comparisonMetrics,
  getAverageScore,
  getHostname,
  getScoreStatus,
  type ComparisonMetric,
  type ComparisonResult,
} from "../utils/comparisonHelpers";

const props = withDefaults(
  defineProps<{
    results: ComparisonResult[];
    title?: string;
  }>(),
  {
    title: "Сравнение показателей",
  }
);

const successfulResults = computed(() => {
  return props.results.filter((result): result is ComparisonResult & { data: NonNullable<ComparisonResult["data"]> } => {
    return result.data !== null;
  });
});

const failedResults = computed(() => {
  return props.results.filter((result) => result.data === null);
});

const hasSuccessfulResults = computed(() => successfulResults.value.length > 0);

const formatScore = (value: number | null): string => {
  if (value === null) return "--";
  return Math.round(value).toString();
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
</script>

<style scoped>
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

.comparison-table-card,
.site-summary-card,
.failed-sites {
  background: linear-gradient(135deg, var(--bg-secondary, #18181b) 0%, var(--bg-elevated, #1f1f23) 100%);
  border: 1px solid var(--border-color, #27272a);
  border-radius: var(--radius-xl, 24px);
  box-shadow: var(--shadow-lg);
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
  .section-header {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
