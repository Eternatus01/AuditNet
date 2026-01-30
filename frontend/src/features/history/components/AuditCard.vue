<template>
  <div class="audit-card" @click="handleClick">
    <div class="audit-main">
      <div class="audit-url">
        <IconLucideGlobe />
        <span>{{ audit.url }}</span>
      </div>
      <div class="audit-date">
        <IconLucideClock />
        <span>{{ formattedDate }}</span>
      </div>
    </div>

    <AuditScores :audit="audit" :get-score-class="getScoreClass" />

    <AuditVitals :audit="audit" />
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { Audit, ScoreClass } from "../types";
import AuditScores from "./AuditScores.vue";
import AuditVitals from "./AuditVitals.vue";
import IconLucideGlobe from "~icons/lucide/globe";
import IconLucideClock from "~icons/lucide/clock";

interface Props {
  audit: Audit;
  formatDate: (date: string) => string;
  getScoreClass: (score: number | null) => ScoreClass;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  click: [id: number];
}>();

const formattedDate = computed(() => props.formatDate(props.audit.created_at));

const handleClick = () => {
  emit("click", props.audit.id);
};
</script>

<style scoped>
.audit-card {
  background-color: var(--sidebar-bg);
  border: 1px solid #333;
  border-radius: 12px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-width: 0;
  max-width: 100%;
}

.audit-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  border-color: var(--primary-color);
}

.audit-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.audit-url,
.audit-date {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.8);
}

.audit-url svg,
.audit-date svg {
  width: 18px;
  height: 18px;
  color: var(--primary-color);
  flex-shrink: 0;
}

.audit-url {
  font-size: 1.1rem;
  font-weight: 500;
  min-width: 0;
  flex: 1;
}

.audit-url span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.audit-date {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.5);
}
</style>

