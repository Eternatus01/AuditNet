<template>
  <div v-if="lcp !== null || fid !== null || cls !== null" class="metrics-section">
    <div class="section-header">
      <h2 class="section-title">Core Web Vitals</h2>
      <IconButton
        class="info-toggle"
        variant="ghost"
        size="sm"
        :aria-expanded="isExpanded('web-vitals')"
        aria-label="Показать описание Core Web Vitals"
        @click="onToggle('web-vitals')"
      >
        <IconLucideChevronDown :class="{ rotated: isExpanded('web-vitals') }" />
      </IconButton>
    </div>
    <div v-if="isExpanded('web-vitals')" class="info-description section-description">
      {{ descriptions.coreWebVitals.section }}
    </div>
    <p class="section-subtitle">Ключевые метрики производительности от Google</p>

    <!-- Metric Cards Grid -->
    <div class="metrics-grid">
      <MetricCard
        title="LCP"
        full-name="Largest Contentful Paint"
        :formatted-value="formatLCP(lcp)"
        :status="getMetricStatus('lcp', lcp)"
        threshold="Цель: < 2.5s"
        :description="descriptions.coreWebVitals.lcp"
        :is-expanded="isExpanded('lcp')"
        @toggle-info="onToggle('lcp')"
      >
        <template #icon>
          <IconLucideClock />
        </template>
      </MetricCard>

      <MetricCard
        title="FID"
        full-name="First Input Delay"
        :formatted-value="formatFID(fid)"
        :status="getMetricStatus('fid', fid)"
        threshold="Цель: < 100ms"
        :description="descriptions.coreWebVitals.fid"
        :is-expanded="isExpanded('fid')"
        @toggle-info="onToggle('fid')"
      >
        <template #icon>
          <IconLucideZap />
        </template>
      </MetricCard>

      <MetricCard
        title="CLS"
        full-name="Cumulative Layout Shift"
        :formatted-value="formatCLS(cls)"
        :status="getMetricStatus('cls', cls)"
        threshold="Цель: < 0.1"
        :description="descriptions.coreWebVitals.cls"
        :is-expanded="isExpanded('cls')"
        @toggle-info="onToggle('cls')"
      >
        <template #icon>
          <IconLucideLayout />
        </template>
      </MetricCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import MetricCard from "./MetricCard.vue";
import { IconButton } from "@/shared/ui/atoms";
import { formatLCP, formatFID, formatCLS } from "@/shared/utils/formatters";
import { getMetricStatus } from "@/shared/utils/metrics";
import IconLucideChevronDown from "~icons/lucide/chevron-down";
import IconLucideClock from "~icons/lucide/clock";
import IconLucideZap from "~icons/lucide/zap";
import IconLucideLayout from "~icons/lucide/layout";

import type { AuditDescriptions } from "../types";

defineProps<{
  lcp: number | null;
  fid: number | null;
  cls: number | null;
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
.section-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.section-header .section-title {
  margin: 0;
}

.section-description {
  max-width: 800px;
  margin: 0 auto 1rem;
}

@media (max-width: 768px) {
  .section-header {
    gap: 0.5rem;
  }
}
</style>
