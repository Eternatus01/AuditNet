<template>
  <div class="comparison-detail-page">
    <LoadingState v-if="isLoading" text="Загрузка сравнения..." size="lg" />

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else-if="comparison" class="comparison-detail-content">
      <header class="comparison-detail-header">
        <div>
          <p class="comparison-eyebrow">Сохранённое сравнение</p>
          <h1>{{ comparison.title || "Сравнение сайтов" }}</h1>
          <p>{{ formatDate(comparison.audited_at || comparison.created_at) }}</p>
        </div>
        <Button variant="secondary" size="md" @click="router.push({ name: 'history' })">
          Назад к истории
        </Button>
      </header>

      <div class="comparison-actions">
        <ShareReportButton :get-share-url="getShareUrl" />
        <DownloadComparisonReportButton :comparison="comparison" />
      </div>

      <ComparisonResults :results="results" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Button } from "@/shared/ui/atoms";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import ShareReportButton from "@/shared/ui/molecules/ShareReportButton.vue";
import ComparisonResults from "../components/ComparisonResults.vue";
import DownloadComparisonReportButton from "../components/DownloadComparisonReportButton.vue";
import { useComparisonApi } from "../composables/useComparisonApi";
import { mapComparisonSiteToResult } from "../utils/mappers";
import { useHistoryHelpers } from "@/features/history/composables/useHistoryHelpers";
import type { AuditComparison } from "@/features/history/types";

const route = useRoute();
const router = useRouter();
const comparisonApi = useComparisonApi();
const { formatDate } = useHistoryHelpers();

const comparison = ref<AuditComparison | null>(null);
const isLoading = ref(false);
const error = ref("");

const results = computed(() => comparison.value?.sites.map(mapComparisonSiteToResult) || []);

const fetchComparison = async (): Promise<void> => {
  const id = route.params.id as string;
  if (!id) {
    error.value = "Сравнение не найдено";
    return;
  }

  isLoading.value = true;
  error.value = "";

  try {
    const response = await comparisonApi.fetchComparisonDetail(id);
    comparison.value = response.data;
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : "Ошибка при загрузке сравнения";
  } finally {
    isLoading.value = false;
  }
};

const getShareUrl = async (): Promise<string> => {
  if (!comparison.value?.id) {
    throw new Error("Сравнение не найдено");
  }

  const response = await comparisonApi.createComparisonShareLink(comparison.value.id);
  return decodeURI(`${window.location.origin}/comparison-report/${response.data.token}`);
};

onMounted(fetchComparison);
</script>

<style scoped>
.comparison-detail-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
}

.comparison-detail-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.comparison-detail-header {
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

.comparison-detail-header h1 {
  margin: 0;
}

.comparison-detail-header p:last-child {
  margin: 0.45rem 0 0;
  color: rgba(255, 255, 255, 0.65);
}

.comparison-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  align-items: flex-end;
}

@media (max-width: 768px) {
  .comparison-detail-page {
    padding: 1.25rem 1rem;
  }

  .comparison-detail-header,
  .comparison-actions {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
