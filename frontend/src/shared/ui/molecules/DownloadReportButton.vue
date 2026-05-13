<template>
  <div class="download-report-wrapper">
    <Button
      class="download-report-button"
      variant="primary"
      size="md"
      :loading="isGenerating"
      :disabled="disabled"
      show-text-while-loading
      @click="handleDownload"
    >
      <template #icon-left>
        <IconLucideDownload />
      </template>
      {{ isGenerating ? "Формируем PDF..." : "Скачать отчёт" }}
    </Button>

    <p v-if="errorMessage" class="download-report-error">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { Button } from "@/shared/ui/atoms";
import IconLucideDownload from "~icons/lucide/download";
import { logger } from "@/shared/utils/logger";
import { generateAuditPdf } from "@/features/dashboard/utils/pdf";
import type { AuditReportData } from "@/features/dashboard/utils/pdf";

const props = withDefaults(
  defineProps<{
    data: AuditReportData;
    disabled?: boolean;
  }>(),
  { disabled: false }
);

const isGenerating = ref(false);
const errorMessage = ref("");

const handleDownload = async (): Promise<void> => {
  if (isGenerating.value || props.disabled) return;

  isGenerating.value = true;
  errorMessage.value = "";

  try {
    await generateAuditPdf(props.data);
  } catch (e: unknown) {
    logger.error("PDF generation failed:", e);
    errorMessage.value =
      e instanceof Error
        ? `Не удалось сформировать PDF: ${e.message}`
        : "Не удалось сформировать PDF. Попробуйте ещё раз.";
  } finally {
    isGenerating.value = false;
  }
};
</script>

<style scoped>
.download-report-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.download-report-wrapper :deep(.download-report-button) {
  min-height: 46px;
  padding: 0.6875rem 1.625rem;
}

.download-report-error {
  margin: 0;
  font-size: 0.8125rem;
  color: #ef4444;
}

@media (max-width: 768px) {
  .download-report-wrapper {
    align-items: stretch;
  }
}
</style>
