<template>
  <header class="audit-detail-header">
    <Button variant="secondary" size="md" @click="handleBack">
      <template #icon-left>
        <IconLucideArrowLeft />
      </template>
      {{ backLabel }}
    </Button>
    <h1 class="audit-detail-title">{{ title }}</h1>
  </header>
</template>

<script setup lang="ts">
import { Button } from "@/shared/ui/atoms";
import IconLucideArrowLeft from "~icons/lucide/arrow-left";

withDefaults(
  defineProps<{
    title?: string;
    backLabel?: string;
  }>(),
  {
    title: "Детали аудита",
    backLabel: "Назад к истории",
  }
);

const emit = defineEmits<{
  back: [];
}>();

const handleBack = () => {
  emit("back");
};
</script>

<style scoped>
.audit-detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.audit-detail-title {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
}

@media (max-width: 768px) {
  .audit-detail-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

