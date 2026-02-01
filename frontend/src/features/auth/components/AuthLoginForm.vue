<template>
  <section class="auth-container">
    <Form :validation-schema="loginSchema" class="auth-form" @submit="onSubmit">
      <h2>Вход в систему</h2>

      <div v-if="sessionExpiredMessage" class="warning-message">
        {{ sessionExpiredMessage }}
      </div>

      <div class="form-fields">
        <InputField
          name="email"
          type="email"
          placeholder="E-mail"
          autocomplete="email"
          label="Email"
        />

        <InputField
          name="password"
          type="password"
          placeholder="Пароль"
          autocomplete="current-password"
          label="Пароль"
        />
      </div>

      <Button type="submit" variant="primary" size="lg" :loading="isLoading" full-width>
        Войти
      </Button>

      <p v-if="error" class="error-message">{{ error }}</p>
    </Form>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { Form } from "vee-validate";
import { useAuthStore } from "../stores/auth";
import type { SignInCredentials } from "../types";
import InputField from "@/shared/ui/molecules/InputField.vue";
import { Button } from "@/shared/ui/atoms";
import { useRouter } from "vue-router";
import { loginSchema } from "@/shared/validation/schemas";

const router = useRouter();
const authStore = useAuthStore();
const { error } = storeToRefs(authStore);
const isLoading = ref<boolean>(false);
const sessionExpiredMessage = ref<string | null>(null);

onMounted(() => {
  const message = localStorage.getItem('auth_expired_message');
  if (message) {
    sessionExpiredMessage.value = message;
    localStorage.removeItem('auth_expired_message');
    
    setTimeout(() => {
      sessionExpiredMessage.value = null;
    }, 5000);
  }
});

const onSubmit = async (values: unknown): Promise<void> => {
  isLoading.value = true;

  try {
    const credentials = values as SignInCredentials;
    const response = await authStore.signIn(credentials);

    if (response) {
      await router.push({ name: "home" });
    }
  } catch {
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.warning-message {
  padding: 1rem;
  background-color: rgba(255, 193, 7, 0.1);
  border: 1px solid rgba(255, 193, 7, 0.3);
  border-radius: 8px;
  color: #ffc107;
  font-size: 0.9rem;
  text-align: center;
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
