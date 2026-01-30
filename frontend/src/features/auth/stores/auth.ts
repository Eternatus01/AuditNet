import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useAuthApi } from "../composables/useAuthApi";
import { tokenStorage } from "@/shared/utils/tokenStorage";
import type { User, SignUpCredentials, SignInCredentials, AuthResponse } from "../types";

export const useAuthStore = defineStore("auth", () => {
  const authApi = useAuthApi();

  const user = ref<User | null>(null);
  const error = ref<string | null>(null);
  const isProfileLoading = ref<boolean>(false);
  const isAuthenticated = computed<boolean>(() => !!user.value);

  const signUp = async (credentials: SignUpCredentials): Promise<AuthResponse | undefined> => {
    try {
      error.value = null;
      const response = await authApi.signUp(credentials);

      if (response.token) {
        tokenStorage.set(response.token);
      }
      user.value = response.user ?? null;

      return response;
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message;
      } else {
        error.value = "Ошибка регистрации";
      }
      return undefined;
    }
  };

  const signIn = async (credentials: SignInCredentials): Promise<AuthResponse | undefined> => {
    try {
      error.value = null;
      const response = await authApi.signIn(credentials);

      if (response.token) {
        tokenStorage.set(response.token);
      }
      user.value = response.user ?? null;

      return response;
    } catch (e: unknown) {
      if (e instanceof Error) {
        error.value = e.message;
      } else {
        error.value = "Ошибка авторизации";
      }
      return undefined;
    }
  };

  const logout = async (): Promise<void> => {
    try {
      error.value = null;
      await authApi.logout();
      tokenStorage.remove();
      user.value = null;
    } catch (e: unknown) {
      tokenStorage.remove();
      user.value = null;
      if (e instanceof Error) {
        error.value = e.message;
      } else {
        error.value = "Ошибка выхода";
      }
    }
  };

  const fetchProfile = async (): Promise<User | null> => {
    isProfileLoading.value = true;
    try {
      const response = await authApi.fetchProfile();
      user.value = response ?? null;
      return response;
    } catch (error) {
      // Ошибка уже обработана в useAuthApi, просто сбрасываем пользователя
      user.value = null;
      return null;
    } finally {
      isProfileLoading.value = false;
    }
  };

  const clearError = (): void => {
    error.value = null;
  };

  return {
    user,
    error,
    isProfileLoading,
    isAuthenticated,
    signUp,
    signIn,
    logout,
    fetchProfile,
    clearError,
  };
});
