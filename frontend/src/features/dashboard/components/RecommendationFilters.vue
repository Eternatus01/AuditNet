<template>
  <div class="recommendations-filters">
    <button
      v-for="cat in categories"
      :key="cat.key"
      class="filter-btn"
      :class="{ active: modelValue === cat.key }"
      @click="$emit('update:modelValue', cat.key)"
    >
      {{ cat.label }} ({{ cat.count }})
    </button>
  </div>
</template>

<script setup lang="ts">
interface Category {
  key: string;
  label: string;
  count: number;
}

defineProps<{
  categories: Category[];
  modelValue: string;
}>();

defineEmits<{
  'update:modelValue': [value: string];
}>();
</script>

<style scoped>
.recommendations-filters {
  display: flex;
  gap: 0.75rem;
  margin: 2rem 0;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.625rem 1.25rem;
  background: var(--bg-secondary, #18181b);
  border: 1px solid var(--border-color, #27272a);
  border-radius: var(--radius-lg, 16px);
  color: var(--text-secondary, rgba(255, 255, 255, 0.7));
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-btn:hover {
  border-color: var(--primary-color, #7c3aed);
  color: #fff;
}

.filter-btn.active {
  background: var(--primary-color, #7c3aed);
  border-color: var(--primary-color, #7c3aed);
  color: #fff;
}
</style>
