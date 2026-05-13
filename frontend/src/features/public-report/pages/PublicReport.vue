<template>
  <div class="public-report-page">
    <LoadingState v-if="isLoading" text="Загрузка публичного отчёта..." size="lg" />

    <div v-else-if="error" class="public-report-error">
      {{ error }}
    </div>

    <div v-else-if="audit" class="public-report-content">
      <header class="public-report-header">
        <div>
          <p class="public-report-eyebrow">Публичный отчёт</p>
          <h1 class="public-report-title">Результаты анализа сайта</h1>
        </div>

        <Button variant="primary" size="md" @click="repeatAudit">
          Повторить проверку
        </Button>
      </header>

      <section class="public-report-meta">
        <div class="public-report-meta-item">
          <span class="public-report-meta-label">URL:</span>
          <a
            class="public-report-link"
            :href="audit.url"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ audit.url }}
          </a>
        </div>
        <div class="public-report-meta-item">
          <span class="public-report-meta-label">Дата проверки:</span>
          <span>{{ formatDate(audit.audited_at || audit.created_at) }}</span>
        </div>
      </section>

      <div class="public-report-actions">
        <DownloadReportButton :data="reportData" />
      </div>

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

        <SecuritySection
          v-if="securityAuditForView"
          :security-audit="securityAuditForView"
          :security-error="''"
          :is-security-ready="true"
          :descriptions="descriptions"
          :is-expanded="isExpanded"
          @toggle="toggle"
        />

        <RecommendationsSection
          v-if="audit.recommendations && audit.recommendations.length > 0"
          :recommendations="audit.recommendations"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Button } from "@/shared/ui/atoms";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import DownloadReportButton from "@/shared/ui/molecules/DownloadReportButton.vue";
import { useHistoryApi } from "@/features/history/composables/useHistoryApi";
import { useHistoryHelpers } from "@/features/history/composables/useHistoryHelpers";
import { useAuditDescriptions } from "@/features/dashboard/composables/useAuditDescriptions";
import { useToggle } from "@/shared/composables/useToggle";
import type { Audit } from "@/features/history/types";
import type { AuditReportData } from "@/features/dashboard/utils/pdf";
import type { SecurityAudit } from "@/features/dashboard/types";
import ScoresSection from "@/features/dashboard/components/ScoresSection.vue";
import CoreWebVitalsSection from "@/features/dashboard/components/CoreWebVitalsSection.vue";
import SecuritySection from "@/features/dashboard/components/SecuritySection.vue";
import RecommendationsSection from "@/features/dashboard/components/RecommendationsSection.vue";

const route = useRoute();
const router = useRouter();
const historyApi = useHistoryApi();
const descriptions = useAuditDescriptions();
const { formatDate } = useHistoryHelpers();
const { toggle, isExpanded } = useToggle();

const audit = ref<Audit | null>(null);
const isLoading = ref(false);
const error = ref("");

const fetchPublicReport = async () => {
  const token = route.params.token as string;

  if (!token) {
    error.value = "Публичный отчёт не найден";
    return;
  }

  isLoading.value = true;
  error.value = "";

  try {
    const response = await historyApi.fetchPublicAudit(token);

    if (response?.success && response.data) {
      audit.value = response.data;
    } else {
      error.value = "Не удалось загрузить публичный отчёт";
    }
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : "Ошибка при загрузке публичного отчёта";
  } finally {
    isLoading.value = false;
  }
};

const repeatAudit = () => {
  if (!audit.value?.url) return;

  router.push({
    name: "dashboard",
    query: { url: audit.value.url, autoStart: "true" },
  });
};

const securityAuditForView = computed<SecurityAudit | null>(() => {
  const source = audit.value?.security_audit;

  if (!source) return null;

  return {
    checked_url: audit.value?.url || "",
    host: audit.value?.url || "",
    headers: source.headers,
    sensitive_files: source.sensitive_files,
    directory_listing: source.directory_listing,
    robots_txt: source.robots_txt ? "Найден" : null,
    sitemap_xml: source.sitemap_xml,
    scripts_info: source.scripts_info.map((script) => script.src),
  };
});

const reportData = computed<AuditReportData>(() => {
  const a = audit.value;

  if (!a) {
    return {
      url: "—",
      auditedAt: null,
      scores: { performance: null, accessibility: null, bestPractices: null, seo: null },
      vitals: { lcp: null, fid: null, cls: null, fcp: null, tbt: null, speedIndex: null },
      securityAudit: null,
      recommendations: [],
    };
  }

  return {
    url: a.url,
    auditedAt: a.audited_at || a.created_at,
    scores: {
      performance: a.performance,
      accessibility: a.accessibility,
      bestPractices: a.best_practices,
      seo: a.seo,
    },
    vitals: {
      lcp: a.lcp,
      fid: a.fid,
      cls: a.cls,
      fcp: a.fcp,
      tbt: a.tbt,
      speedIndex: a.speed_index,
    },
    securityAudit: a.security_audit || null,
    recommendations: a.recommendations || [],
  };
});

onMounted(fetchPublicReport);
</script>

<style scoped>
.public-report-page {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 2rem;
}

.public-report-error {
  padding: 3rem 1rem;
  color: #ff6b6b;
  text-align: center;
}

.public-report-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.public-report-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.public-report-eyebrow {
  margin: 0 0 0.35rem;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.public-report-title {
  margin: 0;
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
  font-size: 1.9rem;
  font-weight: 800;
}

.public-report-meta {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  background: #1a1a1a;
  border: 1px solid #333;
  border-radius: 12px;
}

.public-report-meta-item {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
}

.public-report-meta-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 600;
}

.public-report-link {
  color: #646cff;
  text-decoration: none;
  word-break: break-all;
}

.public-report-link:hover {
  text-decoration: underline;
}

.public-report-actions {
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 768px) {
  .public-report-page {
    padding: 1.25rem 1rem;
  }

  .public-report-header {
    align-items: stretch;
    flex-direction: column;
  }

  .public-report-title {
    font-size: 1.5rem;
  }

  .public-report-actions {
    justify-content: stretch;
  }

  .public-report-actions > :deep(*) {
    width: 100%;
  }
}
</style>
