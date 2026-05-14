<template>
  <div class="public-comparison-page">
    <LoadingState v-if="isLoading" text="Загрузка публичного сравнения..." size="lg" />

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else-if="comparison" class="public-comparison-content">
      <header class="public-comparison-header">
        <div>
          <p class="comparison-eyebrow">Публичное сравнение</p>
          <h1>{{ comparison.title || "Сравнение сайтов" }}</h1>
          <p>{{ formatDate(comparison.audited_at || comparison.created_at) }}</p>
        </div>
        <DownloadComparisonReportButton :comparison="comparison" />
      </header>

      <ComparisonResults :results="results" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import ComparisonResults from "../components/ComparisonResults.vue";
import DownloadComparisonReportButton from "../components/DownloadComparisonReportButton.vue";
import { useComparisonApi } from "../composables/useComparisonApi";
import { mapComparisonSiteToResult } from "../utils/mappers";
import { useHistoryHelpers } from "@/features/history/composables/useHistoryHelpers";
import type { AuditComparison } from "@/features/history/types";

const route = useRoute();
const comparisonApi = useComparisonApi();
const { formatDate } = useHistoryHelpers();

const comparison = ref<AuditComparison | null>(null);
const isLoading = ref(false);
const error = ref("");

const results = computed(() => comparison.value?.sites.map(mapComparisonSiteToResult) || []);

const fetchPublicComparison = async (): Promise<void> => {
  const token = route.params.token as string;
  if (!token) {
    error.value = "Публичное сравнение не найдено";
    return;
  }

  isLoading.value = true;
  error.value = "";

  try {
    const response = await comparisonApi.fetchPublicComparison(token);
    comparison.value = response.data;
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : "Ошибка при загрузке публичного сравнения";
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchPublicComparison);
</script>

<style scoped>
.public-comparison-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
}

.public-comparison-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.public-comparison-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.comparison-eyebrow {
  margin: 0 0 0.35rem;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.public-comparison-header h1 {
  margin: 0;
}

.public-comparison-header p:last-child {
  margin: 0.45rem 0 0;
  color: rgba(255, 255, 255, 0.65);
}

@media (max-width: 768px) {
  .public-comparison-page {
    padding: 1.25rem 1rem;
  }

  .public-comparison-header {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
