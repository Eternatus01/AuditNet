<template>
  <div class="analytics-container">
    <div v-if="!selectedUrl" class="analytics-header">
      <h1>Аналитика производительности</h1>
      <p class="analytics-subtitle">
        Отслеживайте изменения производительности ваших сайтов во времени
      </p>
    </div>

    <LoadingState v-if="isLoadingUrls" text="Загрузка списка сайтов..." size="lg" />

    <div v-else-if="availableUrls.length === 0" class="empty-state">
      <div class="empty-icon">🔍</div>
      <h3>Нет данных для аналитики</h3>
      <p>Сначала выполните проверку сайтов на главной странице</p>
    </div>

    <AnalyticsSitesList
      v-else-if="!selectedUrl"
      :sites-data="sitesData"
      @select="handleSiteSelect"
    />

    <LoadingState v-if="selectedUrl && isLoading" text="Загрузка данных аналитики..." size="lg" />

    <div v-else-if="selectedUrl && error" class="error-state">
      <p>{{ error }}</p>
    </div>

    <div v-else-if="selectedUrl && analytics" class="analytics-content">
      <AnalyticsHeader
        :url="selectedUrl"
        :total-audits="analytics.totalAudits"
        :last-audit="formatShortDate(analytics.lastAudit)"
        @back="selectedUrl = ''"
      />

      <AnalyticsStats
        :total-audits="analytics.totalAudits"
        :first-audit="formatShortDate(analytics.firstAudit)"
        :last-audit="formatShortDate(analytics.lastAudit)"
        :average-score="averageScore"
        :trend-direction="trendDirection"
        :trend-text="trendText"
      />

      <div class="charts-grid">
        <MetricChart
          title="Основные показатели Lighthouse"
          :data="analytics.data"
          :metrics="lighthouseMetrics"
        />

        <MetricChart
          title="Core Web Vitals"
          :data="analytics.data"
          :metrics="coreWebVitalsMetrics"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useAnalyticsApi } from '../composables/useAnalyticsApi';
import type { SiteAnalytics } from '../types';
import AnalyticsSitesList from '../components/AnalyticsSitesList.vue';
import AnalyticsHeader from '../components/AnalyticsHeader.vue';
import AnalyticsStats from '../components/AnalyticsStats.vue';
import MetricChart from '../components/MetricChart.vue';
import LoadingState from '@/shared/ui/molecules/LoadingState.vue';
import { logger } from '@/shared/utils/logger';

const analyticsApi = useAnalyticsApi();

const selectedUrl = ref('');
const availableUrls = ref<string[]>([]);
const sitesMetadata = ref<Record<string, { count: number; lastAudit: string }>>({});
const analytics = ref<SiteAnalytics | null>(null);
const isLoading = ref(false);
const isLoadingUrls = ref(false);
const error = ref<string | null>(null);

const filters = ref({
  dateFrom: undefined as string | undefined,
  dateTo: undefined as string | undefined,
  limit: 20,
});

const sitesData = computed(() => {
  return availableUrls.value.map(url => ({
    url,
    count: sitesMetadata.value[url]?.count || 0,
    lastAudit: formatDate(sitesMetadata.value[url]?.lastAudit || new Date().toISOString()),
  }));
});

const lighthouseMetrics = [
  { key: 'performance' as const, label: 'Производительность', color: '#646cff' },
  { key: 'accessibility' as const, label: 'Доступность', color: '#4caf50' },
  { key: 'best_practices' as const, label: 'Лучшие практики', color: '#ff9800' },
  { key: 'seo' as const, label: 'SEO', color: '#9c27b0' },
];

const coreWebVitalsMetrics = [
  { key: 'lcp' as const, label: 'LCP (мс)', color: '#2196f3' },
  { key: 'fid' as const, label: 'FID (мс)', color: '#f44336' },
  { key: 'cls' as const, label: 'CLS (×1000)', color: '#ffeb3b' },
];

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatShortDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

const averageScore = computed(() => {
  if (!analytics.value || analytics.value.data.length === 0) return 0;
  const latestData = analytics.value.data[0];
  const scores = [
    latestData.performance,
    latestData.accessibility,
    latestData.best_practices,
    latestData.seo,
  ].filter((score): score is number => score !== null);
  
  if (scores.length === 0) return 0;
  return Math.round(scores.reduce((a, b) => a + b, 0) / scores.length);
});

const trendDirection = computed(() => {
  if (!analytics.value || analytics.value.data.length < 2) return 'neutral';
  
  const latest = analytics.value.data[0];
  const previous = analytics.value.data[1];
  
  const latestAvg = [latest.performance, latest.accessibility, latest.best_practices, latest.seo]
    .filter((s): s is number => s !== null)
    .reduce((a, b) => a + b, 0) / 4;
    
  const previousAvg = [previous.performance, previous.accessibility, previous.best_practices, previous.seo]
    .filter((s): s is number => s !== null)
    .reduce((a, b) => a + b, 0) / 4;
  
  if (latestAvg > previousAvg + 2) return 'up';
  if (latestAvg < previousAvg - 2) return 'down';
  return 'neutral';
});

const trendText = computed(() => {
  if (trendDirection.value === 'up') return 'Улучшение';
  if (trendDirection.value === 'down') return 'Снижение';
  return 'Стабильно';
});

const loadUrls = async () => {
  isLoadingUrls.value = true;
  try {
    const response = await analyticsApi.getUniqueUrls();
    if (response.success) {
      availableUrls.value = response.data.map(item => item.url);
      sitesMetadata.value = response.data.reduce((acc, item) => {
        acc[item.url] = {
          count: item.count,
          lastAudit: item.last_audit,
        };
        return acc;
      }, {} as Record<string, { count: number; lastAudit: string }>);
    }
  } catch (err) {
    logger.error('Ошибка загрузки списка URL:', err);
  } finally {
    isLoadingUrls.value = false;
  }
};

const handleSiteSelect = (url: string) => {
  selectedUrl.value = url;
};

const loadAnalytics = async () => {
  if (!selectedUrl.value) {
    analytics.value = null;
    return;
  }

  isLoading.value = true;
  error.value = null;

  try {
    const response = await analyticsApi.getAnalytics(
      selectedUrl.value,
      filters.value.limit
    );

    if (response.success) {
      analytics.value = response.data;
    } else {
      error.value = 'Не удалось загрузить данные аналитики';
    }
  } catch (err: any) {
    logger.error('Ошибка загрузки аналитики:', err);
    error.value = err.message || 'Ошибка при загрузке данных';
  } finally {
    isLoading.value = false;
  }
};

watch(selectedUrl, () => {
  if (selectedUrl.value) {
    loadAnalytics();
  }
});

onMounted(() => {
  loadUrls();
});
</script>

<style scoped>
.analytics-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.analytics-header {
  margin-bottom: 3rem;
  text-align: center;
  padding: 2rem 0;
}

.analytics-header h1 {
  font-size: 2.75rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
  color: #fff;
  letter-spacing: -0.5px;
}

.analytics-subtitle {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.15rem;
  margin: 0;
  font-weight: 400;
}

.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.error-state p {
  color: #ff6b6b;
  font-size: 1.1rem;
}

.empty-state {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.9) 0%, rgba(30, 30, 50, 0.9) 100%);
  border-radius: 16px;
  border: 1px solid rgba(100, 108, 255, 0.2);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: #fff;
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  color: rgba(255, 255, 255, 0.6);
  font-size: 1rem;
  margin: 0;
}

.analytics-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.charts-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

@media (max-width: 768px) {
  .analytics-container {
    padding: 1rem;
  }

  .analytics-header h1 {
    font-size: 2rem;
  }
}
</style>
