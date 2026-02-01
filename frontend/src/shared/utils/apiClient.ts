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
  timeout: 200_000,
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
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      tokenStorage.remove();
      
      const publicRoutes = ['/login', '/register', '/'];
      const currentPath = window.location.pathname;
      
      if (!publicRoutes.includes(currentPath)) {
        localStorage.setItem('auth_expired_message', 'Ваша сессия истекла. Пожалуйста, войдите снова.');
        window.location.href = '/login';
      }
    }
    
    return Promise.reject(error);
  }
);

export type ApiClientOptions = AxiosRequestConfig;

export const apiClient = <T = unknown>(url: string, options: ApiClientOptions = {}): Promise<T> =>
  axiosInstance({
    url,
    ...options,
  }).then((response) => response.data as T);

export const getCsrfCookie = async (): Promise<void> => {
  return Promise.resolve();
};

export { axiosInstance };
