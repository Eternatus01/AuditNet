<template>
  <div class="bar-chart-container">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  type ChartData,
  type ChartOptions,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface BarChartProps {
  performanceScore: number | string;
  accessibilityScore: number | string;
  bestPracticesScore: number | string;
  seoScore: number | string;
  orientation?: 'horizontal' | 'vertical';
}

const props = withDefaults(defineProps<BarChartProps>(), {
  orientation: 'horizontal',
});

const getNumericValue = (value: number | string): number => {
  if (typeof value === 'number') return value;
  if (value === '--' || value === 'N/A') return 0;
  return parseFloat(value) || 0;
};

// Функция для определения цвета по значению
const getColorByScore = (score: number) => {
  if (score >= 90) return { bg: 'rgba(16, 185, 129, 0.8)', border: 'rgba(16, 185, 129, 1)' };
  if (score >= 50) return { bg: 'rgba(245, 158, 11, 0.8)', border: 'rgba(245, 158, 11, 1)' };
  return { bg: 'rgba(239, 68, 68, 0.8)', border: 'rgba(239, 68, 68, 1)' };
};

const chartData = computed<ChartData<'bar'>>(() => {
  const scores = [
    { label: 'Performance', value: getNumericValue(props.performanceScore) },
    { label: 'Accessibility', value: getNumericValue(props.accessibilityScore) },
    { label: 'Best Practices', value: getNumericValue(props.bestPracticesScore) },
    { label: 'SEO', value: getNumericValue(props.seoScore) },
  ];

  return {
    labels: scores.map(s => s.label),
    datasets: [
      {
        label: 'Score',
        data: scores.map(s => s.value),
        backgroundColor: scores.map(s => getColorByScore(s.value).bg),
        borderColor: scores.map(s => getColorByScore(s.value).border),
        borderWidth: 2,
        borderRadius: 12,
        barThickness: props.orientation === 'vertical' ? 80 : 60,
        barPercentage: 0.9,
        categoryPercentage: 0.8,
      },
    ],
  };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: props.orientation === 'horizontal' ? 'y' : 'x',
  scales: {
    x: {
      beginAtZero: true,
      max: props.orientation === 'horizontal' ? 100 : undefined,
      grid: {
        color: 'rgba(255, 255, 255, 0.08)',
        lineWidth: 1,
        display: props.orientation === 'vertical',
      },
      ticks: {
        color: 'rgba(255, 255, 255, 0.7)',
        font: {
          size: props.orientation === 'vertical' ? 14 : 12,
          weight: '500',
        },
        stepSize: 25,
        callback: function(value) {
          return value;
        },
        display: props.orientation === 'horizontal',
      },
      border: {
        display: false,
      },
    },
    y: {
      beginAtZero: true,
      max: props.orientation === 'vertical' ? 100 : undefined,
      grid: {
        color: props.orientation === 'vertical' ? 'rgba(255, 255, 255, 0.08)' : 'transparent',
        lineWidth: 1,
        display: props.orientation === 'vertical',
      },
      ticks: {
        color: 'rgba(255, 255, 255, 0.95)',
        font: {
          size: props.orientation === 'vertical' ? 12 : 14,
          weight: '700',
        },
        padding: 12,
      },
      border: {
        display: false,
      },
    },
  },
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.9)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(124, 58, 237, 0.8)',
      borderWidth: 2,
      padding: 16,
      displayColors: false,
      titleFont: {
        size: 14,
        weight: '700',
      },
      bodyFont: {
        size: 16,
        weight: '600',
      },
      callbacks: {
        label: (context) => {
          const value = props.orientation === 'horizontal' ? context.parsed.x : context.parsed.y;
          return `${value}/100`;
        },
      },
    },
  },
}));
</script>

<style scoped>
.bar-chart-container {
  width: 100%;
  height: 100%;
  min-height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 968px) {
  .bar-chart-container {
    min-height: 240px;
  }
}

@media (max-width: 768px) {
  .bar-chart-container {
    min-height: 220px;
  }
}
</style>

