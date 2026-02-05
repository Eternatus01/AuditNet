<template>
  <div class="metric-chart-card">
    <div class="chart-header">
      <h3>{{ title }}</h3>
      <div class="chart-legend">
        <div class="legend-item" v-for="metric in visibleMetrics" :key="metric.key">
          <span class="legend-color" :style="{ backgroundColor: metric.color }"></span>
          <span class="legend-label">{{ metric.label }}</span>
        </div>
      </div>
    </div>
    <div v-if="!data || data.length === 0" class="chart-empty">
      <p>Нет данных для отображения</p>
    </div>
    <div v-else class="chart-container">
      <canvas ref="chartRef"></canvas>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import {
  Chart,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  LineController,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import type { AnalyticsDataPoint } from '../types';

Chart.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  LineController,
  Title,
  Tooltip,
  Legend,
  Filler
);

interface Props {
  title: string;
  data: AnalyticsDataPoint[];
  metrics: {
    key: keyof AnalyticsDataPoint;
    label: string;
    color: string;
  }[];
}

const props = defineProps<Props>();

const chartRef = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const visibleMetrics = ref(props.metrics);

const createChart = () => {
  if (!chartRef.value || !props.data || props.data.length === 0) return;

  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartRef.value.getContext('2d');
  if (!ctx) return;

  // Разворачиваем данные, чтобы показать от старых к новым
  const reversedData = [...props.data].reverse();

  const labels = reversedData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString('ru-RU', { 
      day: '2-digit', 
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    });
  });

  const datasets = visibleMetrics.value.map(metric => ({
    label: metric.label,
    data: reversedData.map(d => {
      const value = d[metric.key];
      return value !== null && value !== undefined ? value : null;
    }),
    borderColor: metric.color,
    backgroundColor: `${metric.color}20`,
    borderWidth: 3,
    tension: 0.4,
    fill: true,
    pointRadius: 5,
    pointHoverRadius: 7,
    pointBackgroundColor: metric.color,
    pointBorderColor: '#1a1a2e',
    pointBorderWidth: 2,
    spanGaps: true, // Пропускаем null значения
  }));

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets,
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: 'rgba(26, 26, 46, 0.95)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: 'rgba(100, 108, 255, 0.3)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 8,
          displayColors: true,
          callbacks: {
            label: (context) => {
              const label = context.dataset.label || '';
              const value = context.parsed.y;
              return `${label}: ${value !== null ? value.toFixed(0) : 'N/A'}`;
            },
          },
        },
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(255, 255, 255, 0.05)',
            drawBorder: false,
          },
          ticks: {
            color: 'rgba(255, 255, 255, 0.6)',
            font: {
              size: 11,
            },
            maxRotation: 45,
            minRotation: 45,
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(255, 255, 255, 0.05)',
            drawBorder: false,
          },
          ticks: {
            color: 'rgba(255, 255, 255, 0.6)',
            font: {
              size: 12,
            },
            callback: function(value) {
              return value.toLocaleString();
            }
          },
        },
      },
    },
  });
};

onMounted(() => {
  createChart();
});

watch(() => props.data, () => {
  createChart();
}, { deep: true });

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>

<style scoped>
.metric-chart-card {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.9) 0%, rgba(30, 30, 50, 0.9) 100%);
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid rgba(100, 108, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.chart-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
}

.chart-legend {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.legend-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
}

.chart-container {
  height: 400px;
  position: relative;
}

.chart-empty {
  height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1rem;
}

@media (max-width: 768px) {
  .metric-chart-card {
    padding: 1.5rem;
  }

  .chart-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .chart-container,
  .chart-empty {
    height: 300px;
  }
}
</style>
