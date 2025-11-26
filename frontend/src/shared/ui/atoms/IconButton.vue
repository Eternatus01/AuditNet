<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="iconButtonClasses"
    :aria-label="ariaLabel"
    :aria-expanded="ariaExpanded"
    @click="emit('click', $event)"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

export type IconButtonVariant = 'default' | 'ghost' | 'danger';
export type IconButtonSize = 'sm' | 'md' | 'lg';

const props = withDefaults(
  defineProps<{
    variant?: IconButtonVariant;
    size?: IconButtonSize;
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    ariaLabel?: string;
    ariaExpanded?: boolean;
  }>(),
  {
    variant: 'default',
    size: 'md',
    type: 'button',
    disabled: false,
  }
);

const emit = defineEmits<{
  click: [];
}>();

const iconButtonClasses = computed(() => [
  'icon-btn',
  `icon-btn-${props.variant}`,
  `icon-btn-${props.size}`,
]);
</script>

<style scoped>
.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
  padding: 0;
}

.icon-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
}

.icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.icon-btn:focus-visible {
  outline: 2px solid #646cff;
  outline-offset: 2px;
}

/* Sizes */
.icon-btn-sm {
  width: 32px;
  height: 32px;
}

.icon-btn-md {
  width: 36px;
  height: 36px;
}

.icon-btn-lg {
  width: 44px;
  height: 44px;
}

/* Variants */
.icon-btn-ghost {
  background: transparent;
}

.icon-btn-ghost:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.05);
}

.icon-btn-danger {
  color: #ff6b6b;
}

.icon-btn-danger:hover:not(:disabled) {
  background: rgba(255, 107, 107, 0.1);
  color: #ff6b6b;
}
</style>

