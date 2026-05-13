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

    <LoadingState
      v-if="isLoading && !isAuditReady"
      text="Анализ выполняется... Это может занять 30–60 секунд"
      size="lg"
    />


    <div v-if="isAuditReady" class="dashboard-report-actions">
      <ShareReportButton
        v-if="isAuthenticated && currentAuditId"
        :get-share-url="getCurrentShareUrl"
      />
      <DownloadReportButton :data="reportData" />
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

    <div v-if="!isAuthenticated && isAuditReady" class="guest-banner">
      <div class="guest-banner-content">
        <span class="guest-banner-icon">💾</span>
        <div>
          <strong>Результаты не сохранены</strong>
          <p>Зарегистрируйтесь, чтобы сохранять историю аудитов и получить доступ к аналитике.</p>
        </div>
        <button class="guest-banner-btn" @click="router.push({ name: 'profile' })">
          Войти / Зарегистрироваться
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import { useAuditStore } from "../stores/audit";
import { useAuthStore } from "@/features/auth/stores/auth";
import { useAuditDescriptions } from "../composables/useAuditDescriptions";
import { useToggle } from "@/shared/composables/useToggle";
import { logger } from "@/shared/utils/logger";
import UrlInputSection from "../components/UrlInputSection.vue";
import ScoresSection from "../components/ScoresSection.vue";
import CoreWebVitalsSection from "../components/CoreWebVitalsSection.vue";
import SecuritySection from "../components/SecuritySection.vue";
import RecommendationsSection from "../components/RecommendationsSection.vue";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import DownloadReportButton from "@/shared/ui/molecules/DownloadReportButton.vue";
import ShareReportButton from "@/shared/ui/molecules/ShareReportButton.vue";
import { useHistoryApi } from "@/features/history/composables/useHistoryApi";
import type { AuditReportData } from "../utils/pdf";

const route = useRoute();
const router = useRouter();
const auditStore = useAuditStore();
const authStore = useAuthStore();
const historyApi = useHistoryApi();
const descriptions = useAuditDescriptions();
const { expandedItems: expandedInfo, toggle: toggleInfo } = useToggle();
const { isAuthenticated } = storeToRefs(authStore);

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
  fcp,
  tbt,
  speedIndex,
  securityAudit,
  recommendations,
  currentAuditId,
} = storeToRefs(auditStore);

const websiteUrl = ref("");

const isLoading = computed(() => 
  isLighthouseLoading.value || isSecurityLoading.value
);

const securityError = computed(() => error.value);

const analyzeWebsite = async () => {
  if (isAuthenticated.value) {
    const lighthousePromise = auditStore
      .analyzeWebsite(websiteUrl.value)
      .catch((err) => { logger.error("Lighthouse error:", err); });

    const securityPromise = auditStore
      .fetchSecurityAudit(websiteUrl.value)
      .catch((err) => { logger.error("Security audit error:", err); });

    await Promise.all([lighthousePromise, securityPromise]);
  } else {
    await auditStore.analyzeGuestWebsite(websiteUrl.value)
      .catch((err) => { logger.error("Guest audit error:", err); });
  }
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

const isAuditReady = computed(() => {
  return [
    performanceScore.value,
    accessibilityScore.value,
    bestPracticesScore.value,
    seoScore.value,
  ].some((v) => v !== null && typeof v === "number");
});

const reportData = computed<AuditReportData>(() => ({
  url: websiteUrl.value || "—",
  auditedAt: new Date().toISOString(),
  scores: {
    performance: performanceScore.value,
    accessibility: accessibilityScore.value,
    bestPractices: bestPracticesScore.value,
    seo: seoScore.value,
  },
  vitals: {
    lcp: lcp.value,
    fid: fid.value,
    cls: cls.value,
    fcp: fcp.value,
    tbt: tbt.value,
    speedIndex: speedIndex.value,
  },
  securityAudit: securityAudit.value,
  recommendations: recommendations.value,
}));

const getCurrentShareUrl = async (): Promise<string> => {
  if (!currentAuditId.value) {
    throw new Error("Аудит ещё не сохранён");
  }

  const response = await historyApi.createAuditShareLink(currentAuditId.value);
  const token = response.data.token;

  return `${window.location.origin}/report/${token}`;
};

const isSecurityReady = computed(() => {
  return securityAudit.value !== null || auditStore.isSecurityLoading;
});
</script>

<style scoped>

.dashboard-report-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  align-items: flex-start;
  margin: 1.5rem 0 0.5rem;
}

@media (max-width: 768px) {
  .dashboard-report-actions {
    flex-direction: column;
    justify-content: stretch;
  }

  .dashboard-report-actions > :deep(*) {
    width: 100%;
  }
}

.guest-banner {
  margin: 2rem 0;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.12), rgba(124, 58, 237, 0.05));
  border: 1px solid rgba(124, 58, 237, 0.35);
  border-radius: 14px;
  padding: 1.25rem 1.5rem;
  animation: fadeIn 0.4s ease-out;
}

.guest-banner-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.guest-banner-icon {
  font-size: 1.75rem;
  flex-shrink: 0;
}

.guest-banner-content > div {
  flex: 1;
  min-width: 200px;
}

.guest-banner-content strong {
  display: block;
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 0.25rem;
}

.guest-banner-content p {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
}

.guest-banner-btn {
  flex-shrink: 0;
  padding: 0.6rem 1.25rem;
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
  white-space: nowrap;
}

.guest-banner-btn:hover {
  background: #6d28d9;
}

@media (max-width: 768px) {
  .guest-banner-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .guest-banner-btn {
    width: 100%;
    text-align: center;
    white-space: normal;
  }

}
</style>
