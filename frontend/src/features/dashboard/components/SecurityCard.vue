<template>
  <div class="security-card">
    <div class="score-header">
      <div class="score-icon security">
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
    <div class="security-items">
      <slot></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IconButton } from "@/shared/ui/atoms";
import IconLucideChevronDown from "~icons/lucide/chevron-down";

defineProps<{
  title: string;
  description: string;
  isExpanded: boolean;
}>();

defineEmits<{
  "toggle-info": [];
}>();
</script>
