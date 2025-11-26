<template>
  <div :class="cardClasses">
    <div v-if="$slots.header || title" class="card-header">
      <slot name="header">
        <h3 v-if="title" class="card-title">{{ title }}</h3>
      </slot>
    </div>
    
    <div class="card-body">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

export type CardVariant = 'default' | 'elevated' | 'bordered' | 'flat';

const props = withDefaults(
  defineProps<{
    variant?: CardVariant;
    title?: string;
    hoverable?: boolean;
    clickable?: boolean;
  }>(),
  {
    variant: 'default',
    hoverable: false,
    clickable: false,
  }
);

const cardClasses = computed(() => [
  'card',
  `card-${props.variant}`,
  {
    'card-hoverable': props.hoverable,
    'card-clickable': props.clickable,
  },
]);
</script>

<style scoped>
.card {
  background: rgba(26, 26, 26, 0.6);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.card-default {
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.card-elevated {
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.card-bordered {
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.card-flat {
  border: none;
  background: rgba(26, 26, 26, 0.4);
}

.card-hoverable:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
  border-color: rgba(255, 255, 255, 0.2);
}

.card-clickable {
  cursor: pointer;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.card-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.9);
}

.card-body {
  padding: 1.5rem;
}

.card-footer {
  padding: 1.25rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.2);
}
</style>

