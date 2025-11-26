<template>
  <div class="empty-state">
    <div class="empty-icon">
      <slot name="icon">
        <IconLucideInbox />
      </slot>
    </div>
    
    <h3 class="empty-title">
      <slot name="title">{{ title }}</slot>
    </h3>
    
    <p class="empty-description">
      <slot name="description">{{ description }}</slot>
    </p>
    
    <div v-if="$slots.action" class="empty-action">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup lang="ts">
import IconLucideInbox from '~icons/lucide/inbox';

withDefaults(
  defineProps<{
    title?: string;
    description?: string;
  }>(),
  {
    title: 'Нет данных',
    description: '',
  }
);
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.3);
  margin-bottom: 1.5rem;
}

.empty-icon :deep(svg) {
  width: 100%;
  height: 100%;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 0.75rem 0;
}

.empty-description {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0 0 2rem 0;
  max-width: 400px;
}

.empty-action {
  margin-top: 1rem;
}
</style>

