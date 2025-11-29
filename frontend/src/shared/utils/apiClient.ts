import axios, {
  AxiosError,
  AxiosRequestConfig,
  AxiosResponse,
  InternalAxiosRequestConfig,
} from "axios";

const BASE_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

const axiosInstance = axios.create({
  baseURL: BASE_URL,
  timeout: 30_000,
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
  withCredentials: true,
});

function getCookie(name: string): string | null {
  try {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    
    if (parts.length === 2) {
      const cookieValue = parts.pop()?.split(";").shift();
      return cookieValue ? decodeURIComponent(cookieValue) : null;
    }
    
    return null;
  } catch (error) {
    console.error(`Error reading cookie "${name}":`, error);
    return null;
  }
}

axiosInstance.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
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
  async (error: AxiosError) => Promise.reject(error)
);

export type ApiClientOptions = AxiosRequestConfig;

export const apiClient = <T = unknown>(url: string, options: ApiClientOptions = {}): Promise<T> =>
  axiosInstance({
    url,
    ...options,
  }).then((response) => response.data as T);

export const getCsrfCookie = async (): Promise<void> => {
  const baseUrl = BASE_URL.replace(/\/api$/, "");
  const csrfUrl = `${baseUrl}/sanctum/csrf-cookie`;

  await axios.get(csrfUrl, {
    withCredentials: true,
  });
};

export { axiosInstance };
