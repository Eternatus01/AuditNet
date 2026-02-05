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
      <AuditCard
        v-for="audit in audits"
        :key="audit.id"
        :audit="audit"
        :format-date="formatDate"
        :get-score-class="getScoreClass"
        @click="viewAudit"
      />

      <PaginationControls
        :pagination="pagination"
        @page-change="loadPage"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useHistoryApi } from "../composables/useHistoryApi";
import { useHistoryHelpers } from "../composables/useHistoryHelpers";
import { Button } from "@/shared/ui/atoms";
import EmptyState from "@/shared/ui/molecules/EmptyState.vue";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import AuditCard from "../components/AuditCard.vue";
import PaginationControls from "../components/PaginationControls.vue";
import type { Audit, PaginationMeta } from "../types";

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

