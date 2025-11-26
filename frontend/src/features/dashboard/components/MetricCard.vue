<template>
  <div class="metric-card" :class="status">
    <div class="metric-header">
      <div class="metric-icon">
        <slot name="icon"></slot>
      </div>
      <h3>{{ title }}</h3>
      <span class="metric-badge" :class="status">{{ status }}</span>
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
    <p class="metric-description">{{ fullName }}</p>
    <p class="metric-threshold">{{ threshold }}</p>
  </div>
</template>

<script setup lang="ts">
import { IconButton } from '@/shared/ui/atoms';
import IconLucideChevronDown from '~icons/lucide/chevron-down';

defineProps<{
  title: string;
  fullName: string;
  formattedValue: string;
  status: string;
  threshold: string;
  description: string;
  isExpanded: boolean;
}>();

defineEmits<{
  'toggle-info': [];
}>();
</script>

