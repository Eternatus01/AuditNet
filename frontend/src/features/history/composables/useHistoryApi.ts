import { apiClient } from "@/shared/utils/apiClient";
import { handleApiError } from "@/shared/utils/errorHandling";
import type { AuditDetailResponse, HistoryItem, PaginatedResponse, ShareLinkResponse } from "../types";

export const useHistoryApi = () => {
  const fetchHistory = async (page = 1): Promise<PaginatedResponse<HistoryItem>> => {
    try {
      return await apiClient<PaginatedResponse<Audit>>(`/audit/history?page=${page}`, {
        method: "GET",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при загрузке истории");
      throw error;
    }
  };

  const fetchAuditDetail = async (id: string): Promise<AuditDetailResponse> => {
    try {
      return await apiClient<AuditDetailResponse>(`/audit/history/${id}`, {
        method: "GET",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при загрузке аудита");
      throw error;
    }
  };

  const createAuditShareLink = async (id: number): Promise<ShareLinkResponse> => {
    try {
      return await apiClient<ShareLinkResponse>(`/audit/history/${id}/share`, {
        method: "POST",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при создании публичной ссылки");
      throw error;
    }
  };

  const fetchPublicAudit = async (token: string): Promise<AuditDetailResponse> => {
    try {
      return await apiClient<AuditDetailResponse>(`/audit/public/${token}`, {
        method: "GET",
      });
    } catch (error: unknown) {
      handleApiError(error, "Ошибка при загрузке публичного отчёта");
      throw error;
    }
  };

  return {
    fetchHistory,
    fetchAuditDetail,
    createAuditShareLink,
    fetchPublicAudit,
  };
};

