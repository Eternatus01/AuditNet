import { apiClient } from "@/widgets/apiClient";

interface AnalyzeWebsiteResponse {
  success: boolean;
  data: {
    performance: number;
    accessibility: number;
    bestPractices: number;
    seo: number;
    lcp: number;
    fid: number;
    cls: number;
    fcp: number;
    tbt: number;
    speedIndex: number;
  };
}
export const useAuditApi = () => {
  const analyzeWebsite = async (
    websiteUrl: string
  ): Promise<AnalyzeWebsiteResponse> => {
    try {
      const response: AnalyzeWebsiteResponse = await apiClient(
        "/audit/analyze",
        {
          method: "POST",
          data: {
            url: websiteUrl.trim(),
          },
        }
      );

      if (response?.success && response?.data) {
        return response;
      } else {
        throw new Error("Не удалось получить результаты анализа");
      }
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data?.errors) {
        throw new Error(Object.values(e.response.data.errors).flat().join(" "));
      }
      throw new Error(e.response?.data?.message || e.message || "Ошибка при анализе сайта");
    }
  };

  return {
    analyzeWebsite,
  };
};
