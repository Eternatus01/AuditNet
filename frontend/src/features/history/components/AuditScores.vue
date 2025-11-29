<template>
  <div class="audit-scores">
    <div
      v-for="score in scores"
      :key="score.key"
      class="score-item"
      :class="getScoreClass(score.value)"
    >
      <span class="score-label">{{ score.label }}</span>
      <span class="score-value">{{ score.value ?? "--" }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { Audit, ScoreClass } from "../types";

interface Props {
  audit: Audit;
  getScoreClass: (score: number | null) => ScoreClass;
}

const props = defineProps<Props>();

const scores = computed(() => [
  { key: "performance", label: "Performance", value: props.audit.performance },
  { key: "accessibility", label: "Accessibility", value: props.audit.accessibility },
  { key: "best_practices", label: "Best Practices", value: props.audit.best_practices },
  { key: "seo", label: "SEO", value: props.audit.seo },
]);
</script>

<style scoped>
.audit-scores {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
}

.score-item {
  display: flex;
  flex-direction: column;
  padding: 0.75rem;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.03);
}

.score-label {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 0.25rem;
}

.score-value {
  font-size: 1.5rem;
  font-weight: 700;
}

.score-item.good {
  border-left: 3px solid #4caf50;
}

.score-item.good .score-value {
  color: #4caf50;
}

.score-item.average {
  border-left: 3px solid #ffc107;
}

.score-item.average .score-value {
  color: #ffc107;
}

.score-item.poor {
  border-left: 3px solid #f44336;
}

.score-item.poor .score-value {
  color: #f44336;
}
</style>

