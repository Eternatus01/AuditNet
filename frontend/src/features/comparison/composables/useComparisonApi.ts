import { apiClient } from "@/shared/utils/apiClient";
import { handleApiError } from "@/shared/utils/errorHandling";
import type { AuditComparison, ComparisonDetailResponse, ShareLinkResponse } from "@/features/history/types";

interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
}

export const useComparisonApi = () => {
  const analyzeComparison = async (urls: string[]): Promise<ComparisonDetailResponse> => {
    try {
      const response = await apiClient<ApiResponse<AuditComparison>>("/comparisons/analyze", {
        method: "POST",
        data: { urls },
      });

      return { success: response.success, data: response.data };
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка при сравнении сайтов");
    }
  };

  const fetchComparisonDetail = async (id: string): Promise<ComparisonDetailResponse> => {
    try {
      return await apiClient<ComparisonDetailResponse>(`/comparisons/history/${id}`, {
        method: "GET",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при загрузке сравнения");
      throw error;
    }
  };

  const createComparisonShareLink = async (id: number): Promise<ShareLinkResponse> => {
    try {
      return await apiClient<ShareLinkResponse>(`/comparisons/history/${id}/share`, {
        method: "POST",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при создании публичной ссылки");
      throw error;
    }
  };

  const fetchPublicComparison = async (token: string): Promise<ComparisonDetailResponse> => {
    try {
      return await apiClient<ComparisonDetailResponse>(`/comparisons/public/${token}`, {
        method: "GET",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при загрузке публичного сравнения");
      throw error;
    }
  };

  return {
    analyzeComparison,
    fetchComparisonDetail,
    createComparisonShareLink,
    fetchPublicComparison,
  };
};
