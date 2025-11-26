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
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  white-space: nowrap;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

/* Sizes */
.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  min-height: 36px;
}

.btn-md {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  min-height: 44px;
}

.btn-lg {
  padding: 1rem 2rem;
  font-size: 1.125rem;
  min-height: 52px;
}

/* Variants */
.btn-primary {
  background: linear-gradient(135deg, #646cff 0%, #5558d9 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(100, 108, 255, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(100, 108, 255, 0.4);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.btn-ghost {
  background: transparent;
  color: rgba(255, 255, 255, 0.8);
}

.btn-ghost:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 1);
}

.btn-danger {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
}

.btn-danger:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(81, 207, 102, 0.3);
}

.btn-success:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(81, 207, 102, 0.4);
}

/* Full Width */
.btn-full-width {
  width: 100%;
}

/* Loading State */
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

