<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <Form
        :validation-schema="resetPasswordSchema"
        :initial-values="initialValues"
        class="auth-form"
        @submit="onSubmit"
      >
        <h2>Новый пароль</h2>
        <p class="form-hint">Придумайте новый пароль для вашего аккаунта.</p>

        <div class="form-fields">
          <InputField name="email" type="email" label="Email" autocomplete="email" />
          <InputField
            name="password"
            type="password"
            label="Новый пароль"
            autocomplete="new-password"
          />
          <InputField
            name="password_confirmation"
            type="password"
            label="Подтверждение пароля"
            autocomplete="new-password"
          />
        </div>

        <Button type="submit" variant="primary" size="lg" :loading="isLoading" full-width>
          Сохранить пароль
        </Button>

        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </Form>

      <p class="switch-link">
        <RouterLink :to="{ name: 'profile' }">Вернуться ко входу</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Form } from "vee-validate";
import InputField from "@/shared/ui/molecules/InputField.vue";
import { Button } from "@/shared/ui/atoms";
import { resetPasswordSchema } from "@/shared/validation/schemas";
import { useAuthApi } from "../composables/useAuthApi";
import type { ResetPasswordCredentials } from "../types";

const route = useRoute();
const router = useRouter();
const authApi = useAuthApi();

const isLoading = ref(false);
const successMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

const token = computed(() => String(route.query.token ?? ""));
const email = computed(() => String(route.query.email ?? ""));

const initialValues = computed(() => ({
  email: email.value,
}));

const onSubmit = async (values: unknown) => {
  if (!token.value) {
    errorMessage.value = "Ссылка для сброса пароля недействительна. Запросите новую.";
    return;
  }

  isLoading.value = true;
  successMessage.value = null;
  errorMessage.value = null;

  try {
    const data = values as Omit<ResetPasswordCredentials, "token">;
    const message = await authApi.resetPassword({
      ...data,
      token: token.value,
    });
    successMessage.value = message;
    setTimeout(() => router.push({ name: "profile" }), 2000);
  } catch (e: unknown) {
    errorMessage.value = e instanceof Error ? e.message : "Не удалось сбросить пароль";
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.auth-wrapper {
  display: flex;
  justify-content: center;
  padding: 2rem 1rem 4rem;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 2rem 1.5rem;
}

.auth-form h2 {
  margin: 0 0 0.5rem;
  color: #fff;
  text-align: center;
}

.form-hint {
  text-align: center;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  margin: 0 0 1.25rem;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.switch-link {
  margin-top: 1.25rem;
  text-align: center;
  font-size: 0.875rem;
}

.switch-link a {
  color: #7c3aed;
  text-decoration: underline;
}

.success-message {
  color: #10b981;
  font-size: 0.875rem;
  text-align: center;
  margin: 1rem 0 0;
}

.error-message {
  color: #f87171;
  font-size: 0.875rem;
  text-align: center;
  margin: 1rem 0 0;
}
</style>
