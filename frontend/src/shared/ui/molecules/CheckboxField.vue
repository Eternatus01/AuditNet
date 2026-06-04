<template>
  <div class="checkbox-field">
    <Field
      :name="name"
      type="checkbox"
      :value="true"
      :unchecked-value="false"
      v-slot="{ field, errorMessage }"
    >
      <label class="checkbox-label">
        <input
          type="checkbox"
          class="checkbox-input"
          :name="field.name"
          :checked="Boolean(field.checked)"
          @change="field.onChange"
          @blur="field.onBlur"
        />
        <span class="checkbox-text">
          <slot />
        </span>
      </label>
      <p v-if="errorMessage" class="field-error">{{ errorMessage }}</p>
    </Field>
  </div>
</template>

<script setup lang="ts">
import { Field } from "vee-validate";

defineProps<{
  name: string;
}>();
</script>

<style scoped>
.checkbox-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.625rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.75);
  line-height: 1.45;
}

.checkbox-input {
  margin-top: 0.2rem;
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
  accent-color: #7c3aed;
  cursor: pointer;
}

.checkbox-text :deep(a) {
  color: #a78bfa;
  text-decoration: underline;
  text-underline-offset: 2px;
}

.checkbox-text :deep(a:hover) {
  color: #c4b5fd;
}

.field-error {
  font-size: 0.8125rem;
  color: #f87171;
  margin: 0.25rem 0 0 1.625rem;
}
</style>
