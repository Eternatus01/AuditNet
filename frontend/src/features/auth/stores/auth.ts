import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useAuthApi } from "../composables/useAuthApi";

export const useAuthStore = defineStore("auth", () => {
  const authApi = useAuthApi();
  const user = ref<{ id: number; name: string; email: string } | null>(null);
  const error = ref<string | null>(null);
  const isProfileLoading = ref(false); // Флаг загрузки профиля при инициализации
  const isAuthenticated = computed(() => !!user.value);

  const signUp = async (name: string, email: string, password: string) => {
    try {
      error.value = null;
      const response = await authApi.signUp(name, email, password);

      user.value = response.user ?? null;

      return response;
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка регистрации";
    }
  };

  const signIn = async (email: string, password: string) => {
    try {
      error.value = null;
      const response = await authApi.signIn(email, password);

      user.value = response.user ?? null;

      return response;
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка авторизации";
    }
  };

  const logout = async () => {
    try {
      error.value = null;
      await authApi.logout();
      user.value = null;
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка выхода";
    }
  };

  const fetchProfile = async () => {
    isProfileLoading.value = true;
    try {
      const response = await authApi.fetchProfile();
      user.value = response ?? null;
      return response;
    } catch {
      user.value = null;
      return null;
    } finally {
      isProfileLoading.value = false;
    }
  };

  return {
    signUp,
    signIn,
    logout,
    fetchProfile,
    error,
    user,
    isAuthenticated,
    isProfileLoading,
  };
});
