import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useAuditApi } from "../composables/useAuditApi";

export const useAuditStore = defineStore("audit", () => {
  const auditApi = useAuditApi();

  const isLighthouseLoading = ref(false);
  const isSecurityLoading = ref(false);
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

  const securityAudit = ref<any>(null);

  const resetMetrics = () => {
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

  const analyzeWebsite = async (websiteUrl: string) => {
    if (!websiteUrl.trim()) {
      error.value = "Введите URL сайта для анализа";
      return;
    }

    isLighthouseLoading.value = true;
    error.value = null;

    resetMetrics();

    try {
      const response: any = await auditApi.analyzeWebsite(websiteUrl);

      if (response?.success && response?.data) {
        performanceScore.value = response.data.performance ?? null;
        accessibilityScore.value = response.data.accessibility ?? null;
        bestPracticesScore.value = response.data.bestPractices ?? null;
        seoScore.value = response.data.seo ?? null;

        // Core Web Vitals
        lcp.value = response.data.lcp ?? null;
        fid.value = response.data.fid ?? null;
        cls.value = response.data.cls ?? null;

        // Дополнительные метрики
        fcp.value = response.data.fcp ?? null;
        tbt.value = response.data.tbt ?? null;
        speedIndex.value = response.data.speedIndex ?? null;
      } else {
        error.value = "Не удалось получить результаты анализа";
      }
    } catch (e: any) {
      error.value =
        e.response?.data?.message || e.message || "Ошибка при анализе сайта";

      resetMetrics();
    } finally {
      isLighthouseLoading.value = false;
    }
  };

  const fetchSecurityAudit = async (websiteUrl: string) => {
    isSecurityLoading.value = true;
    securityAudit.value = null;
    error.value = null;

    try {
      const response: any = await auditApi.fetchSecurityAudit(websiteUrl);

      if (response.error) {
        error.value = response.error || "Ошибка аудита безопасности";
        return;
      }

      securityAudit.value = response;
    } catch (e: any) {
      error.value =
        e.response?.data?.error || e.message || "Ошибка аудита безопасности";
    } finally {
      isSecurityLoading.value = false;
    }
  };

  // Computed для отображения с форматированием
  const performanceScoreDisplay = computed(() => performanceScore.value ?? '--');
  const accessibilityScoreDisplay = computed(() => accessibilityScore.value ?? '--');
  const bestPracticesScoreDisplay = computed(() => bestPracticesScore.value ?? '--');
  const seoScoreDisplay = computed(() => seoScore.value ?? '--');

  return {
    analyzeWebsite,
    fetchSecurityAudit,
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
