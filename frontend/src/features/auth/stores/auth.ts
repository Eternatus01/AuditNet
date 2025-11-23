import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthApi } from "../composable/useAuthApi";

export const useAuthStore = defineStore("auth", () => {
  const router = useRouter();
  const authApi = useAuthApi();
  const user = ref<{ id: number; name: string; email: string } | null>(null);
  const token = ref<string>(localStorage.getItem("token") || "");
  const error = ref<string | null>(null);
  const isAuthenticated = computed(() => !!token.value);

  const signUp = async (name: string, email: string, password: string) => {
    try {
      const response = await authApi.signUp(name, email, password);

      localStorage.setItem("token", response.token);
      token.value = response.token;
      user.value = response.user ?? null;
      router.push({ name: "home" });
      
      return response;
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка регистрации";
    }
  };

  const signIn = async (email: string, password: string) => {
    try {
      const response = await authApi.signIn(email, password);

      localStorage.setItem("token", response.token);
      token.value = response.token;
      user.value = response.user ?? null;
      router.push({ name: "home" });

      return response;
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка авторизации";
    }
  };

  const logout = async () => {
    try {
      await authApi.logout();
      localStorage.removeItem("token");
      token.value = "";
      user.value = null;

      router.push({ name: "login" });
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка выхода";
    }
  };

  const fetchProfile = async () => {
    try {
      const response = await authApi.fetchProfile();
      user.value = response ?? null;

      return response;
    } catch (e: any) {
      error.value = e.response?.data?.message || e.message || "Ошибка получения профиля";
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
  };
});
