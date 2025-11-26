<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Аудит сайта</h1>
      <p class="dashboard-subtitle">
        Введите URL сайта для анализа производительности
      </p>
      <p class="dashboard-subtitle">
        Примеры сайтов для анализа:
        <ul>
          <li>https://www.google.com</li>
          <li>http://testphp.vulnweb.com</li>
        </ul>
      </p>
    </div>

    <UrlInputSection
      v-model="websiteUrl"
      :is-loading="isLoading"
      @analyze="analyzeWebsite"
    />

    <div class="dashboard-results" :class="{ hidden: !isAuditReady }">
      <ScoresSection
        :performance-score="performanceScoreDisplay"
        :accessibility-score="accessibilityScoreDisplay"
        :best-practices-score="bestPracticesScoreDisplay"
        :seo-score="seoScoreDisplay"
        :descriptions="descriptions"
        :is-expanded="(key) => expandedInfo.has(key)"
        @toggle="toggleInfo"
      />

      <CoreWebVitalsSection
        :lcp="lcp"
        :fid="fid"
        :cls="cls"
        :descriptions="descriptions"
        :is-expanded="(key) => expandedInfo.has(key)"
        @toggle="toggleInfo"
      />
    </div>

    <SecuritySection
      :security-audit="securityAudit" 
      :security-error="securityError || ''"
      :is-security-ready="isSecurityReady"
      :descriptions="descriptions"
      :is-expanded="(key) => expandedInfo.has(key)"
      @toggle="toggleInfo"
    />

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAuditStore } from "../stores/audit";
import { useAuditDescriptions } from "../composables/useAuditDescriptions";
import { useToggle } from "@/shared/composables/useToggle";
import UrlInputSection from "../components/UrlInputSection.vue";
import ScoresSection from "../components/ScoresSection.vue";
import CoreWebVitalsSection from "../components/CoreWebVitalsSection.vue";
import SecuritySection from "../components/SecuritySection.vue";

const auditStore = useAuditStore();
const descriptions = useAuditDescriptions();
const { expandedItems: expandedInfo, toggle: toggleInfo } = useToggle();

const websiteUrl = ref("");
const isLoading = computed(() => auditStore.isLighthouseLoading || auditStore.isSecurityLoading);
const error = computed(() => auditStore.error);

const performanceScore = computed(() => auditStore.performanceScore);
const accessibilityScore = computed(() => auditStore.accessibilityScore);
const bestPracticesScore = computed(() => auditStore.bestPracticesScore);
const seoScore = computed(() => auditStore.seoScore);

const performanceScoreDisplay = computed(() => auditStore.performanceScoreDisplay);
const accessibilityScoreDisplay = computed(() => auditStore.accessibilityScoreDisplay);
const bestPracticesScoreDisplay = computed(() => auditStore.bestPracticesScoreDisplay);
const seoScoreDisplay = computed(() => auditStore.seoScoreDisplay);

const lcp = computed(() => auditStore.lcp);
const fid = computed(() => auditStore.fid);
const cls = computed(() => auditStore.cls);

const securityAudit = computed(() => auditStore.securityAudit);
const securityError = computed(() => auditStore.error);

const analyzeWebsite = async () => {
  const lighthousePromise = auditStore
    .analyzeWebsite(websiteUrl.value)
    .catch((err) => {
      console.error("Lighthouse error:", err);
    });

  const securityPromise = auditStore
    .fetchSecurityAudit(websiteUrl.value)
    .catch((err) => {
      console.error("Security audit error:", err);
    });

  await Promise.all([lighthousePromise, securityPromise]);
};

const isAuditReady = computed(() => {
  return [
    performanceScore.value,
    accessibilityScore.value,
    bestPracticesScore.value,
    seoScore.value,
  ].some((v) => v !== null && typeof v === "number");
});

const isSecurityReady = computed(() => {
  return securityAudit.value !== null || auditStore.isSecurityLoading;
});
</script>
