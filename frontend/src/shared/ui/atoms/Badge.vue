<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

export type BadgeVariant = 'good' | 'warning' | 'danger' | 'info' | 'neutral';
export type BadgeSize = 'sm' | 'md' | 'lg';

const props = withDefaults(
  defineProps<{
    variant?: BadgeVariant;
    size?: BadgeSize;
  }>(),
  {
    variant: 'neutral',
    size: 'md',
  }
);

const badgeClasses = computed(() => [
  'badge',
  `badge-${props.variant}`,
  `badge-${props.size}`,
]);
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  font-weight: 600;
  white-space: nowrap;
  line-height: 1;
}

/* Sizes */
.badge-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.badge-md {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.badge-lg {
  padding: 0.5rem 1rem;
  font-size: 1rem;
}

/* Variants */
.badge-good {
  background: rgba(81, 207, 102, 0.2);
  color: #51cf66;
  border: 1px solid rgba(81, 207, 102, 0.3);
}

.badge-warning {
  background: rgba(255, 184, 0, 0.2);
  color: #ffb800;
  border: 1px solid rgba(255, 184, 0, 0.3);
}

.badge-danger {
  background: rgba(255, 107, 107, 0.2);
  color: #ff6b6b;
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.badge-info {
  background: rgba(100, 108, 255, 0.2);
  color: #646cff;
  border: 1px solid rgba(100, 108, 255, 0.3);
}

.badge-neutral {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>

