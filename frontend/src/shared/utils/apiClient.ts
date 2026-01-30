import axios, {
  AxiosError,
  AxiosRequestConfig,
  AxiosResponse,
  InternalAxiosRequestConfig,
} from "axios";
import { tokenStorage } from "./tokenStorage";

const BASE_URL = import.meta.env.VITE_API_URL || "https://auditnet-backend.onrender.com/api";

const axiosInstance = axios.create({
  baseURL: BASE_URL,
  timeout: 200_000, // 200 секунд для долгих операций Lighthouse
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

axiosInstance.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = tokenStorage.get();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    return config;
  },
  (error: AxiosError) => Promise.reject(error)
);

axiosInstance.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => Promise.reject(error)
);

export type ApiClientOptions = AxiosRequestConfig;

export const apiClient = <T = unknown>(url: string, options: ApiClientOptions = {}): Promise<T> =>
  axiosInstance({
    url,
    ...options,
  }).then((response) => response.data as T);

export const getCsrfCookie = async (): Promise<void> => {
  // Больше не нужен для token-based auth
  return Promise.resolve();
};

export { axiosInstance };
