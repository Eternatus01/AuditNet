<template>
  <section class="auth-container">
    <Form :validation-schema="loginSchema" class="auth-form" @submit="onSubmit">
      <h2>Вход в систему</h2>

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
import { ref } from "vue";
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

const onSubmit = async (values: unknown): Promise<void> => {
  isLoading.value = true;

  try {
    const credentials = values as SignInCredentials;
    const response = await authStore.signIn(credentials);

    if (response) {
      await router.push({ name: "home" });
    }
  } catch {
    // Ошибка уже обработана в store
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
</style>
