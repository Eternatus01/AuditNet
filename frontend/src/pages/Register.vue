<template>
  <section class="auth-container">
    <form class="auth-form" @submit.prevent="register">
      <h2>Регистрация</h2>

      <input
        class="auth-input"
        v-model="name"
        placeholder="Имя"
        autocomplete="name"
      />

      <input
        class="auth-input"
        v-model="email"
        type="email"
        placeholder="E-mail"
        autocomplete="email"
      />

      <input
        class="auth-input"
        v-model="password"
        type="password"
        placeholder="Пароль"
        autocomplete="new-password"
      />

      <button type="submit">Создать аккаунт</button>

      <p v-if="error" class="error-message">{{ error }}</p>
    </form>
  </section>
</template>

<script lang="ts" setup>
import axios from "axios";
import { Ref, ref } from "vue";

const name: Ref<string> = ref("");
const email: Ref<string> = ref("");
const password: Ref<string> = ref("");
const error: Ref<string | null> = ref(null);

const register = async () => {
  error.value = null;
  try {
    const response = await axios.post(
      "http://localhost:8000/api/auth/register",
      {
        name: name.value,
        email: email.value,
        password: password.value,
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
    name.value = "";
    email.value = "";
    password.value = "";
  } catch (e: any) {
    if (e.response?.status === 422 && e.response.data.errors) {
      error.value = Object.values(e.response.data.errors).flat().join(" ");
      return;
    }

    error.value =
      e.response?.data?.message || e.message || "Ошибка регистрации";
  }
};
</script>
