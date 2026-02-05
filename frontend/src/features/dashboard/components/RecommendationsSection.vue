<template>
  <div v-if="recommendations && recommendations.length > 0" class="recommendations-section">
    <div class="section-header">
      <h2 class="section-title">Рекомендации по улучшению</h2>
      <span class="recommendations-count">{{ recommendations.length }} проблем</span>
    </div>
    <p class="section-subtitle">
      Конкретные советы по оптимизации на основе анализа Lighthouse
    </p>

    <RecommendationFilters
      v-model="selectedCategory"
      :categories="categories"
    />

    <div class="recommendations-list">
      <RecommendationCard
        v-for="rec in filteredRecommendations"
        :key="rec.id"
        :recommendation="rec"
        :category-label="getCategoryLabel(rec.category)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import type { AuditRecommendation } from '@/features/history/types';
import { useRecommendations } from '../composables/useRecommendations';
import RecommendationCard from './RecommendationCard.vue';
import RecommendationFilters from './RecommendationFilters.vue';

const props = defineProps<{
  recommendations: AuditRecommendation[];
}>();

const selectedCategory = ref('all');

const { categories, getCategoryLabel, filterByCategory } = useRecommendations(props.recommendations);

const filteredRecommendations = computed(() => {
  return filterByCategory(selectedCategory.value);
});
</script>

<style scoped>
.recommendations-section {
  margin-top: 3rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.recommendations-count {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary, rgba(255, 255, 255, 0.6));
  padding: 0.375rem 0.875rem;
  background: rgba(124, 58, 237, 0.1);
  border-radius: var(--radius-md, 12px);
}

.recommendations-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .recommendations-section {
    margin-top: 2rem;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
}
</style>
