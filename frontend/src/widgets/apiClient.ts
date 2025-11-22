import axios, {
  AxiosError,
  AxiosRequestConfig,
  AxiosResponse,
  InternalAxiosRequestConfig,
} from "axios";

const BASE_URL =
  import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

const axiosInstance = axios.create({
  baseURL: BASE_URL,
  timeout: 150_000,
  headers: {
    Accept: "application/json",
  },
});

axiosInstance.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem("token");

    if (token) {
      config.headers.Authorization = config.headers.Authorization
        ?? `Bearer ${token}`;
    }

    return config;
  },
  (error: AxiosError) => Promise.reject(error),
);

axiosInstance.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
    }

    return Promise.reject(error);
  },
);

export type ApiClientOptions = AxiosRequestConfig;

export const apiClient = <T = unknown>(
  url: string,
  options: ApiClientOptions = {},
): Promise<T> =>
  axiosInstance({
    url,
    ...options,
  }).then((response) => response.data as T);

export { axiosInstance };
