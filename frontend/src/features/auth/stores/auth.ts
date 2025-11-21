import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { apiClient } from "@/widgets/apiClient";

export const useAuthStore = defineStore("auth", () => {
  const router = useRouter();
  const user = ref<{ id: number; name: string; email: string } | null>(null);
  const token = ref<string>(localStorage.getItem("token") || "");
  const error = ref<string | null>(null);
  const isAuthenticated = computed(() => !!token.value);

  const signUp = async (name: string, email: string, password: string) => {
    error.value = null;
    try {
      const response: {
        token: string;
        user: { id: number; name: string; email: string };
      } = await apiClient("/auth/register", {
        method: "POST",
        data: {
          name,
          email,
          password,
        },
      });

      if (!response.token) {
        error.value = "Ответ от сервера не содержит токена! Проверь backend.";
        return;
      }

      localStorage.setItem("token", response.token);
      token.value = response.token;
      user.value = response.user ?? null;
      router.push({ name: "home" });
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data?.errors) {
        error.value = Object.values(e.response.data.errors).flat().join(" ");
        return;
      }

      error.value =
        e.response?.data?.message || e.message || "Ошибка регистрации";
    }
  };

  const signIn = async (email: string, password: string) => {
    error.value = null;

    try {
      const response: {
        token: string;
        user: { id: number; name: string; email: string };
      } = await apiClient("/auth/login", {
        method: "POST",
        data: {
          email,
          password,
        },
      });

      if (!response.token) {
        error.value = "Сервер не вернул токен. Проверь backend.";
        return;
      }

      localStorage.setItem("token", response.token);
      token.value = response.token;

      user.value = response.user ?? null;
      router.push({ name: "home" });
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data?.errors) {
        error.value = Object.values(e.response.data.errors).flat().join(" ");
        return;
      }

      if (e.response?.status === 401) {
        error.value = e.response?.data?.message || "Неверный email или пароль";
        return;
      }

      error.value =
        e.response?.data?.message || e.message || "Ошибка авторизации";
    }
  };

  const logout = async () => {
    try {
      await apiClient("/auth/logout", {
        method: "POST",
      });
    } finally {
      localStorage.removeItem("token");
      token.value = "";
      user.value = null;
      router.push({ name: "login" });
    }
  };

  const fetchProfile = async () => {
    try {
      const cashToken = localStorage.getItem("token");
      if (!cashToken) return;

      token.value = cashToken;
      const data: {
        id: number;
        name: string;
        email: string;
      } = await apiClient("/user", {
        method: "GET",
      });
      console.log("data ", data);
      user.value = data;
    } catch {
      token.value = "";
      user.value = null;
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
