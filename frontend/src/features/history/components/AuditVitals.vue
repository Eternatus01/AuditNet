<template>
  <div class="audit-vitals">
    <div class="vital-item">
      <span class="vital-label">LCP</span>
      <span class="vital-value">{{ formatLCP(audit.lcp) }}</span>
    </div>
    <div class="vital-item">
      <span class="vital-label">FID</span>
      <span class="vital-value">{{ formatFID(audit.fid) }}</span>
    </div>
    <div class="vital-item">
      <span class="vital-label">CLS</span>
      <span class="vital-value">{{ formatCLS(audit.cls) }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { formatLCP, formatFID, formatCLS } from "@/shared/utils/formatters";
import type { Audit } from "../types";

interface Props {
  audit: Audit;
}

defineProps<Props>();
</script>

<style scoped>
.audit-vitals {
  display: flex;
  gap: 1.5rem;
  padding: 0.75rem;
  background-color: rgba(100, 108, 255, 0.05);
  border-radius: 8px;
}

.vital-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.vital-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.vital-value {
  font-size: 1rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
}
</style>

