<template>
  <div class="hd-page">
    <LoadingState v-if="isLoading" text="Загрузка аудита..." size="lg" />

    <div v-else-if="error" class="hd-error">
      {{ error }}
    </div>

    <div v-else-if="audit" class="hd-content">
      <header class="hd-header">
        <div class="hd-header-left">
          <Button variant="secondary" size="md" @click="goBack">
            Назад к истории
          </Button>
        </div>
        <h1 class="hd-title">Детали аудита</h1>
        <div class="hd-header-right">
          <Button variant="primary" size="md" @click="repeatAudit">
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

      <div class="hd-report-actions">
        <ShareReportButton :get-share-url="getShareUrl" />
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
import { computed, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useHistoryApi } from "../composables/useHistoryApi";
import { useHistoryHelpers } from "../composables/useHistoryHelpers";
import { useToggle } from "@/shared/composables/useToggle";
import { useAuditDescriptions } from "@/features/dashboard/composables/useAuditDescriptions";
import { Button } from "@/shared/ui/atoms";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";
import DownloadReportButton from "@/shared/ui/molecules/DownloadReportButton.vue";
import ShareReportButton from "@/shared/ui/molecules/ShareReportButton.vue";
import type { Audit } from "../types";
import type { AuditReportData } from "@/features/dashboard/utils/pdf";
import type { SecurityAudit } from "@/features/dashboard/types";
import ScoresSection from "@/features/dashboard/components/ScoresSection.vue";
import CoreWebVitalsSection from "@/features/dashboard/components/CoreWebVitalsSection.vue";
import SecuritySection from "@/features/dashboard/components/SecuritySection.vue";
import RecommendationsSection from "@/features/dashboard/components/RecommendationsSection.vue";

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

const getShareUrl = async (): Promise<string> => {
  if (!audit.value?.id) {
    throw new Error("Аудит не найден");
  }

  const response = await historyApi.createAuditShareLink(audit.value.id);
  const token = response.data.token;

  return decodeURI(`${window.location.origin}/report/${token}`);
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
    securityAudit: a.security_audit
      ? {
          headers: a.security_audit.headers,
          sensitive_files: a.security_audit.sensitive_files,
          directory_listing: a.security_audit.directory_listing,
          robots_txt: a.security_audit.robots_txt,
          sitemap_xml: a.security_audit.sitemap_xml,
          scripts_info: a.security_audit.scripts_info,
        }
      : null,
    recommendations: a.recommendations || [],
  };
});

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

.hd-report-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  align-items: flex-end;
}

@media (max-width: 768px) {
  .hd-report-actions {
    flex-direction: column;
    justify-content: stretch;
  }

  .hd-report-actions > :deep(*) {
    width: 100%;
  }
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

