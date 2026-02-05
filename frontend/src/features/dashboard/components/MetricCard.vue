<template>
  <div class="metric-card" :class="status">
    <div class="status-indicator" :class="status"></div>
    <div class="metric-header">
      <div class="metric-icon" :class="status">
        <slot name="icon"></slot>
      </div>
      <div class="metric-header-content">
        <div class="metric-title-row">
          <h3>{{ title }}</h3>
          <span class="metric-badge" :class="status">{{ statusLabel }}</span>
        </div>
        <p class="metric-description">{{ fullName }}</p>
      </div>
      <IconButton
        class="info-toggle"
        variant="ghost"
        size="sm"
        :aria-expanded="isExpanded"
        aria-label="Показать описание"
        @click="$emit('toggle-info')"
      >
        <IconLucideChevronDown :class="{ rotated: isExpanded }" />
      </IconButton>
    </div>
    <div v-if="isExpanded" class="info-description">
      {{ description }}
    </div>
    <div class="metric-value">
      <span>{{ formattedValue }}</span>
    </div>
    <p class="metric-threshold">{{ threshold }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { IconButton } from "@/shared/ui/atoms";
import IconLucideChevronDown from "~icons/lucide/chevron-down";
import type { MetricStatus } from "../types";

const props = defineProps<{
  title: string;
  fullName: string;
  formattedValue: string;
  status: MetricStatus;
  threshold: string;
  description: string;
  isExpanded: boolean;
}>();

defineEmits<{
  "toggle-info": [];
}>();

const statusLabel = computed(() => {
  const labels: Record<MetricStatus, string> = {
    'good': 'GOOD',
    'poor': 'POOR',
    'unknown': 'N/A'
  };
  return labels[props.status] || 'N/A';
});
</script>
