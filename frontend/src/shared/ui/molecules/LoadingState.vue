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
    size: 'lg',
  }
);

const spinnerSize = computed(() => {
  const sizeMap = { sm: 24, md: 36, lg: 56 };
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
  gap: 1.25rem;
}

.loading-text {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.65);
  margin: 0;
  text-align: center;
}
</style>

