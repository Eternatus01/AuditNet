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

      if (response.token && response.expires_at) {
        tokenStorage.set(response.token, response.expires_at);
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

      if (response.token && response.expires_at) {
        tokenStorage.set(response.token, response.expires_at);
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
      // Сразу очищаем локальное состояние, не дожидаясь ответа от Backend
      tokenStorage.remove();
      user.value = null;
      error.value = null;
      
      // Отправляем запрос на Backend в фоне (без await)
      authApi.logout().catch(() => {
        // Игнорируем ошибки, т.к. пользователь уже вышел локально
      });
    } catch (e: unknown) {
      // На всякий случай очищаем состояние даже при ошибке
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
