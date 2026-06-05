<template>
  <div class="audit-changes">
    <div class="changes-header">
      <div class="header-icon">
        <IconLucideGitCompare />
      </div>
      <div>
        <h3>Изменения с прошлого анализа</h3>
        <p v-if="diff?.previous_audited_at" class="header-sub">
          Сравнение с проверкой от {{ formatDate(diff.previous_audited_at) }}
        </p>
      </div>
    </div>

    <LoadingState v-if="isLoading" text="Загрузка изменений..." size="md" />

    <div v-else-if="!diff || !diff.has_previous" class="no-data">
      Недостаточно данных для сравнения — нужно минимум два анализа этого сайта.
    </div>

    <template v-else>
      <div class="score-deltas">
        <div
          v-for="item in scoreItems"
          :key="item.key"
          class="delta-card"
        >
          <span class="delta-label">{{ item.label }}</span>
          <div class="delta-values">
            <span class="delta-current">{{ item.data.current }}</span>
            <span class="delta-badge" :class="deltaClass(item.data.delta)">
              {{ formatDelta(item.data.delta) }}
            </span>
          </div>
        </div>
      </div>

      <ul v-if="diff.explanations.length" class="explanations">
        <li v-for="(text, index) in diff.explanations" :key="index">
          {{ text }}
        </li>
      </ul>
      <p v-else class="no-data">Показатели не изменились с прошлого раза.</p>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { AuditDiff } from '../types';
import LoadingState from '@/shared/ui/molecules/LoadingState.vue';
import IconLucideGitCompare from '~icons/lucide/git-compare';

interface Props {
  diff: AuditDiff | null;
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
});

const SCORE_LABELS: Record<string, string> = {
  performance: 'Производительность',
  accessibility: 'Доступность',
  best_practices: 'Лучшие практики',
  seo: 'SEO',
};

const scoreItems = computed(() => {
  if (!props.diff) return [];
  return Object.keys(SCORE_LABELS)
    .filter((key) => props.diff!.score_deltas[key])
    .map((key) => ({
      key,
      label: SCORE_LABELS[key],
      data: props.diff!.score_deltas[key],
    }));
});

const deltaClass = (delta: number) => {
  if (delta > 0) return 'positive';
  if (delta < 0) return 'negative';
  return 'neutral';
};

const formatDelta = (delta: number) => {
  if (delta > 0) return `+${delta}`;
  return `${delta}`;
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};
</script>

<style scoped>
.audit-changes {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border: 1px solid rgba(100, 108, 255, 0.15);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.changes-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  border-radius: 12px;
  flex-shrink: 0;
}

.header-icon svg {
  width: 22px;
  height: 22px;
  color: #fff;
}

.changes-header h3 {
  margin: 0;
  color: #fff;
  font-size: 1.25rem;
}

.header-sub {
  margin: 0.25rem 0 0 0;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.85rem;
}

.no-data {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.95rem;
  margin: 0;
}

.score-deltas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
}

.delta-card {
  background: rgba(15, 15, 30, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.delta-label {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.65);
}

.delta-values {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.delta-current {
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
  line-height: 1;
}

.delta-badge {
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.2rem 0.55rem;
  border-radius: 8px;
}

.delta-badge.positive {
  background: rgba(76, 175, 80, 0.15);
  color: #4caf50;
}

.delta-badge.negative {
  background: rgba(244, 67, 54, 0.15);
  color: #f44336;
}

.delta-badge.neutral {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.6);
}

.explanations {
  margin: 0;
  padding-left: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.explanations li {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.95rem;
  line-height: 1.5;
}
</style>
