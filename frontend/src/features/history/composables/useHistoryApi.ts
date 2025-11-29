import { apiClient } from "@/shared/utils/apiClient";
import { handleApiError } from "@/shared/utils/errorHandling";
import type { Audit, PaginatedResponse, AuditDetailResponse } from "../types";

export const useHistoryApi = () => {
  const fetchHistory = async (page = 1): Promise<PaginatedResponse<Audit>> => {
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

  return {
    fetchHistory,
    fetchAuditDetail,
  };
};

