<template>
  <div v-if="pagination && pagination.last_page > 1" class="pagination">
    <Button
      variant="secondary"
      size="md"
      :disabled="pagination.current_page === 1"
      @click="handlePrevious"
    >
      <template #icon-left>
        <IconLucideChevronLeft />
      </template>
      Назад
    </Button>
    <span class="page-info">
      Страница {{ pagination.current_page }} из {{ pagination.last_page }}
    </span>
    <Button
      variant="secondary"
      size="md"
      :disabled="pagination.current_page === pagination.last_page"
      @click="handleNext"
    >
      Вперёд
      <template #icon-right>
        <IconLucideChevronRight />
      </template>
    </Button>
  </div>
</template>

<script setup lang="ts">
import { Button } from "@/shared/ui/atoms";
import type { PaginationMeta } from "../types";
import IconLucideChevronRight from "~icons/lucide/chevron-right";
import IconLucideChevronLeft from "~icons/lucide/chevron-left";

interface Props {
  pagination: PaginationMeta | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  "page-change": [page: number];
}>();

const handlePrevious = () => {
  if (props.pagination && props.pagination.current_page > 1) {
    emit("page-change", props.pagination.current_page - 1);
  }
};

const handleNext = () => {
  if (props.pagination && props.pagination.current_page < props.pagination.last_page) {
    emit("page-change", props.pagination.current_page + 1);
  }
};
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  margin-top: 2rem;
  padding: 1.5rem;
}

.page-info {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.95rem;
}
</style>

