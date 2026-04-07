import { apiClient } from "@/shared/utils/apiClient";
import { handleApiError } from "@/shared/utils/errorHandling";
import type { AnalyzeWebsiteResponse, GuestAuditResponse, GuestAuditData, AuditResource, SecurityAuditResponse, AuditStatusResponse } from "../types";

interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
}

export const useAuditApi = () => {
  const analyzeWebsite = async (websiteUrl: string): Promise<AnalyzeWebsiteResponse> => {
    try {
      const response = await apiClient<ApiResponse<AuditResource>>("/audit/analyze", {
        method: "POST",
        data: { url: websiteUrl.trim() },
      });

      if (response?.success && response?.data) {
        return { success: true, data: response.data };
      }

      throw new Error("Не удалось получить результаты анализа");
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка при анализе сайта");
    }
  };

  const fetchSecurityAudit = async (websiteUrl: string): Promise<SecurityAuditResponse> => {
    try {
      const response = await apiClient<ApiResponse<SecurityAuditResponse>>("/audit/security-audit", {
        method: "POST",
        data: { url: websiteUrl.trim() },
      });

      if (response?.success && response?.data) {
        return response.data;
      }

      throw new Error("Не удалось получить результаты security audit");
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка аудита безопасности");
    }
  };

  const analyzeGuestWebsite = async (websiteUrl: string): Promise<GuestAuditResponse> => {
    try {
      const response = await apiClient<ApiResponse<GuestAuditData>>("/audit/analyze-guest", {
        method: "POST",
        data: { url: websiteUrl.trim() },
      });

      if (response?.success && response?.data) {
        return { success: true, data: response.data };
      }

      throw new Error("Не удалось получить результаты анализа");
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка при анализе сайта");
    }
  };

  const fetchGuestSecurityAudit = async (websiteUrl: string): Promise<SecurityAuditResponse> => {
    try {
      const response = await apiClient<ApiResponse<SecurityAuditResponse>>("/audit/security-audit-guest", {
        method: "POST",
        data: { url: websiteUrl.trim() },
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
    analyzeGuestWebsite,
    fetchGuestSecurityAudit,
    checkAuditStatus,
  };
};
