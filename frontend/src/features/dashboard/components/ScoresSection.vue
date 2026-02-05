<template>
  <div class="scores-section">
    <div class="info-notice">
      <IconLucideInfo class="notice-icon" />
      <div class="notice-content">
        <strong>Важно:</strong> Сайты на React, Vue, Angular (SPA) часто получают низкие баллы Performance из-за 
        клиентского рендеринга. Lighthouse тестирует на медленном соединении и слабом устройстве, поэтому даже 
        быстрые сайты могут показать 20-40 баллов. Это нормально для SPA без SSR.
      </div>
    </div>
    
    <div class="scores-grid-modern">
      <div class="featured-card">
        <div class="featured-header">
          <h3>Overall Performance</h3>
          <p class="featured-subtitle">Сводная оценка производительности</p>
        </div>
        <BarChart
          :performance-score="performanceScore"
          :accessibility-score="accessibilityScore"
          :best-practices-score="bestPracticesScore"
          :seo-score="seoScore"
          orientation="vertical"
        />
      </div>

      <ScoreCard
        title="Performance"
        :score="performanceScore"
        icon-class="performance"
        :description="descriptions.scores.performance"
        :is-expanded="isExpanded('performance')"
        @toggle-info="onToggle('performance')"
      >
        <template #icon>
          <IconLucideActivity />
        </template>
      </ScoreCard>

      <ScoreCard
        title="Accessibility"
        :score="accessibilityScore"
        icon-class="accessibility"
        :description="descriptions.scores.accessibility"
        :is-expanded="isExpanded('accessibility')"
        @toggle-info="onToggle('accessibility')"
      >
        <template #icon>
          <IconLucideUsers />
        </template>
      </ScoreCard>

      <ScoreCard
        title="Best Practices"
        :score="bestPracticesScore"
        icon-class="best-practices"
        :description="descriptions.scores.bestPractices"
        :is-expanded="isExpanded('best-practices')"
        @toggle-info="onToggle('best-practices')"
      >
        <template #icon>
          <IconLucideCheckCircle />
        </template>
      </ScoreCard>

      <ScoreCard
        title="SEO"
        :score="seoScore"
        icon-class="best-practices"
        :description="descriptions.scores.seo"
        :is-expanded="isExpanded('seo')"
        @toggle-info="onToggle('seo')"
      >
        <template #icon>
          <IconLucideSearch />
        </template>
      </ScoreCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import ScoreCard from "./ScoreCard.vue";
import { BarChart } from "@/shared/ui/molecules";
import IconLucideActivity from "~icons/lucide/activity";
import IconLucideUsers from "~icons/lucide/users";
import IconLucideCheckCircle from "~icons/lucide/check-circle";
import IconLucideSearch from "~icons/lucide/search";
import IconLucideInfo from "~icons/lucide/info";

import type { ScoreDisplay, AuditDescriptions } from "../types";

defineProps<{
  performanceScore: ScoreDisplay;
  accessibilityScore: ScoreDisplay;
  bestPracticesScore: ScoreDisplay;
  seoScore: ScoreDisplay;
  descriptions: AuditDescriptions;
  isExpanded: (key: string) => boolean;
}>();

const emit = defineEmits<{
  toggle: [key: string];
}>();

const onToggle = (_key: string) => {
  emit("toggle", _key);
};
</script>

<style scoped>
.scores-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-notice {
  display: flex;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: var(--radius-lg, 16px);
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9375rem;
  line-height: 1.6;
}

.notice-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  color: #3b82f6;
  margin-top: 0.125rem;
}

.notice-content {
  flex: 1;
}

.notice-content strong {
  color: #fff;
  font-weight: 600;
}

.scores-grid-modern {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  align-items: start;
}

.featured-card {
  grid-column: span 2;
  grid-row: span 2;
  background: linear-gradient(135deg, var(--bg-secondary, #18181b) 0%, var(--bg-elevated, #1f1f23) 100%);
  border: 1px solid var(--border-color, #27272a);
  border-radius: var(--radius-xl, 24px);
  padding: 2.5rem;
  box-shadow: var(--shadow-lg);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  min-height: 420px;
}

.featured-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
  border-color: var(--border-color-hover, #3f3f46);
}

.featured-header {
  margin-bottom: 2rem;
}

.featured-header h3 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.02em;
}

.featured-subtitle {
  font-size: 1rem;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
  margin: 0;
}

@media (max-width: 1400px) {
  .scores-grid-modern {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .featured-card {
    grid-column: span 3;
    grid-row: span 1;
    min-height: 350px;
  }
}

@media (max-width: 968px) {
  .scores-grid-modern {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .featured-card {
    grid-column: span 2;
  }
}

@media (max-width: 768px) {
  .scores-grid-modern {
    grid-template-columns: 1fr;
  }
  
  .featured-card {
    grid-column: span 1;
    min-height: 300px;
  }
  
  .info-notice {
    padding: 1rem 1.25rem;
    font-size: 0.875rem;
  }
}
</style>
