import { apiClient } from "@/shared/utils/apiClient";

export const useHistoryApi = () => {
  const fetchHistory = async (page = 1) => {
    return await apiClient(`/audit/history?page=${page}`, {
      method: "GET",
    });
  };

  const fetchAuditDetail = async (id: string) => {
    return await apiClient(`/audit/history/${id}`, {
      method: "GET",
    });
  };

  return {
    fetchHistory,
    fetchAuditDetail,
  };
};

