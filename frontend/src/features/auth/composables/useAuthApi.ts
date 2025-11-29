import { apiClient, getCsrfCookie } from "@/shared/utils/apiClient";
import { handleApiError, isApiError } from "@/shared/utils/errorHandling";
import type {
  AuthResponse,
  LogoutResponse,
  ProfileResponse,
  SignUpCredentials,
  SignInCredentials,
} from "../types";

export const useAuthApi = () => {
  const signUp = async (credentials: SignUpCredentials): Promise<AuthResponse> => {
    try {
      await getCsrfCookie();

      const response = await apiClient<AuthResponse>("/auth/register", {
        method: "POST",
        data: credentials,
      });

      return response;
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка регистрации");
    }
  };

  const signIn = async (credentials: SignInCredentials): Promise<AuthResponse> => {
    try {
      await getCsrfCookie();

      const response = await apiClient<AuthResponse>("/auth/login", {
        method: "POST",
        data: credentials,
      });

      return response;
    } catch (error: unknown) {
      if (isApiError(error) && error.response?.status === 401) {
        throw new Error(error.response?.data?.message || "Неверный email или пароль");
      }

      return handleApiError(error, "Ошибка авторизации");
    }
  };

  const logout = async (): Promise<LogoutResponse> => {
    try {
      const response = await apiClient<LogoutResponse>("/auth/logout", {
        method: "POST",
      });
      return response;
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка выхода");
    }
  };

  const fetchProfile = async (): Promise<ProfileResponse> => {
    try {
      const data = await apiClient<ProfileResponse>("/user", {
        method: "GET",
      });

      if (!data.id) {
        throw new Error("Профиль не найден");
      }

      return data;
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка получения профиля");
    }
  };

  return {
    signUp,
    signIn,
    logout,
    fetchProfile,
  };
};
