<template>
  <div class="url-input-section">
    <div class="url-input-wrapper" :class="{ 'has-error': validationError, 'is-valid': isValid }">
      <IconLucideLink class="url-icon" />
      <input
        type="url"
        class="url-input"
        :value="modelValue"
        placeholder="https://example.com"
        autocomplete="url"
        aria-label="URL сайта для анализа"
        :aria-invalid="!!validationError"
        @input="handleInput"
      />
      <button
        class="analyze-btn"
        type="button"
        :disabled="isLoading || !!validationError || !modelValue"
        :aria-label="isLoading ? 'Анализ выполняется' : 'Начать анализ'"
        @click="$emit('analyze')"
      >
        <span v-if="!isLoading">Анализировать</span>
        <span v-else>Анализ...</span>
        <IconLucideArrowRight v-if="!isLoading" />
        <IconLucideLoader2 v-else class="spinner" />
      </button>
    </div>
    <transition name="fade">
      <p v-if="validationError" class="error-message">
        {{ validationError }}
      </p>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useDebounce } from "@/shared/composables/useDebounce";
import IconLucideLink from "~icons/lucide/link";
import IconLucideArrowRight from "~icons/lucide/arrow-right";
import IconLucideLoader2 from "~icons/lucide/loader-2";

const props = defineProps<{
  modelValue: string;
  isLoading: boolean;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: string];
  analyze: [];
}>();

const localValue = ref(props.modelValue);
const validationError = ref<string | null>(null);
const isValid = ref(false);

const debouncedValue = useDebounce(localValue, 500);

const validateUrl = (url: string): { valid: boolean; error: string | null } => {
  if (!url) {
    return { valid: false, error: null };
  }

  try {
    const urlObj = new URL(url);

    if (!["http:", "https:"].includes(urlObj.protocol)) {
      return {
        valid: false,
        error: "URL должен начинаться с http:// или https://",
      };
    }

    if (!urlObj.hostname || urlObj.hostname.length < 3) {
      return {
        valid: false,
        error: "Укажите корректный домен",
      };
    }

    return { valid: true, error: null };
  } catch {
    return {
      valid: false,
      error: "Некорректный формат URL. Пример: https://example.com",
    };
  }
};

const handleInput = (event: InputEvent) => {
  const value = (event.target as HTMLInputElement).value;
  localValue.value = value;
  emit("update:modelValue", value);

  if (!value) {
    validationError.value = null;
    isValid.value = false;
  }
};

watch(debouncedValue, (newValue) => {
  const result = validateUrl(newValue);
  validationError.value = result.error;
  isValid.value = result.valid;
});
</script>

<style scoped>
.url-input-wrapper {
  transition: all 0.3s ease;
}

.url-input-wrapper.has-error {
  border-color: #ff4e42;
  box-shadow: 0 0 0 3px rgba(255, 78, 66, 0.1);
}

.url-input-wrapper.is-valid {
  border-color: #0cce6b;
  box-shadow: 0 0 0 3px rgba(12, 206, 107, 0.1);
}

.error-message {
  margin-top: 0.5rem;
  margin-bottom: 0;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  color: #ff4e42;
  background-color: rgba(255, 78, 66, 0.1);
  border-left: 3px solid #ff4e42;
  border-radius: 4px;
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
