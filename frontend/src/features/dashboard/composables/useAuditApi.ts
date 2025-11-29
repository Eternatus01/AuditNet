import { apiClient } from "@/shared/utils/apiClient";
import { handleApiError, isApiError } from "@/shared/utils/errorHandling";
import type { AnalyzeWebsiteResponse, SecurityAuditResponse, AuditStatusResponse } from "../types";

export const useAuditApi = () => {
  const analyzeWebsite = async (websiteUrl: string): Promise<AnalyzeWebsiteResponse> => {
    try {
      const response = await apiClient<AnalyzeWebsiteResponse>("/audit/analyze", {
        method: "POST",
        data: {
          url: websiteUrl.trim(),
        },
      });

      if (response?.success && response?.data) {
        return response;
      } else {
        throw new Error("Не удалось получить результаты анализа");
      }
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка при анализе сайта");
    }
  };

  const fetchSecurityAudit = async (websiteUrl: string): Promise<SecurityAuditResponse> => {
    try {
      const response = await apiClient<SecurityAuditResponse>("/audit/security-audit", {
        method: "POST",
        data: {
          url: websiteUrl.trim(),
        },
      });

      if (response.error) {
        throw new Error(response.error || "Ошибка аудита безопасности");
      }

      return response;
    } catch (error: unknown) {
      if (isApiError(error)) {
        throw new Error(error.response?.data?.error || error.message || "Ошибка сети");
      }
      throw new Error("Ошибка сети");
    }
  };

  const checkAuditStatus = async (auditId: number): Promise<AuditStatusResponse> => {
    try {
      const response = await apiClient<AuditStatusResponse>(`/audit/status/${auditId}`, {
        method: "GET",
      });
      return response;
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка при проверке статуса");
    }
  };

  return {
    analyzeWebsite,
    fetchSecurityAudit,
    checkAuditStatus,
  };
};
