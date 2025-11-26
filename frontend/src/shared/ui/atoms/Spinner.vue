<template>
  <span :class="['spinner', `spinner-${variant}`]" :style="spinnerStyle" role="status" aria-label="Загрузка">
    <span class="sr-only">Загрузка...</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

export type SpinnerVariant = 'primary' | 'white' | 'inherit';

const props = withDefaults(
  defineProps<{
    size?: number;
    variant?: SpinnerVariant;
  }>(),
  {
    size: 16,
    variant: 'white',
  }
);

const spinnerStyle = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
}));
</script>

<style scoped>
.spinner {
  display: inline-block;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: currentColor;
  animation: spin 0.6s linear infinite;
}

.spinner-primary {
  border-color: rgba(100, 108, 255, 0.3);
  border-top-color: #646cff;
}

.spinner-white {
  border-color: rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
}

.spinner-inherit {
  border-color: rgba(255, 255, 255, 0.3);
  border-top-color: currentColor;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>

