import axios, {
  AxiosError,
  AxiosRequestConfig,
  AxiosResponse,
  InternalAxiosRequestConfig,
} from "axios";

const BASE_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

const axiosInstance = axios.create({
  baseURL: BASE_URL,
  timeout: 30_000, // 30 секунд (было 150 секунд)
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
  withCredentials: true,
});

// Helper функция для чтения cookie по имени
function getCookie(name: string): string | null {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    return parts.pop()?.split(';').shift() || null;
  }
  return null;
}

// Interceptor для добавления CSRF токена в каждый запрос
axiosInstance.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Читаем XSRF-TOKEN из cookie и добавляем в header
    const token = getCookie('XSRF-TOKEN');
    if (token) {
      config.headers['X-XSRF-TOKEN'] = token;
    }
    return config;
  },
  (error: AxiosError) => Promise.reject(error)
);

axiosInstance.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
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
  // BASE_URL это http://localhost:8000/api
  // Нужно получить http://localhost:8000/sanctum/csrf-cookie
  const baseUrl = BASE_URL.replace(/\/api$/, "");
  const csrfUrl = `${baseUrl}/sanctum/csrf-cookie`;

  await axios.get(csrfUrl, {
    withCredentials: true,
  });
};

export { axiosInstance };
