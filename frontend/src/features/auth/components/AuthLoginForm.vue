<template>
  <section class="auth-container">
    <Form
      :validation-schema="loginSchema"
      class="auth-form"
      @submit="onSubmit"
    >
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

      <Button
        type="submit"
        variant="primary"
        size="lg"
        :loading="isLoading"
        full-width
      >
        Войти
      </Button>

      <p v-if="error" class="error-message">{{ error }}</p>
    </Form>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { Form } from "vee-validate";
import { useAuthStore } from "../stores/auth";
import InputField from "@/shared/ui/molecules/InputField.vue";
import { Button } from "@/shared/ui/atoms";
import { useRouter } from "vue-router";
import { loginSchema, type LoginFormData } from "@/shared/validation/schemas";

const router = useRouter();
const authStore = useAuthStore();
const error = computed(() => authStore.error);
const isLoading = ref(false);

const onSubmit = async (values: LoginFormData) => {
  isLoading.value = true;
  
  try {
    await authStore.signIn(values.email, values.password);
    router.push({ name: "home" });
  } catch (err) {
    console.error(err);
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

