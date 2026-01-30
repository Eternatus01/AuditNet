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

      const response = await apiClient<{ data: AuthResponse }>("/auth/register", {
        method: "POST",
        data: credentials,
      });

      return response.data;
    } catch (error: unknown) {
      return handleApiError(error, "Ошибка регистрации");
    }
  };

  const signIn = async (credentials: SignInCredentials): Promise<AuthResponse> => {
    try {
      await getCsrfCookie();

      const response = await apiClient<{ data: AuthResponse }>("/auth/login", {
        method: "POST",
        data: credentials,
      });

      return response.data;
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

  const fetchProfile = async (): Promise<ProfileResponse | null> => {
    try {
      await getCsrfCookie();

      const response = await apiClient<{ data: ProfileResponse } | ProfileResponse>("/user", {
        method: "GET",
      });
      let userData: ProfileResponse | null = null;

      if (response && typeof response === "object") {
        if (
          "data" in response &&
          response.data &&
          typeof response.data === "object" &&
          "id" in response.data
        ) {
          userData = response.data as ProfileResponse;
        } else if ("id" in response) {
          userData = response as ProfileResponse;
        }
      }

      if (!userData?.id) {
        return null;
      }

      return userData;
    } catch (error: unknown) {
      if (isApiError(error) && error.response?.status === 401) {
        return null;
      }

      return null;
    }
  };

  return {
    signUp,
    signIn,
    logout,
    fetchProfile,
  };
};
