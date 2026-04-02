<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Аудит сайта</h1>
      <p class="dashboard-subtitle">
        Введите URL сайта для анализа производительности
      </p>
      <div class="examples-hint">
        Например: <span class="hint-url">google.com</span> или <span class="hint-url">github.com</span>
      </div>
    </div>

    <UrlInputSection
      v-model="websiteUrl"
      :is-loading="isLoading"
      @analyze="analyzeWebsite"
    />

    <div v-if="isLoading && !isAuditReady" class="loading-indicator">
      <div class="loading-spinner"></div>
      <h3>Анализ выполняется...</h3>
      <p>Это может занять 30-60 секунд. Пожалуйста, подождите.</p>
      <div class="loading-dots">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>

    <div class="dashboard-results" :class="{ hidden: !isAuditReady }">
      <ScoresSection
        :performance-score="performanceScoreDisplay"
        :accessibility-score="accessibilityScoreDisplay"
        :best-practices-score="bestPracticesScoreDisplay"
        :seo-score="seoScoreDisplay"
        :descriptions="descriptions"
        :is-expanded="(key) => expandedInfo.has(key)"
        @toggle="toggleInfo"
      />

      <CoreWebVitalsSection
        :lcp="lcp"
        :fid="fid"
        :cls="cls"
        :descriptions="descriptions"
        :is-expanded="(key) => expandedInfo.has(key)"
        @toggle="toggleInfo"
      />
    </div>

    <SecuritySection
      :security-audit="securityAudit" 
      :security-error="securityError || ''"
      :is-security-ready="isSecurityReady"
      :descriptions="descriptions"
      :is-expanded="(key) => expandedInfo.has(key)"
      @toggle="toggleInfo"
    />

    <RecommendationsSection
      v-if="recommendations && recommendations.length > 0"
      :recommendations="recommendations"
    />

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onBeforeUnmount, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { useAuditStore } from "../stores/audit";
import { useAuditDescriptions } from "../composables/useAuditDescriptions";
import { useToggle } from "@/shared/composables/useToggle";
import { logger } from "@/shared/utils/logger";
import UrlInputSection from "../components/UrlInputSection.vue";
import ScoresSection from "../components/ScoresSection.vue";
import CoreWebVitalsSection from "../components/CoreWebVitalsSection.vue";
import SecuritySection from "../components/SecuritySection.vue";
import RecommendationsSection from "../components/RecommendationsSection.vue";

const POLLING_INTERVAL_MS = 10000;
const MAX_POLLING_ATTEMPTS = 30;

const route = useRoute();
const auditStore = useAuditStore();
const descriptions = useAuditDescriptions();
const { expandedItems: expandedInfo, toggle: toggleInfo } = useToggle();

const {
  isLighthouseLoading,
  isSecurityLoading,
  error,
  performanceScore,
  accessibilityScore,
  bestPracticesScore,
  seoScore,
  performanceScoreDisplay,
  accessibilityScoreDisplay,
  bestPracticesScoreDisplay,
  seoScoreDisplay,
  lcp,
  fid,
  cls,
  securityAudit,
  recommendations,
} = storeToRefs(auditStore);

const websiteUrl = ref("");
const pollInterval = ref<ReturnType<typeof setInterval> | null>(null);
const pollingAttempts = ref(0);

const isLoading = computed(() => 
  isLighthouseLoading.value || isSecurityLoading.value
);

const securityError = computed(() => error.value);

const analyzeWebsite = async () => {
  const lighthousePromise = auditStore
    .analyzeWebsite(websiteUrl.value)
    .catch((err) => {
      logger.error("Lighthouse error:", err);
    });

  const securityPromise = auditStore
    .fetchSecurityAudit(websiteUrl.value)
    .catch((err) => {
      logger.error("Security audit error:", err);
    });

  await Promise.all([lighthousePromise, securityPromise]);
};

const clearPolling = () => {
  if (pollInterval.value) {
    clearInterval(pollInterval.value);
    pollInterval.value = null;
    pollingAttempts.value = 0;
  }
};

const startPolling = (auditId: number) => {
  clearPolling();

  pollInterval.value = setInterval(async () => {
    pollingAttempts.value++;

    if (pollingAttempts.value >= MAX_POLLING_ATTEMPTS) {
      clearPolling();
      auditStore.setError('Превышено время ожидания анализа. Попробуйте позже.');
      return;
    }

    try {
      const response = await auditStore.checkAuditStatus(auditId);

      if (!response || !response.data) return;

      const auditData = response.data;

      if (auditData.status === 'completed') {
        clearPolling();
        auditStore.updateFromPolling(auditData);
      } else if (auditData.status === 'failed') {
        clearPolling();
        auditStore.setError(auditData.error_message || 'Ошибка анализа');
      }
    } catch (err) {
      logger.error('Polling error:', err);
    }
  }, POLLING_INTERVAL_MS);
};

onMounted(() => {
  const urlParam = route.query.url as string;
  const autoStart = route.query.autoStart as string;

  if (urlParam) {
    websiteUrl.value = urlParam;

    if (autoStart === 'true') {
      setTimeout(() => {
        analyzeWebsite();
      }, 500);
    }
  }
});

onBeforeUnmount(() => {
  clearPolling();
});

const isAuditReady = computed(() => {
  return [
    performanceScore.value,
    accessibilityScore.value,
    bestPracticesScore.value,
    seoScore.value,
  ].some((v) => v !== null && typeof v === "number");
});

const isSecurityReady = computed(() => {
  return securityAudit.value !== null || auditStore.isSecurityLoading;
});
</script>

<style scoped>
.loading-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 2rem;
  margin: 2rem 0;
  background: linear-gradient(135deg, rgba(100, 108, 255, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%);
  border: 2px solid rgba(100, 108, 255, 0.3);
  border-radius: 16px;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.loading-spinner {
  width: 60px;
  height: 60px;
  border: 4px solid rgba(100, 108, 255, 0.2);
  border-top-color: #646cff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1.5rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-indicator h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.5rem 0;
}

.loading-indicator p {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0 0 1.5rem 0;
}

.loading-dots {
  display: flex;
  gap: 0.5rem;
}

.loading-dots span {
  width: 12px;
  height: 12px;
  background: #646cff;
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
}

.loading-dots span:nth-child(1) {
  animation-delay: -0.32s;
}

.loading-dots span:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
