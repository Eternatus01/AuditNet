<template>
  <section class="auth-container">
    <form class="auth-form" @submit.prevent="login">
      <h2>Вход в систему</h2>

      <input
        class="auth-input"
        type="email"
        v-model="email"
        placeholder="E-mail"
        autocomplete="email"
      />

      <input
        class="auth-input"
        type="password"
        v-model="password"
        placeholder="Пароль"
        autocomplete="current-password"
      />

      <button type="submit">Войти</button>

      <p v-if="error" class="error-message">{{ error }}</p>
    </form>
  </section>
</template>

<script lang="ts" setup>
import { computed, Ref, ref } from "vue";
import { useAuthStore } from "../stores/auth";

const authStore = useAuthStore();
const email: Ref<string> = ref("");
const password: Ref<string> = ref("");
const error: Ref<string | null> = computed(() => authStore.error);

const login = async () => {
  try {
    await authStore.signIn(email.value, password.value);
    
    email.value = "";
    password.value = "";
  } catch (error: any) {
    console.error(error.message);
  }
};

</script>
