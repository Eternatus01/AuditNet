import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useAuditApi } from "../composables/useAuditApi";
import type { SecurityAudit, ScoreDisplay, AuditResource, AnalyzeWebsiteResponse } from "../types";

export const useAuditStore = defineStore("audit", () => {
  const auditApi = useAuditApi();

  const isLighthouseLoading = ref<boolean>(false);
  const isSecurityLoading = ref<boolean>(false);
  const error = ref<string | null>(null);

  const performanceScore = ref<number | null>(null);
  const accessibilityScore = ref<number | null>(null);
  const bestPracticesScore = ref<number | null>(null);
  const seoScore = ref<number | null>(null);

  const lcp = ref<number | null>(null);
  const fid = ref<number | null>(null);
  const cls = ref<number | null>(null);

  const fcp = ref<number | null>(null);
  const tbt = ref<number | null>(null);
  const speedIndex = ref<number | null>(null);

  const securityAudit = ref<SecurityAudit | null>(null);

  const resetMetrics = (): void => {
    error.value = null;
    performanceScore.value = null;
    accessibilityScore.value = null;
    bestPracticesScore.value = null;
    seoScore.value = null;

    lcp.value = null;
    fid.value = null;
    cls.value = null;

    fcp.value = null;
    tbt.value = null;
    speedIndex.value = null;
  };

  const analyzeWebsite = async (websiteUrl: string): Promise<AnalyzeWebsiteResponse | null> => {
    if (!websiteUrl.trim()) {
      error.value = "Введите URL сайта для анализа";
      return null;
    }

    isLighthouseLoading.value = true;
    error.value = null;

    resetMetrics();

    try {
      const response = await auditApi.analyzeWebsite(websiteUrl);

      if (response?.success) {
        return response;
      } else {
        error.value = "Не удалось запустить анализ";
        isLighthouseLoading.value = false;
        return null;
      }
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message;
      } else {
        error.value = "Ошибка при анализе сайта";
      }

      resetMetrics();
      isLighthouseLoading.value = false;
      return null;
    }
  };

  const fetchSecurityAudit = async (websiteUrl: string): Promise<void> => {
    isSecurityLoading.value = true;
    securityAudit.value = null;
    error.value = null;

    try {
      const response = await auditApi.fetchSecurityAudit(websiteUrl);

      if (response.error) {
        error.value = response.error || "Ошибка аудита безопасности";
        return;
      }

      securityAudit.value = response;
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message;
      } else {
        error.value = "Ошибка аудита безопасности";
      }
    } finally {
      isSecurityLoading.value = false;
    }
  };

  const performanceScoreDisplay = computed<ScoreDisplay>(() => performanceScore.value ?? '--');
  const accessibilityScoreDisplay = computed<ScoreDisplay>(() => accessibilityScore.value ?? '--');
  const bestPracticesScoreDisplay = computed<ScoreDisplay>(() => bestPracticesScore.value ?? '--');
  const seoScoreDisplay = computed<ScoreDisplay>(() => seoScore.value ?? '--');

  const checkAuditStatus = async (auditId: number) => {
    try {
      return await auditApi.checkAuditStatus(auditId);
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message;
      }
      return null;
    }
  };

  const updateFromPolling = (auditData: AuditResource): void => {
    performanceScore.value = auditData.performance ?? null;
    accessibilityScore.value = auditData.accessibility ?? null;
    bestPracticesScore.value = auditData.best_practices ?? null;
    seoScore.value = auditData.seo ?? null;

    lcp.value = auditData.lcp ?? null;
    fid.value = auditData.fid ?? null;
    cls.value = auditData.cls ?? null;

    fcp.value = auditData.fcp ?? null;
    tbt.value = auditData.tbt ?? null;
    speedIndex.value = auditData.speed_index ?? null;

    isLighthouseLoading.value = false;
    error.value = null;
  };

  const setError = (message: string): void => {
    error.value = message;
    isLighthouseLoading.value = false;
  };

  return {
    analyzeWebsite,
    fetchSecurityAudit,
    checkAuditStatus,
    updateFromPolling,
    setError,
    securityAudit,
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
    isLighthouseLoading,
    isSecurityLoading,
    error,
  };
});
