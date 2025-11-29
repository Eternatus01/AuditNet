<template>
  <section class="auth-container">
    <Form :validation-schema="registerSchema" class="auth-form" @submit="onSubmit">
      <h2>Регистрация</h2>

      <div class="form-fields">
        <InputField name="name" type="text" placeholder="Имя" autocomplete="name" label="Имя" />

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
          autocomplete="new-password"
          label="Пароль"
        />
      </div>

      <Button type="submit" variant="primary" size="lg" :loading="isLoading" full-width>
        Создать аккаунт
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
import type { SignUpCredentials } from "../types";
import InputField from "@/shared/ui/molecules/InputField.vue";
import { Button } from "@/shared/ui/atoms";
import { useRouter } from "vue-router";
import { registerSchema } from "@/shared/validation/schemas";

const router = useRouter();
const authStore = useAuthStore();
const { error } = storeToRefs(authStore);
const isLoading = ref<boolean>(false);

const onSubmit = async (values: unknown): Promise<void> => {
  isLoading.value = true;

  try {
    const credentials = values as SignUpCredentials;
    const response = await authStore.signUp(credentials);

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
