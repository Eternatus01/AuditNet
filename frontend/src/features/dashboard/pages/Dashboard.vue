<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Аудит сайта</h1>
      <p class="dashboard-subtitle">Введите URL сайта для анализа производительности</p>
    </div>

    <div class="url-input-section">
      <div class="url-input-wrapper">
        <svg class="url-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
          <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
        </svg>
        <input
          type="url"
          class="url-input"
          v-model="websiteUrl"
          placeholder="https://example.com"
          autocomplete="url"
        />
        <button class="analyze-btn" type="button">
          <span>Анализировать</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="5 12 19 12"></polyline>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>
    </div>

    <div class="scores-grid">
      <div class="score-card">
        <div class="score-header">
          <div class="score-icon performance">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
          </div>
          <h3>Performance</h3>
        </div>
        <div class="score-value">
          <span class="score-number">--</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div class="score-progress-bar" style="width: 0%"></div>
        </div>
      </div>

      <div class="score-card">
        <div class="score-header">
          <div class="score-icon accessibility">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <h3>Accessibility</h3>
        </div>
        <div class="score-value">
          <span class="score-number">--</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div class="score-progress-bar" style="width: 0%"></div>
        </div>
      </div>

      <div class="score-card">
        <div class="score-header">
          <div class="score-icon best-practices">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <h3>Best Practices</h3>
        </div>
        <div class="score-value">
          <span class="score-number">--</span>
          <span class="score-label">/ 100</span>
        </div>
        <div class="score-progress">
          <div class="score-progress-bar" style="width: 0%"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const websiteUrl = ref('')

</script>

