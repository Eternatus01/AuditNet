<template>
  <div class="input-field">
    <input
      :id="name"
      :name="name"
      :type="type"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :value="inputValue"
      class="auth-input"
      :class="{ 'input-error': errorMessage }"
      :aria-label="label || placeholder"
      :aria-invalid="!!errorMessage"
      :aria-describedby="errorMessage ? `${name}-error` : undefined"
      @input="handleChange"
      @blur="handleBlur"
    />
    <span v-if="errorMessage" :id="`${name}-error`" class="error-text">
      {{ errorMessage }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { useField } from "vee-validate";
import { toRef } from "vue";
import { debounce } from "@/shared/composables/useDebounce";

const props = withDefaults(
  defineProps<{
    name: string;
    type?: string;
    placeholder?: string;
    label?: string;
    autocomplete?: string;
    debounceMs?: number;
  }>(),
  {
    debounceMs: 0, // По умолчанию без debounce
  }
);

const name = toRef(props, "name");

const { 
  value: inputValue, 
  errorMessage, 
  handleBlur, 
  handleChange: originalHandleChange,
  validate 
} = useField(name, undefined, {
  validateOnValueUpdate: false,
});

// Создаем debounced версию валидации если указан debounceMs
const debouncedValidate = props.debounceMs > 0 
  ? debounce(() => validate(), props.debounceMs)
  : null;

// Обработчик изменения с опциональным debounce
const handleChange = (event: InputEvent) => {
  originalHandleChange(event);
  
  if (debouncedValidate) {
    debouncedValidate();
  }
};
</script>

<style scoped>
.input-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
}

.auth-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #333;
  border-radius: 8px;
  background: #1a1a1a;
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.auth-input:focus {
  outline: none;
  border-color: #646cff;
  box-shadow: 0 0 0 3px rgba(100, 108, 255, 0.1);
}

.auth-input.input-error {
  border-color: #ff6b6b;
}

.auth-input.input-error:focus {
  box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.error-text {
  color: #ff6b6b;
  font-size: 0.875rem;
  margin-top: -0.25rem;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}
</style>

