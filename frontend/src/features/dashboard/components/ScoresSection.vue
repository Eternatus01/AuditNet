<template>
  <div class="scores-section">
    <!-- Bar Chart Overview -->
    <div class="scores-chart-wrapper">
      <h3 class="chart-title">Overall Scores</h3>
      <BarChart
        :performance-score="performanceScore"
        :accessibility-score="accessibilityScore"
        :best-practices-score="bestPracticesScore"
        :seo-score="seoScore"
      />
    </div>

    <!-- Score Cards Grid -->
    <div class="scores-grid">
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
  gap: 2.5rem;
}

.scores-chart-wrapper {
  background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(30, 30, 40, 0.95) 100%);
  border: 1px solid rgba(100, 108, 255, 0.2);
  border-radius: 16px;
  padding: 2rem 2rem 2.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  position: relative;
  overflow: hidden;
}

.scores-chart-wrapper::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(100, 108, 255, 0.5) 50%,
    transparent 100%
  );
}

.chart-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.98);
  margin: 0 0 1rem 0;
  text-align: center;
  letter-spacing: -0.5px;
  background: linear-gradient(135deg, #646cff 0%, #8b93ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.scores-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

@media (max-width: 968px) {
  .scores-chart-wrapper {
    padding: 2rem 1.5rem;
  }

  .chart-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .scores-section {
    gap: 2rem;
  }

  .scores-chart-wrapper {
    padding: 1.5rem 1rem;
    border-radius: 12px;
  }

  .chart-title {
    font-size: 1.35rem;
  }

  .scores-grid {
    grid-template-columns: 1fr;
  }
}
</style>
