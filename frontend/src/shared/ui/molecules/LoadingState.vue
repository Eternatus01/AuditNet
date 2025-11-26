<template>
  <div class="loading-state">
    <Spinner :size="spinnerSize" variant="primary" />
    <p class="loading-text">
      <slot>{{ text }}</slot>
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Spinner } from '@/shared/ui/atoms';

const props = withDefaults(
  defineProps<{
    text?: string;
    size?: 'sm' | 'md' | 'lg';
  }>(),
  {
    text: 'Загрузка...',
    size: 'md',
  }
);

const spinnerSize = computed(() => {
  const sizeMap = { sm: 24, md: 32, lg: 48 };
  return sizeMap[props.size];
});
</script>

<style scoped>
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  gap: 1.5rem;
}

.loading-text {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}
</style>

