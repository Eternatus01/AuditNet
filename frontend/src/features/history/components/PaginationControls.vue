<template>
  <div v-if="pagination && pagination.last_page > 1" class="pagination">
    <button
      class="pagination-btn"
      :class="{ disabled: pagination.current_page === 1 }"
      :disabled="pagination.current_page === 1"
      @click="handleFirst"
      title="Первая страница"
    >
      <IconLucideChevronsLeft />
    </button>

    <button
      class="pagination-btn"
      :class="{ disabled: pagination.current_page === 1 }"
      :disabled="pagination.current_page === 1"
      @click="handlePrevious"
      title="Предыдущая"
    >
      <IconLucideChevronLeft />
    </button>

    <div class="page-numbers">
      <button
        v-for="page in visiblePages"
        :key="page"
        class="page-number"
        :class="{ active: page === pagination.current_page }"
        @click="handlePageClick(page)"
      >
        {{ page }}
      </button>
    </div>

    <button
      class="pagination-btn"
      :class="{ disabled: pagination.current_page === pagination.last_page }"
      :disabled="pagination.current_page === pagination.last_page"
      @click="handleNext"
      title="Следующая"
    >
      <IconLucideChevronRight />
    </button>

    <button
      class="pagination-btn"
      :class="{ disabled: pagination.current_page === pagination.last_page }"
      :disabled="pagination.current_page === pagination.last_page"
      @click="handleLast"
      title="Последняя страница"
    >
      <IconLucideChevronsRight />
    </button>

    <div class="page-info">
      <span>Всего: {{ pagination.total }} записей</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { PaginationMeta } from "../types";
import IconLucideChevronRight from "~icons/lucide/chevron-right";
import IconLucideChevronLeft from "~icons/lucide/chevron-left";
import IconLucideChevronsRight from "~icons/lucide/chevrons-right";
import IconLucideChevronsLeft from "~icons/lucide/chevrons-left";

interface Props {
  pagination: PaginationMeta | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  "page-change": [page: number];
}>();

const visiblePages = computed(() => {
  if (!props.pagination) return [];
  
  const { current_page, last_page } = props.pagination;
  const pages: number[] = [];
  const maxVisible = 5;
  
  if (last_page <= maxVisible) {
    for (let i = 1; i <= last_page; i++) {
      pages.push(i);
    }
  } else {
    const start = Math.max(1, current_page - 2);
    const end = Math.min(last_page, current_page + 2);
    
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
  }
  
  return pages;
});

const handleFirst = () => {
  emit("page-change", 1);
};

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

const handleLast = () => {
  if (props.pagination) {
    emit("page-change", props.pagination.last_page);
  }
};

const handlePageClick = (page: number) => {
  emit("page-change", page);
};
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 2rem;
  padding: 1.5rem;
  flex-wrap: wrap;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(.disabled) {
  background: rgba(100, 108, 255, 0.15);
  border-color: rgba(100, 108, 255, 0.3);
  color: #646cff;
  transform: translateY(-2px);
}

.pagination-btn.disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.pagination-btn svg {
  width: 20px;
  height: 20px;
}

.page-numbers {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin: 0 0.5rem;
}

.page-number {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  padding: 0 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.page-number:hover {
  background: rgba(100, 108, 255, 0.15);
  border-color: rgba(100, 108, 255, 0.3);
  color: #646cff;
  transform: translateY(-2px);
}

.page-number.active {
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  border-color: #646cff;
  color: #fff;
  box-shadow: 0 4px 12px rgba(100, 108, 255, 0.4);
}

.page-info {
  margin-left: 1rem;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.875rem;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .pagination {
    gap: 0.375rem;
    padding: 1rem;
  }

  .pagination-btn,
  .page-number {
    width: 36px;
    height: 36px;
    min-width: 36px;
  }

  .page-numbers {
    margin: 0 0.25rem;
  }

  .page-info {
    width: 100%;
    text-align: center;
    margin: 0.5rem 0 0 0;
  }
}
</style>

