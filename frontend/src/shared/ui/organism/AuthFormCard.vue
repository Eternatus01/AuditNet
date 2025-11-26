<template>
  <section class="auth-container">
    <div class="auth-form">
      <h2>{{ title }}</h2>

      <slot />

      <button type="submit" :disabled="isLoading" class="submit-button">
        <span v-if="!isLoading">
          <slot name="submit-label">
            {{ submitLabel }}
          </slot>
        </span>
        <span v-else class="loading-text">
          <span class="spinner"></span>
          Загрузка...
        </span>
      </button>

      <p v-if="error" class="error-message">{{ error }}</p>
    </div>
  </section>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  submitLabel: string;
  error?: string | null;
  isLoading?: boolean;
}>();
</script>

<style scoped>
.submit-button {
  width: 100%;
  justify-content: center;
  position: relative;
  min-height: 44px;
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

