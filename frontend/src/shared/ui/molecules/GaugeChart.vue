<template>
  <div class="gauge-chart-container">
    <div class="gauge-wrapper">
      <Doughnut :data="chartData" :options="chartOptions" />
      <div class="gauge-center">
        <div class="gauge-value">{{ formattedValue }}</div>
        <div class="gauge-label">{{ metricName }}</div>
      </div>
    </div>
    <div class="gauge-status" :class="statusClass">
      {{ statusText }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  type ChartData,
  type ChartOptions,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip);

interface GaugeChartProps {
  metricName: string;
  value: number | null;
  goodThreshold: number;
  poorThreshold: number;
  unit?: string;
  // eslint-disable-next-line no-unused-vars
  formatter?: (val: number) => string;
}

const props = withDefaults(defineProps<GaugeChartProps>(), {
  unit: '',
  formatter: (val: number) => val.toString(),
});

// Определяем статус метрики
const status = computed(() => {
  if (props.value === null) return 'unknown';
  if (props.value <= props.goodThreshold) return 'good';
  if (props.value <= props.poorThreshold) return 'needs-improvement';
  return 'poor';
});

const statusClass = computed(() => `status-${status.value}`);

const statusText = computed(() => {
  const statusMap = {
    good: 'Good',
    'needs-improvement': 'Needs Improvement',
    poor: 'Poor',
    unknown: 'N/A',
  };
  return statusMap[status.value as keyof typeof statusMap];
});

const formattedValue = computed(() => {
  if (props.value === null) return 'N/A';
  return props.formatter(props.value) + props.unit;
});

const percentage = computed(() => {
  if (props.value === null) return 0;
  
  const maxValue = props.poorThreshold * 1.5;
  const normalized = Math.min((props.value / maxValue) * 100, 100);
  
  return 100 - normalized; // Инвертируем, чтобы хорошие значения были справа
});

const chartData = computed<ChartData<'doughnut'>>(() => {
  const colors = {
    good: '#0cce6b',
    'needs-improvement': '#ffa400',
    poor: '#ff4e42',
    unknown: '#666',
  };
  
  const currentColor = colors[status.value as keyof typeof colors];
  const grayColor = 'rgba(255, 255, 255, 0.1)';
  
  return {
    labels: ['Score', 'Remaining'],
    datasets: [
      {
        data: [percentage.value, 100 - percentage.value],
        backgroundColor: [currentColor, grayColor],
        borderWidth: 0,
        circumference: 180,
        rotation: 270,
      },
    ],
  };
});

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: true,
  cutout: '75%',
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      enabled: false,
    },
  },
}));
</script>

<style scoped>
.gauge-chart-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
}

.gauge-wrapper {
  position: relative;
  width: 200px;
  height: 130px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gauge-wrapper :deep(canvas) {
  margin: 0 auto;
  transform: translateY(-16px);
}

.gauge-center {
  position: absolute;
  top: 55%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}

.gauge-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.95);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.gauge-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.gauge-status {
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.375rem 0.75rem;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.gauge-status.status-good {
  background-color: rgba(12, 206, 107, 0.15);
  color: #0cce6b;
}

.gauge-status.status-needs-improvement {
  background-color: rgba(255, 164, 0, 0.15);
  color: #ffa400;
}

.gauge-status.status-poor {
  background-color: rgba(255, 78, 66, 0.15);
  color: #ff4e42;
}

.gauge-status.status-unknown {
  background-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.5);
}

@media (max-width: 768px) {
  .gauge-wrapper {
    width: 150px;
    height: 100px;
  }
  
  .gauge-value {
    font-size: 1.25rem;
  }
  
  .gauge-label {
    font-size: 0.75rem;
  }
}
</style>

