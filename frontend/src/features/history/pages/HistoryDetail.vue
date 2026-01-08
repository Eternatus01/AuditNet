<template>
  <div class="hd-page">
    <LoadingState v-if="isLoading" text="Загрузка аудита..." />

    <div v-else-if="error" class="hd-error">
      {{ error }}
    </div>

    <div v-else-if="audit" class="hd-content">
      <header class="hd-header">
        <div class="hd-header-left">
          <Button variant="secondary" size="md" @click="goBack">
            <template #icon-left>
              <IconLucideArrowLeft />
            </template>
            Назад к истории
          </Button>
        </div>
        <h1 class="hd-title">Детали аудита</h1>
        <div class="hd-header-right">
          <Button variant="primary" size="md" @click="repeatAudit">
            <template #icon-left>
              <IconLucideRefreshCw />
            </template>
            Повторить проверку
          </Button>
        </div>
      </header>

      <section class="hd-meta">
        <div class="hd-meta-item">
          <span class="hd-meta-label">URL:</span>
          <a
            class="hd-meta-value hd-link"
            :href="audit.url"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ audit.url }}
          </a>
        </div>
        <div class="hd-meta-item">
          <span class="hd-meta-label">Дата проверки:</span>
          <span class="hd-meta-value">{{ formatDate(audit.created_at) }}</span>
        </div>
      </section>

      <div class="dashboard-results">
        <ScoresSection
          :performance-score="audit.performance ?? '--'"
          :accessibility-score="audit.accessibility ?? '--'"
          :best-practices-score="audit.best_practices ?? '--'"
          :seo-score="audit.seo ?? '--'"
          :descriptions="descriptions"
          :is-expanded="isExpanded"
          @toggle="toggle"
        />

        <CoreWebVitalsSection
          :lcp="audit.lcp"
          :fid="audit.fid"
          :cls="audit.cls"
          :descriptions="descriptions"
          :is-expanded="isExpanded"
          @toggle="toggle"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useHistoryApi } from "../composables/useHistoryApi";
import { useHistoryHelpers } from "../composables/useHistoryHelpers";
import { useToggle } from "@/shared/composables/useToggle";
import { useAuditDescriptions } from "@/features/dashboard/composables/useAuditDescriptions";
import { Button } from "@/shared/ui/atoms";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import type { Audit } from "../types";
import ScoresSection from "@/features/dashboard/components/ScoresSection.vue";
import CoreWebVitalsSection from "@/features/dashboard/components/CoreWebVitalsSection.vue";
import IconLucideArrowLeft from "~icons/lucide/arrow-left";
import IconLucideRefreshCw from "~icons/lucide/refresh-cw";

const router = useRouter();
const route = useRoute();
const historyApi = useHistoryApi();
const { formatDate } = useHistoryHelpers();
const { toggle, isExpanded } = useToggle();
const descriptions = useAuditDescriptions();

const audit = ref<Audit | null>(null);
const isLoading = ref<boolean>(false);
const error = ref<string>("");

const fetchAudit = async (id: string): Promise<void> => {
  isLoading.value = true;
  error.value = "";
  audit.value = null;

  try {
    const response = await historyApi.fetchAuditDetail(id);

    if (response?.success && response.data) {
      const auditData = 'data' in response.data && response.data.data
        ? response.data.data
        : response.data;
      
      if (auditData && typeof auditData === 'object' && 'id' in auditData) {
        audit.value = auditData as Audit;
      } else {
        error.value = "Не удалось загрузить данные аудита: неверный формат";
      }
    } else {
      error.value = "Не удалось загрузить данные аудита";
      audit.value = null;
    }
  } catch (e: unknown) {
    if (e instanceof Error) {
      error.value = e.message;
    } else {
      error.value = "Ошибка при загрузке аудита";
    }
    audit.value = null;
  } finally {
    isLoading.value = false;
  }
};

const goBack = (): void => {
  router.push({ name: "history" });
};

const repeatAudit = (): void => {
  if (audit.value?.url) {
    router.push({ 
      name: "dashboard", 
      query: { url: audit.value.url, autoStart: 'true' } 
    });
  }
};

onMounted(() => {
  const id = route.params.id as string;
  if (id) {
    fetchAudit(id);
  } else {
    error.value = "Аудит не найден";
  }
});
</script>

<style scoped>
.hd-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  width: 100%;
}

.hd-error {
  text-align: center;
  padding: 3rem 1rem;
  color: #ff6b6b;
}

.hd-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.hd-header {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 1rem;
}

.hd-header-left,
.hd-header-right {
  display: flex;
  align-items: center;
}

.hd-title {
  text-align: center;
}

.hd-title {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
}

.hd-meta {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  background: #1a1a1a;
  border: 1px solid #333;
}

.hd-meta-item {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  font-size: 0.95rem;
}

.hd-meta-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 600;
}

.hd-meta-value {
  color: rgba(255, 255, 255, 0.9);
}

.hd-link {
  color: #646cff;
  text-decoration: none;
  word-break: break-all;
}

.hd-link:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .hd-page {
    padding: 1.25rem 1rem;
  }

  .hd-header {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .hd-header-left {
    order: 1;
  }

  .hd-title {
    order: 2;
    text-align: left;
    font-size: 1.5rem;
  }

  .hd-header-right {
    order: 3;
  }
}
</style>

