<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Аудит сайта</h1>
      <p class="dashboard-subtitle">
        Введите URL сайта для анализа производительности
      </p>
    </div>

    <div class="url-input-section">
      <div class="url-input-wrapper">
        <svg
          class="url-icon"
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path
            d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"
          ></path>
          <path
            d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"
          ></path>
        </svg>
        <input
          type="url"
          class="url-input"
          v-model="websiteUrl"
          placeholder="https://example.com"
          autocomplete="url"
        />
        <button
          class="analyze-btn"
          type="button"
          @click="analyzeWebsite"
          :disabled="isLoading"
        >
          <span v-if="!isLoading">Анализировать</span>
          <span v-else>Анализ...</span>
          <svg
            v-if="!isLoading"
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="5 12 19 12"></polyline>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
          <svg
            v-else
            class="spinner"
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="12" y1="2" x2="12" y2="6"></line>
            <line x1="12" y1="18" x2="12" y2="22"></line>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
            <line x1="2" y1="12" x2="6" y2="12"></line>
            <line x1="18" y1="12" x2="22" y2="12"></line>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
          </svg>
        </button>
      </div>
    </div>

    <div class="scores-grid">
      <div class="score-card">
        <div class="score-header">
          <div class="score-icon performance">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
          </div>
          <h3>Performance</h3>
        </div>
        <div class="score-value">
          <span class="score-number">{{ performanceScore }}</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div
            class="score-progress-bar"
            :style="{ width: performanceScore + '%' }"
          ></div>
        </div>
      </div>

      <div class="score-card">
        <div class="score-header">
          <div class="score-icon accessibility">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <h3>Accessibility</h3>
        </div>
        <div class="score-value">
          <span class="score-number">{{ accessibilityScore }}</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div
            class="score-progress-bar"
            :style="{ width: accessibilityScore + '%' }"
          ></div>
        </div>
      </div>

      <div class="score-card">
        <div class="score-header">
          <div class="score-icon best-practices">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <h3>Best Practices</h3>
        </div>
        <div class="score-value">
          <span class="score-number">{{ bestPracticesScore }}</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div
            class="score-progress-bar"
            :style="{ width: bestPracticesScore + '%' }"
          ></div>
        </div>
      </div>
      <div class="score-card">
        <div class="score-header">
          <div class="score-icon best-practices">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <h3>SEO</h3>
        </div>
        <div class="score-value">
          <span class="score-number">{{ seoScore }}</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div
            class="score-progress-bar"
            :style="{ width: seoScore + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Core Web Vitals Section -->
    <div
      class="metrics-section"
      v-if="lcp !== null || fid !== null || cls !== null"
    >
      <h2 class="section-title">Core Web Vitals</h2>
      <p class="section-subtitle">
        Ключевые метрики производительности от Google
      </p>

      <div class="metrics-grid">
        <!-- LCP Card -->
        <div class="metric-card" :class="getMetricStatus('lcp', lcp)">
          <div class="metric-header">
            <div class="metric-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <h3>LCP</h3>
            <span class="metric-badge" :class="getMetricStatus('lcp', lcp)">{{
              getMetricStatus("lcp", lcp)
            }}</span>
          </div>
          <div class="metric-value">
            <span v-if="lcp !== null">{{ formatLCP(lcp) }}</span>
            <span v-else>--</span>
          </div>
          <p class="metric-description">Largest Contentful Paint</p>
          <p class="metric-threshold">Цель: &lt; 2.5s</p>
        </div>

        <!-- FID Card -->
        <div class="metric-card" :class="getMetricStatus('fid', fid)">
          <div class="metric-header">
            <div class="metric-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
              </svg>
            </div>
            <h3>FID</h3>
            <span class="metric-badge" :class="getMetricStatus('fid', fid)">{{
              getMetricStatus("fid", fid)
            }}</span>
          </div>
          <div class="metric-value">
            <span v-if="fid !== null">{{ formatFID(fid) }}</span>
            <span v-else>--</span>
          </div>
          <p class="metric-description">First Input Delay</p>
          <p class="metric-threshold">Цель: &lt; 100ms</p>
        </div>

        <!-- CLS Card -->
        <div class="metric-card" :class="getMetricStatus('cls', cls)">
          <div class="metric-header">
            <div class="metric-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="9" y1="9" x2="15" y2="9"></line>
                <line x1="9" y1="15" x2="15" y2="15"></line>
              </svg>
            </div>
            <h3>CLS</h3>
            <span class="metric-badge" :class="getMetricStatus('cls', cls)">{{
              getMetricStatus("cls", cls)
            }}</span>
          </div>
          <div class="metric-value">
            <span v-if="cls !== null">{{ formatCLS(cls) }}</span>
            <span v-else>--</span>
          </div>
          <p class="metric-description">Cumulative Layout Shift</p>
          <p class="metric-threshold">Цель: &lt; 0.1</p>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAuditStore } from "../stores/audit";

const websiteUrl = ref("");
const isLoading = computed(() => auditStore.isLoading);
const error = computed(() => auditStore.error);
const auditStore = useAuditStore();

const performanceScore = computed(() => auditStore.performanceScore);
const accessibilityScore = computed(() => auditStore.accessibilityScore);
const bestPracticesScore = computed(() => auditStore.bestPracticesScore);
const seoScore = computed(() => auditStore.seoScore);

// Core Web Vitals
const lcp = computed(() => auditStore.lcp);
const fid = computed(() => auditStore.fid);
const cls = computed(() => auditStore.cls);

const analyzeWebsite = async () => {
  try {
    await auditStore.analyzeWebsite(websiteUrl.value);
  } catch (error) {}
};

// Функции для форматирования значений
const formatLCP = (value: number | null): string => {
  if (value === null) return "--";
  return `${value.toFixed(2)} s`;
};

const formatFID = (value: number | null): string => {
  if (value === null) return "--";
  return `${Math.round(value)} ms`;
};

const formatCLS = (value: number | null): string => {
  if (value === null) return "--";
  return value.toFixed(3);
};

// Функция для определения статуса метрики (good, needs-improvement, poor)
const getMetricStatus = (metric: string, value: number | null): string => {
  if (value === null) return "unknown";

  switch (metric) {
    case "lcp":
      // LCP: < 2.5s = good, 2.5-4s = needs-improvement, > 4s = poor
      if (value < 2.5) return "good";
      if (value <= 4.0) return "needs-improvement";
      return "poor";

    case "fid":
      // FID: < 100ms = good, 100-300ms = needs-improvement, > 300ms = poor
      if (value < 100) return "good";
      if (value <= 300) return "needs-improvement";
      return "poor";

    case "cls":
      // CLS: < 0.1 = good, 0.1-0.25 = needs-improvement, > 0.25 = poor
      if (value < 0.1) return "good";
      if (value <= 0.25) return "needs-improvement";
      return "poor";

    default:
      return "unknown";
  }
};
</script>