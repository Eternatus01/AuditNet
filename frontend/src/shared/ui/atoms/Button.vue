<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    :aria-label="ariaLabel"
    @click="handleClick"
  >
    <span v-if="loading" class="button-spinner">
      <Spinner :size="spinnerSize" />
    </span>
    <slot v-if="!loading" name="icon-left" />
    <span v-if="!loading || showTextWhileLoading" class="button-text">
      <slot />
    </span>
    <slot v-if="!loading" name="icon-right" />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Spinner from './Spinner.vue';

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger' | 'success';
export type ButtonSize = 'sm' | 'md' | 'lg';

const props = withDefaults(
  defineProps<{
    variant?: ButtonVariant;
    size?: ButtonSize;
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
    fullWidth?: boolean;
    showTextWhileLoading?: boolean;
    ariaLabel?: string;
  }>(),
  {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    fullWidth: false,
    showTextWhileLoading: false,
  }
);

const emit = defineEmits<{
  click: [];
}>();

const buttonClasses = computed(() => [
  'btn',
  `btn-${props.variant}`,
  `btn-${props.size}`,
  {
    'btn-full-width': props.fullWidth,
    'btn-loading': props.loading,
  },
]);

const spinnerSize = computed(() => {
  const sizeMap = { sm: 14, md: 16, lg: 18 };
  return sizeMap[props.size];
});

const handleClick = () => {
  if (!props.disabled && !props.loading) {
    emit('click');
  }
};
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-family: inherit;
  font-weight: 600;
  border: none;
  border-radius: var(--radius-lg, 18px);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  white-space: nowrap;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn:focus-visible {
  outline: 2px solid var(--primary-light, #8b5cf6);
  outline-offset: 2px;
}

.btn-sm {
  padding: 0.5rem 1.25rem;
  font-size: 0.9375rem;
  min-height: 38px;
}

.btn-md {
  padding: 0.6875rem 1.625rem;
  font-size: 1rem;
  min-height: 46px;
}

.btn-lg {
  padding: 0.9375rem 2.125rem;
  font-size: 1.0625rem;
  min-height: 52px;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-color, #7c3aed) 0%, var(--primary-hover, #6d28d9) 100%);
  color: white;
  box-shadow: 0 4px 14px 0 rgba(124, 58, 237, 0.35);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 14px 0 rgba(124, 58, 237, 0.35);
}

.btn-secondary {
  background: var(--bg-tertiary, rgba(39, 39, 42, 0.8));
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
  border: 1px solid var(--border-color, #27272a);
}

.btn-secondary:hover:not(:disabled) {
  background: var(--bg-elevated, #1f1f23);
  border-color: var(--border-color-hover, #3f3f46);
}

.btn-ghost {
  background: transparent;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
}

.btn-ghost:hover:not(:disabled) {
  background: var(--bg-tertiary, rgba(39, 39, 42, 0.5));
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
}

.btn-danger {
  background: linear-gradient(135deg, var(--danger-color, #ef4444) 0%, #dc2626 100%);
  color: white;
  box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.35);
}

.btn-danger:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, var(--accent-color, #10b981) 0%, var(--accent-hover, #059669) 100%);
  color: white;
  box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.35);
}

.btn-success:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-full-width {
  width: 100%;
}

.btn-loading {
  position: relative;
}

.button-spinner {
  display: inline-flex;
  align-items: center;
}

.button-text {
  display: inline-flex;
  align-items: center;
}
</style>

