import { apiClient } from "@/widgets/apiClient";

interface SignUpResponse {
  token: string;
  user: { id: number; name: string; email: string };
}

interface SignInResponse {
  token: string;
  user: { id: number; name: string; email: string };
}

interface LogoutResponse {
  message: string;
}

interface ProfileResponse {
  id: number;
  name: string;
  email: string;
}

export const useAuthApi = () => {
  const signUp = async (
    name: string,
    email: string,
    password: string
  ): Promise<SignUpResponse> => {
    try {
      const response: SignUpResponse = await apiClient("/auth/register", {
        method: "POST",
        data: { name, email, password },
      });
      if (!response.token) {
        throw new Error("Ответ от сервера не содержит токена!");
      }
      return response;
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data?.errors) {
        throw new Error(Object.values(e.response.data.errors).flat().join(" "));
      }
      throw new Error(
        e.response?.data?.message || e.message || "Ошибка регистрации"
      );
    }
  };

  const signIn = async (email: string, password: string) => {
    try {
      const response: SignInResponse = await apiClient("/auth/login", {
        method: "POST",
        data: {
          email,
          password,
        },
      });

      if (!response.token) {
        throw new Error("Сервер не вернул токен. Проверь backend.");
      }

      return response;
    } catch (e: any) {
      if (e.response?.status === 422 && e.response.data?.errors) {
        throw new Error(Object.values(e.response.data.errors).flat().join(" "));
      }

      if (e.response?.status === 401) {
        throw new Error(
          e.response?.data?.message || "Неверный email или пароль"
        );
      }

      throw new Error(
        e.response?.data?.message || e.message || "Ошибка авторизации"
      );
    }
  };

  const logout = async (): Promise<LogoutResponse> => {
    try {
      const response: LogoutResponse = await apiClient("/auth/logout", {
        method: "POST",
      });
      return response;
    } catch (e: any) {
      throw new Error(e.response?.data?.message || e.message || "Ошибка выхода");
    }
  };

  const fetchProfile = async (): Promise<ProfileResponse> => {
    try {
      const cashToken = localStorage.getItem("token");
      if (!cashToken) throw new Error("Токен не найден");

      const data: ProfileResponse = await apiClient("/user", {
        method: "GET",
      });

      if (!data.id) throw new Error("Профиль не найден");
      return data;
    } catch (e: any) {
      throw new Error(e.response?.data?.message || e.message || "Ошибка получения профиля");
    }
  };

  return {
    signUp,
    signIn,
    logout,
    fetchProfile,
  };
};
