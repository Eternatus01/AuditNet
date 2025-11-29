<template>
  <div class="history-container">
    <div class="history-header">
      <h1>История аудитов</h1>
      <p class="history-subtitle">
        Просмотр всех проведённых аудитов
      </p>
    </div>

    <LoadingState v-if="isLoading" text="Загрузка истории..." />

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <EmptyState
      v-else-if="!audits || audits.length === 0"
      title="История аудитов пуста"
      description="Проведите первый аудит сайта"
    >
      <template #action>
        <Button variant="primary" size="lg" @click="router.push('/dashboard')">
          Перейти к аудиту
        </Button>
      </template>
    </EmptyState>

    <div v-else-if="audits && audits.length > 0" class="audits-list">
      <div
        v-for="audit in audits"
        :key="audit.id"
        class="audit-card"
        @click="viewAudit(audit.id)"
      >
        <div class="audit-main">
          <div class="audit-url">
            <IconLucideGlobe />
            <span>{{ audit.url }}</span>
          </div>
          <div class="audit-date">
            <IconLucideClock />
            <span>{{ formatDate(audit.created_at) }}</span>
          </div>
        </div>

        <div class="audit-scores">
          <div class="score-item" :class="getScoreClass(audit.performance)">
            <span class="score-label">Performance</span>
            <span class="score-value">{{ audit.performance }}</span>
          </div>
          <div class="score-item" :class="getScoreClass(audit.accessibility)">
            <span class="score-label">Accessibility</span>
            <span class="score-value">{{ audit.accessibility }}</span>
          </div>
          <div class="score-item" :class="getScoreClass(audit.best_practices)">
            <span class="score-label">Best Practices</span>
            <span class="score-value">{{ audit.best_practices }}</span>
          </div>
          <div class="score-item" :class="getScoreClass(audit.seo)">
            <span class="score-label">SEO</span>
            <span class="score-value">{{ audit.seo }}</span>
          </div>
        </div>

        <div class="audit-vitals">
          <div class="vital-item">
            <span class="vital-label">LCP</span>
            <span class="vital-value">{{ formatLCP(audit.lcp) }}</span>
          </div>
          <div class="vital-item">
            <span class="vital-label">FID</span>
            <span class="vital-value">{{ formatFID(audit.fid) }}</span>
          </div>
          <div class="vital-item">
            <span class="vital-label">CLS</span>
            <span class="vital-value">{{ formatCLS(audit.cls) }}</span>
          </div>
        </div>
      </div>

      <div v-if="pagination && pagination.last_page > 1" class="pagination">
        <Button
          variant="secondary"
          size="md"
          :disabled="pagination.current_page === 1"
          @click="loadPage(pagination.current_page - 1)"
        >
          <template #icon-left>
            <IconLucideChevronLeft />
          </template>
          Назад
        </Button>
        <span class="page-info">
          Страница {{ pagination.current_page }} из {{ pagination.last_page }}
        </span>
        <Button
          variant="secondary"
          size="md"
          :disabled="pagination.current_page === pagination.last_page"
          @click="loadPage(pagination.current_page + 1)"
        >
          Вперёд
          <template #icon-right>
            <IconLucideChevronRight />
          </template>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { formatLCP, formatFID, formatCLS } from "@/shared/utils/formatters";
import { useHistoryApi } from "../composables/useHistoryApi";
import { useHistoryHelpers } from "../composables/useHistoryHelpers";
import { Button } from "@/shared/ui/atoms";
import EmptyState from "@/shared/ui/molecules/EmptyState.vue";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import type { Audit, PaginationMeta } from "../types";
import IconLucideGlobe from "~icons/lucide/globe";
import IconLucideClock from "~icons/lucide/clock";
import IconLucideChevronRight from "~icons/lucide/chevron-right";
import IconLucideChevronLeft from "~icons/lucide/chevron-left";

const router = useRouter();
const historyApi = useHistoryApi();
const { formatDate, getScoreClass } = useHistoryHelpers();

const audits = ref<Audit[]>([]);
const pagination = ref<PaginationMeta | null>(null);
const isLoading = ref<boolean>(false);
const error = ref<string>("");

const fetchHistory = async (page = 1): Promise<void> => {
  isLoading.value = true;
  error.value = "";
  audits.value = [];

  try {
    const response = await historyApi.fetchHistory(page);

    if (response?.success && response.data) {
      audits.value = Array.isArray(response.data) ? response.data : [];
      
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        total: response.total,
      };
    } else {
      error.value = "Не удалось загрузить историю";
    }
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : "Ошибка при загрузке истории";
  } finally {
    isLoading.value = false;
  }
};

const loadPage = (page: number): void => {
  fetchHistory(page);
};

const viewAudit = (id: number): void => {
  router.push(`/history/${id}`);
};

onMounted(() => {
  fetchHistory();
});
</script>

