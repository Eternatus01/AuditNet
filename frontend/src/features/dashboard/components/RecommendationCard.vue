<template>
  <div class="recommendation-card" :class="`severity-${severity}`">
    <div class="recommendation-header">
      <div class="recommendation-icon" :class="`severity-${severity}`">
        <IconLucideAlertCircle v-if="severity === 'critical'" />
        <IconLucideAlertTriangle v-else-if="severity === 'warning'" />
        <IconLucideInfo v-else />
      </div>
      <div class="recommendation-title-group">
        <h3 class="recommendation-title">{{ translatedTitle }}</h3>
        <span class="recommendation-category">{{ categoryLabel }}</span>
      </div>
      <div class="recommendation-score">
        <span class="score-value">{{ scoreValue }}</span>
        <span class="score-label">/100</span>
      </div>
    </div>

    <div class="recommendation-solution">
      <IconLucideCheckCircle />
      <span>{{ translatedSolution }}</span>
    </div>

    <div v-if="displayValue" class="recommendation-impact">
      <IconLucideTrendingUp />
      <span>{{ displayValue }}</span>
    </div>

    <div v-if="formattedDetails.length > 0" class="recommendation-details">
      <div class="details-content">
        <div
          v-for="(item, idx) in formattedDetails.slice(0, 5)"
          :key="idx"
          class="detail-item"
        >
          <div class="detail-main">
            <span class="detail-label">{{ item.label }}</span>
            <span v-if="item.value" class="detail-value">{{ item.value }}</span>
          </div>
          <div v-if="item.savings" class="detail-savings">
            Экономия: {{ item.savings }}
          </div>
        </div>
        <button 
          v-if="formattedDetails.length > 5"
          class="show-more-btn"
          @click="toggleExpanded"
        >
          <template v-if="!isExpanded">
            Показать ещё {{ formattedDetails.length - 5 }}
            <IconLucideChevronDown />
          </template>
          <template v-else>
            Скрыть
            <IconLucideChevronDown class="rotated" />
          </template>
        </button>
      </div>
      
      <div v-if="isExpanded" class="details-content-extra">
        <div
          v-for="(item, idx) in formattedDetails.slice(5)"
          :key="`extra-${idx}`"
          class="detail-item"
        >
          <div class="detail-main">
            <span class="detail-label">{{ item.label }}</span>
            <span v-if="item.value" class="detail-value">{{ item.value }}</span>
          </div>
          <div v-if="item.savings" class="detail-savings">
            Экономия: {{ item.savings }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import type { AuditRecommendation } from '@/features/history/types';
import { getTranslation } from '../utils/recommendationsTranslations';
import IconLucideAlertCircle from '~icons/lucide/alert-circle';
import IconLucideAlertTriangle from '~icons/lucide/alert-triangle';
import IconLucideInfo from '~icons/lucide/info';
import IconLucideTrendingUp from '~icons/lucide/trending-up';
import IconLucideChevronDown from '~icons/lucide/chevron-down';
import IconLucideCheckCircle from '~icons/lucide/check-circle';

const props = defineProps<{
  recommendation: AuditRecommendation;
  categoryLabel: string;
}>();

const isExpanded = ref(false);

const severity = computed(() => {
  if (props.recommendation.score < 0.5) return 'critical';
  if (props.recommendation.score < 0.9) return 'warning';
  return 'info';
});

const scoreValue = computed(() => Math.round(props.recommendation.score * 100));

const translatedTitle = computed(() => 
  getTranslation(props.recommendation.audit_id_key, props.recommendation.title).title
);

const translatedSolution = computed(() => 
  getTranslation(props.recommendation.audit_id_key, props.recommendation.title).solution
);

const displayValue = computed(() => props.recommendation.display_value);

const formatBytes = (bytes: number): string => {
  if (bytes < 1024) return `${bytes}B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)}KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
};

const formattedDetails = computed(() => {
  if (!props.recommendation.details?.items) return [];
  
  return props.recommendation.details.items
    .filter((item: any) => {
      return item.url || item.wastedMs || item.wastedBytes || 
             item.source?.url || item.deprecatedAPIUsed || item.subItems;
    })
    .map((item: any) => {
      let label = '';
      let value = '';
      let savings = '';
      
      if (item.deprecatedAPIUsed) {
        label = item.deprecatedAPIUsed;
        if (item.source?.url) {
          const url = item.source.url;
          const fileName = url.split('/').pop() || url;
          value = fileName.length > 40 ? fileName.substring(0, 40) + '...' : fileName;
        }
      } else if (item.url) {
        const url = item.url;
        try {
          const urlObj = new URL(url);
          const pathParts = urlObj.pathname.split('/').filter((p: string) => p);
          label = pathParts.length > 0 ? pathParts[pathParts.length - 1] : urlObj.hostname;
          if (label.length > 60) {
            label = label.substring(0, 60) + '...';
          }
        } catch {
          label = url.substring(0, 60) + (url.length > 60 ? '...' : '');
        }
      } else if (item.node?.snippet) {
        label = item.node.snippet.replace(/<[^>]+>/g, '').substring(0, 80);
      } else {
        label = 'Элемент';
      }
      
      if (item.totalBytes) {
        value = formatBytes(item.totalBytes);
      }
      
      if (item.wastedBytes && item.wastedMs) {
        savings = `${formatBytes(item.wastedBytes)} / ${Math.round(item.wastedMs)}ms`;
      } else if (item.wastedBytes) {
        savings = formatBytes(item.wastedBytes);
      } else if (item.wastedMs) {
        savings = `${Math.round(item.wastedMs)}ms`;
      }
      
      return {
        label: label || 'Элемент',
        value: value || undefined,
        savings: savings || undefined
      };
    })
    .filter((item: any) => item.label !== 'Элемент' || item.value || item.savings);
});

const toggleExpanded = () => {
  isExpanded.value = !isExpanded.value;
};
</script>

<style scoped>
.recommendation-card {
  background: linear-gradient(135deg, var(--bg-secondary, #18181b) 0%, var(--bg-elevated, #1f1f23) 100%);
  border: 1px solid var(--border-color, #27272a);
  border-left-width: 4px;
  border-radius: var(--radius-xl, 24px);
  padding: 2rem;
  transition: all 0.3s;
}

.recommendation-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.recommendation-card.severity-critical {
  border-left-color: #ef4444;
}

.recommendation-card.severity-warning {
  border-left-color: #f59e0b;
}

.recommendation-card.severity-info {
  border-left-color: #3b82f6;
}

.recommendation-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.recommendation-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md, 12px);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.recommendation-icon.severity-critical {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.recommendation-icon.severity-warning {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.recommendation-icon.severity-info {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.recommendation-icon :deep(svg) {
  width: 20px;
  height: 20px;
}

.recommendation-title-group {
  flex: 1;
}

.recommendation-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.5rem 0;
  line-height: 1.4;
}

.recommendation-category {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary, rgba(255, 255, 255, 0.5));
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.recommendation-score {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
}

.score-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #fff;
}

.score-label {
  font-size: 0.875rem;
  color: var(--text-secondary, rgba(255, 255, 255, 0.5));
}

.recommendation-solution {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(99, 102, 241, 0.08) 100%);
  border: 1px solid rgba(124, 58, 237, 0.2);
  border-radius: var(--radius-md, 12px);
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9375rem;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.recommendation-solution :deep(svg) {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 0.125rem;
  color: var(--primary-color, #7c3aed);
}

.recommendation-impact {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(16, 185, 129, 0.1);
  border-radius: var(--radius-md, 12px);
  color: #10b981;
  font-size: 0.9375rem;
  font-weight: 500;
  margin-bottom: 1rem;
}

.recommendation-impact :deep(svg) {
  width: 16px;
  height: 16px;
}

.recommendation-details {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color, #27272a);
}

.details-content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.details-content-extra {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.show-more-btn {
  margin-top: 0.75rem;
  padding: 0.625rem 1rem;
  background: rgba(124, 58, 237, 0.1);
  border: 1px solid rgba(124, 58, 237, 0.2);
  border-radius: var(--radius-md, 12px);
  color: var(--primary-color, #7c3aed);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.show-more-btn:hover {
  background: rgba(124, 58, 237, 0.15);
  border-color: rgba(124, 58, 237, 0.3);
}

.show-more-btn :deep(svg) {
  width: 16px;
  height: 16px;
  transition: transform 0.3s;
}

.show-more-btn :deep(svg.rotated) {
  transform: rotate(180deg);
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 1.25rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: var(--radius-md, 12px);
  border-left: 2px solid rgba(124, 58, 237, 0.3);
  font-size: 0.875rem;
  transition: all 0.2s;
}

.detail-item:hover {
  background: rgba(255, 255, 255, 0.05);
  border-left-color: var(--primary-color, #7c3aed);
}

.detail-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.detail-label {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
  flex: 1;
  word-break: break-word;
  line-height: 1.4;
}

.detail-value {
  color: #f59e0b;
  font-weight: 600;
  white-space: nowrap;
}

.detail-savings {
  color: #10b981;
  font-size: 0.8125rem;
  font-weight: 600;
  padding: 0.375rem 0.75rem;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 6px;
  align-self: flex-start;
}

@media (max-width: 768px) {
  .recommendation-card {
    padding: 1.5rem;
  }

  .recommendation-header {
    flex-wrap: wrap;
  }

  .recommendation-score {
    width: 100%;
    justify-content: flex-end;
  }

  .detail-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .detail-label {
    margin-right: 0;
  }
}
</style>
