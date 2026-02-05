<template>
  <div class="analytics-stats">
    <div class="stat-card modern">
      <div class="stat-header">
        <div class="stat-icon-wrapper blue">
          <IconLucideBarChart2 />
        </div>
        <span class="stat-label">Всего проверок</span>
      </div>
      <div class="stat-value-large">{{ totalAudits }}</div>
    </div>
    
    <div class="stat-card modern">
      <div class="stat-header">
        <div class="stat-icon-wrapper green">
          <IconLucideCalendar />
        </div>
        <span class="stat-label">Первая проверка</span>
      </div>
      <div class="stat-value-large">{{ firstAudit }}</div>
    </div>
    
    <div class="stat-card modern">
      <div class="stat-header">
        <div class="stat-icon-wrapper purple">
          <IconLucideClock />
        </div>
        <span class="stat-label">Последняя проверка</span>
      </div>
      <div class="stat-value-large">{{ lastAudit }}</div>
    </div>
    
    <div class="stat-card modern featured">
      <div class="stat-header">
        <div class="stat-icon-wrapper orange">
          <IconLucideTrendingUp />
        </div>
        <span class="stat-label">Средний балл</span>
      </div>
      <div class="stat-value-large">{{ averageScore }}</div>
      <div class="stat-trend">
        <IconLucideArrowUp v-if="trendDirection === 'up'" class="trend-up" />
        <IconLucideArrowDown v-else-if="trendDirection === 'down'" class="trend-down" />
        <IconLucideMinus v-else class="trend-neutral" />
        <span>{{ trendText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import IconLucideBarChart2 from '~icons/lucide/bar-chart-2';
import IconLucideCalendar from '~icons/lucide/calendar';
import IconLucideClock from '~icons/lucide/clock';
import IconLucideTrendingUp from '~icons/lucide/trending-up';
import IconLucideArrowUp from '~icons/lucide/arrow-up';
import IconLucideArrowDown from '~icons/lucide/arrow-down';
import IconLucideMinus from '~icons/lucide/minus';

interface Props {
  totalAudits: number;
  firstAudit: string;
  lastAudit: string;
  averageScore: number;
  trendDirection: 'up' | 'down' | 'neutral';
  trendText: string;
}

defineProps<Props>();
</script>

<style scoped>
.analytics-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card.modern {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card.modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent, rgba(100, 108, 255, 0.5), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card.modern:hover::before {
  opacity: 1;
}

.stat-card.modern:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
  border-color: rgba(100, 108, 255, 0.3);
}

.stat-card.modern.featured {
  background: linear-gradient(135deg, rgba(100, 108, 255, 0.12) 0%, rgba(156, 39, 176, 0.12) 100%);
  border-color: rgba(100, 108, 255, 0.3);
}

.stat-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-icon-wrapper {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  flex-shrink: 0;
}

.stat-icon-wrapper svg {
  width: 20px;
  height: 20px;
}

.stat-icon-wrapper.blue {
  background: rgba(33, 150, 243, 0.15);
  color: #2196f3;
}

.stat-icon-wrapper.green {
  background: rgba(76, 175, 80, 0.15);
  color: #4caf50;
}

.stat-icon-wrapper.purple {
  background: rgba(156, 39, 176, 0.15);
  color: #9c27b0;
}

.stat-icon-wrapper.orange {
  background: rgba(255, 152, 0, 0.15);
  color: #ff9800;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.stat-value-large {
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.5px;
  line-height: 1;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  margin-top: 0.5rem;
}

.stat-trend svg {
  width: 16px;
  height: 16px;
}

.trend-up {
  color: #4caf50;
}

.trend-down {
  color: #f44336;
}

.trend-neutral {
  color: rgba(255, 255, 255, 0.5);
}

.stat-trend span {
  color: rgba(255, 255, 255, 0.7);
}

@media (max-width: 768px) {
  .analytics-stats {
    grid-template-columns: 1fr;
  }

  .stat-value-large {
    font-size: 1.75rem;
  }
}
</style>
