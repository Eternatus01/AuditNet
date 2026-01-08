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
    const MAX_RETRIES = 10;
    const RETRY_DELAY = 5000;
    
    const initialResponse = await apiClient<any>("/audit/security-audit", {
      method: "POST",
      data: {
        url: websiteUrl.trim(),
      },
    });

    if (initialResponse?.data?.status === 'processing' && initialResponse?.data?.cache_key) {
      const cacheKey = initialResponse.data.cache_key;
      
      for (let attempt = 0; attempt < MAX_RETRIES; attempt++) {
        await new Promise(resolve => setTimeout(resolve, RETRY_DELAY));
        
        try {
          const checkResponse = await apiClient<any>("/audit/security-audit", {
            method: "POST",
            data: {
              url: websiteUrl.trim(),
            },
          });

          if (checkResponse?.data && !checkResponse.data.status) {
            return checkResponse.data as SecurityAuditResponse;
          }

          if (checkResponse?.error || checkResponse?.data?.error) {
            throw new Error(checkResponse.error || checkResponse.data.error || "Ошибка аудита безопасности");
          }
        } catch (error: unknown) {
          if (attempt === MAX_RETRIES - 1) {
            throw error;
          }
        }
      }
      
      throw new Error("Превышено время ожидания результатов security audit");
    }

    if (initialResponse?.data && !initialResponse.data.status) {
      return initialResponse.data as SecurityAuditResponse;
    }

    if (initialResponse?.error) {
      throw new Error(initialResponse.error || "Ошибка аудита безопасности");
    }

    throw new Error("Не удалось получить результаты security audit");
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
