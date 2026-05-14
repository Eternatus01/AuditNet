<template>
  <div class="download-comparison-wrapper">
    <Button
      class="download-comparison-button"
      variant="primary"
      size="md"
      :loading="isGenerating"
      show-text-while-loading
      @click="handleDownload"
    >
      <template #icon-left>
        <IconLucideDownload />
      </template>
      {{ isGenerating ? "Формируем PDF..." : "Скачать отчёт" }}
    </Button>

    <p v-if="errorMessage" class="download-comparison-error">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { Button } from "@/shared/ui/atoms";
import IconLucideDownload from "~icons/lucide/download";
import { logger } from "@/shared/utils/logger";
import type { AuditComparison } from "@/features/history/types";
import { generateComparisonPdf } from "../utils/pdf";

const props = defineProps<{
  comparison: AuditComparison;
}>();

const isGenerating = ref(false);
const errorMessage = ref("");

const handleDownload = async (): Promise<void> => {
  if (isGenerating.value) return;

  isGenerating.value = true;
  errorMessage.value = "";

  try {
    await generateComparisonPdf(props.comparison);
  } catch (e: unknown) {
    logger.error("Comparison PDF generation failed:", e);
    errorMessage.value = e instanceof Error ? `Не удалось сформировать PDF: ${e.message}` : "Не удалось сформировать PDF.";
  } finally {
    isGenerating.value = false;
  }
};
</script>

<style scoped>
.download-comparison-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.download-comparison-wrapper :deep(.download-comparison-button) {
  min-height: 46px;
  padding: 0.6875rem 1.625rem;
}

.download-comparison-error {
  margin: 0;
  color: #ef4444;
  font-size: 0.8125rem;
}

@media (max-width: 768px) {
  .download-comparison-wrapper {
    align-items: stretch;
  }
}
</style>
