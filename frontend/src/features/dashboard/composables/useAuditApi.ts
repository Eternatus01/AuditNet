import { apiClient } from "@/shared/utils/apiClient";
import { handleApiError, isApiError } from "@/shared/utils/errorHandling";
import type { AnalyzeWebsiteResponse, SecurityAuditResponse, AuditStatusResponse } from "../types";

export const useAuditApi = () => {
  const analyzeWebsite = async (websiteUrl: string): Promise<AnalyzeWebsiteResponse> => {
    try {
      const response = await apiClient<{ data: any }>("/audit/analyze", {
        method: "POST",
        data: {
          url: websiteUrl.trim(),
        },
      });

      if (response?.success && response?.data) {
        return {
          success: true,
          data: response.data
        };
      }

      throw new Error("Не удалось получить результаты анализа");
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка при анализе сайта");
    }
  };

  const fetchSecurityAudit = async (websiteUrl: string): Promise<SecurityAuditResponse> => {
    try {
      const response = await apiClient<{ data: SecurityAuditResponse }>("/audit/security-audit", {
        method: "POST",
        data: {
          url: websiteUrl.trim(),
        },
      });

      if (response?.success && response?.data) {
        return response.data;
      }

      throw new Error("Не удалось получить результаты security audit");
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка аудита безопасности");
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
