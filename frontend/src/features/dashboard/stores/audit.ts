import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuditApi } from "../composable/useAuditApi";

export const useAuditStore = defineStore("audit", () => {
  const auditApi = useAuditApi();
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const performanceScore = ref<number | string>("--");
  const accessibilityScore = ref<number | string>("--");
  const bestPracticesScore = ref<number | string>("--");
  const seoScore = ref<number | string>("--");

  const lcp = ref<number | null>(null);
  const fid = ref<number | null>(null);
  const cls = ref<number | null>(null);

  const fcp = ref<number | null>(null);
  const tbt = ref<number | null>(null);
  const speedIndex = ref<number | null>(null);

  const resetMetrics = () => {
    performanceScore.value = "--";
    accessibilityScore.value = "--";
    bestPracticesScore.value = "--";
    seoScore.value = "--";

    lcp.value = null;
    fid.value = null;
    cls.value = null;

    fcp.value = null;
    tbt.value = null;
    speedIndex.value = null;
  };

  const analyzeWebsite = async (websiteUrl: string) => {
    if (!websiteUrl.trim()) {
      error.value = "Введите URL сайта";
      return;
    }

    isLoading.value = true;
    error.value = null;

    resetMetrics();

    try {
      const response: any = await auditApi.analyzeWebsite(websiteUrl);

      if (response?.success && response?.data) {
        performanceScore.value = response.data.performance ?? 0;
        accessibilityScore.value = response.data.accessibility ?? 0;
        bestPracticesScore.value = response.data.bestPractices ?? 0;
        seoScore.value = response.data.seo ?? 0;

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
      isLoading.value = false;
    }
  };

  return {
    analyzeWebsite,
    performanceScore,
    accessibilityScore,
    bestPracticesScore,
    seoScore,
    lcp,
    fid,
    cls,
    fcp,
    tbt,
    speedIndex,
    isLoading,
    error,
  };
});
