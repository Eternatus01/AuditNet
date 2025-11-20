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
import { computed, Ref, ref } from "vue";
import { useAuthStore } from "../stores/auth";

const authStore = useAuthStore();
const name: Ref<string> = ref("");
const email: Ref<string> = ref("");
const password: Ref<string> = ref("");
const error: Ref<string | null> = computed(() => authStore.error);

const register = async () => {
  try {
    await authStore.signUp(name.value, email.value, password.value);
    
    name.value = "";
    email.value = "";
    password.value = "";
  } catch (error: any) {
    console.error(error.message);
  }
};
</script>
