<template>
  <section class="audit-meta">
    <div class="audit-meta-item">
      <span class="audit-meta-label">URL:</span>
      <a
        class="audit-meta-value audit-link"
        :href="url"
        target="_blank"
        rel="noopener noreferrer"
      >
        {{ url }}
      </a>
    </div>
    <div class="audit-meta-item">
      <span class="audit-meta-label">Дата проверки:</span>
      <span class="audit-meta-value">{{ formattedDate }}</span>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from "vue";

interface Props {
  url: string;
  createdAt: string;
  formatDate: (date: string) => string;
}

const props = defineProps<Props>();

const formattedDate = computed(() => props.formatDate(props.createdAt));
</script>

<style scoped>
.audit-meta {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  background: #1a1a1a;
  border: 1px solid #333;
}

.audit-meta-item {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  font-size: 0.95rem;
}

.audit-meta-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 600;
}

.audit-meta-value {
  color: rgba(255, 255, 255, 0.9);
}

.audit-link {
  color: #646cff;
  text-decoration: none;
  word-break: break-all;
}

.audit-link:hover {
  text-decoration: underline;
}
</style>

