import { apiClient } from '@/shared/utils/apiClient';
import type {
  CreateSitePayload,
  SiteActionResponse,
  SiteResponse,
  SitesListResponse,
  UpdateSitePayload,
} from '../types';

export const useSitesApi = () => {
  const getSites = () =>
    apiClient<SitesListResponse>('/sites', { method: 'GET' });

  const createSite = (payload: CreateSitePayload) =>
    apiClient<SiteResponse>('/sites', { method: 'POST', data: payload });

  const updateSite = (id: number, payload: UpdateSitePayload) =>
    apiClient<SiteResponse>(`/sites/${id}`, { method: 'PUT', data: payload });

  const deleteSite = (id: number) =>
    apiClient<SiteActionResponse>(`/sites/${id}`, { method: 'DELETE' });

  const runSite = (id: number) =>
    apiClient<SiteActionResponse>(`/sites/${id}/run`, { method: 'POST' });

  return {
    getSites,
    createSite,
    updateSite,
    deleteSite,
    runSite,
  };
};
