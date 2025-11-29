<template>
  <div class="score-card">
    <div class="score-header">
      <div class="score-icon" :class="iconClass">
        <slot name="icon"></slot>
      </div>
      <h3>{{ title }}</h3>
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
    <div class="score-value">
      <span class="score-number">{{ score ?? '--' }}</span>
      <span class="score-label">/ 100</span>
    </div>
    <div class="score-progress">
      <div class="score-progress-bar" :style="{ width: (score ?? 0) + '%' }"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IconButton } from '@/shared/ui/atoms';
import IconLucideChevronDown from '~icons/lucide/chevron-down';
import type { ScoreDisplay } from "../types";

defineProps<{
  title: string;
  score: ScoreDisplay;
  iconClass: string;
  description: string;
  isExpanded: boolean;
}>();

defineEmits<{
  'toggle-info': [];
}>();
</script>

