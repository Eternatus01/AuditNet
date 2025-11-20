import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

export const useAuthStore = defineStore("auth", () => {
  const router = useRouter();
  
  const token = ref<string>(localStorage.getItem('token') || '')
  const error = ref<string | null>(null)

  const signUp = async (name: string, email: string, password: string) => {
    error.value = null;
    try {
      const response = await axios.post(
        "http://localhost:8000/api/auth/register",
        {
          name,
          email,
          password,
        },
        {
          headers: {
            Accept: "application/json",
          },
        }
      );

      if (!response.data.token) {
        error.value = "Ответ от сервера не содержит токена! Проверь backend.";
        return;
      }

      localStorage.setItem("token", response.data.token);
      token.value = response.data.token
      axios.defaults.headers.common.Authorization = `Bearer ${token.value}`

      router.push({ name: "home" });
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data.errors) {
        error.value = Object.values(e.response.data.errors).join(" ");
        return;
      }

      error.value =
        e.response?.data?.message || e.message || "Ошибка регистрации";
    }
  };

  const signIn = async (email: string, password: string) => {
    error.value = null;
  
    try {
      const response = await axios.post(
        "http://localhost:8000/api/auth/login",
        {
          email,
          password
        },
        {
          headers: {
            Accept: "application/json",
          },
        }
      );
  
      if (!response.data.token) {
        error.value = "Сервер не вернул токен. Проверь backend.";
        return;
      }
  
      localStorage.setItem("token", response.data.token);
      token.value = response.data.token
      axios.defaults.headers.common.Authorization = `Bearer ${token.value}`
  
      router.push({ name: "home" });
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data.errors) {
        error.value = Object.values(e.response.data.errors).join(" ");
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

  return {
    signUp,
    signIn,
    error,
  }
});
