import { apiClient } from "@/shared/utils/apiClient";
import type { SiteAnalytics } from "../types";

interface HistoryAudit {
  id: number;
  url: string;
  status: string;
  performance: number | null;
  accessibility: number | null;
  best_practices: number | null;
  seo: number | null;
  lcp: number | null;
  fid: number | null;
  cls: number | null;
  audited_at: string | null;
  created_at: string;
}

interface PaginatedHistoryResponse {
  success: boolean;
  data: HistoryAudit[];
  current_page: number;
  last_page: number;
  total: number;
}

export const useAnalyticsApi = () => {
  const getAllHistory = async () => {
    const allAudits: HistoryAudit[] = [];
    let currentPage = 1;
    let hasMorePages = true;

    while (hasMorePages) {
      const response = await apiClient<PaginatedHistoryResponse>(
        `/audit/history?page=${currentPage}&per_page=50`,
        { method: 'GET' }
      );

      if (response.success && response.data.length > 0) {
        allAudits.push(...response.data);
        hasMorePages = currentPage < response.last_page;
        currentPage++;
      } else {
        hasMorePages = false;
      }
    }

    return {
      success: true,
      data: allAudits,
    };
  };

  const getAnalytics = async (url: string, limit: number = 20) => {
    const historyResponse = await getAllHistory();
    
    if (!historyResponse.success) {
      throw new Error('Не удалось загрузить историю');
    }

    const filteredAudits = historyResponse.data
      .filter(audit => audit.url === url && audit.status === 'completed')
      .sort((a, b) => {
        const dateA = new Date(a.audited_at || a.created_at).getTime();
        const dateB = new Date(b.audited_at || b.created_at).getTime();
        return dateB - dateA;
      })
      .slice(0, limit);

    if (filteredAudits.length === 0) {
      throw new Error('Нет данных для выбранного сайта');
    }

    const data = filteredAudits.map(audit => ({
      date: audit.audited_at || audit.created_at,
      performance: audit.performance,
      accessibility: audit.accessibility,
      best_practices: audit.best_practices,
      seo: audit.seo,
      lcp: audit.lcp,
      fid: audit.fid,
      cls: audit.cls ? audit.cls * 1000 : null,
    }));

    const firstAudit = filteredAudits[filteredAudits.length - 1];
    const lastAudit = filteredAudits[0];

    return {
      success: true,
      data: {
        url,
        data,
        totalAudits: filteredAudits.length,
        firstAudit: firstAudit.audited_at || firstAudit.created_at,
        lastAudit: lastAudit.audited_at || lastAudit.created_at,
      },
    };
  };

  const getUniqueUrls = async () => {
    const historyResponse = await getAllHistory();
    
    if (!historyResponse.success) {
      return { success: false, data: [] };
    }

    const completedAudits = historyResponse.data.filter(
      audit => audit.status === 'completed'
    );

    const urlsMap = new Map<string, { count: number; last_audit: string }>();

    completedAudits.forEach(audit => {
      const existing = urlsMap.get(audit.url);
      const auditDate = audit.audited_at || audit.created_at;

      if (!existing) {
        urlsMap.set(audit.url, {
          count: 1,
          last_audit: auditDate,
        });
      } else {
        existing.count++;
        if (new Date(auditDate) > new Date(existing.last_audit)) {
          existing.last_audit = auditDate;
        }
      }
    });

    const data = Array.from(urlsMap.entries())
      .map(([url, metadata]) => ({
        url,
        count: metadata.count,
        last_audit: metadata.last_audit,
      }))
      .sort((a, b) => {
        return new Date(b.last_audit).getTime() - new Date(a.last_audit).getTime();
      });

    return {
      success: true,
      data,
    };
  };

  return {
    getAnalytics,
    getUniqueUrls,
  };
};
