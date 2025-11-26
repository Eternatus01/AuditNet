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
}

const props = defineProps<BarChartProps>();

const getNumericValue = (value: number | string): number => {
  if (typeof value === 'number') return value;
  if (value === '--' || value === 'N/A') return 0;
  return parseFloat(value) || 0;
};

// Функция для определения цвета по значению
const getColorByScore = (score: number) => {
  if (score >= 90) return { bg: 'rgba(12, 206, 107, 0.8)', border: 'rgba(12, 206, 107, 1)' };
  if (score >= 50) return { bg: 'rgba(255, 164, 0, 0.8)', border: 'rgba(255, 164, 0, 1)' };
  return { bg: 'rgba(255, 78, 66, 0.8)', border: 'rgba(255, 78, 66, 1)' };
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
        borderRadius: 8,
        barThickness: 60,
      },
    ],
  };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  scales: {
    x: {
      beginAtZero: true,
      max: 100,
      grid: {
        color: 'rgba(255, 255, 255, 0.08)',
        lineWidth: 1,
      },
      ticks: {
        color: 'rgba(255, 255, 255, 0.7)',
        font: {
          size: 12,
          weight: '500',
        },
        stepSize: 25,
        callback: function(value) {
          return value;
        },
      },
      border: {
        display: false,
      },
    },
    y: {
      grid: {
        display: false,
      },
      ticks: {
        color: 'rgba(255, 255, 255, 0.95)',
        font: {
          size: 14,
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
      borderColor: 'rgba(100, 108, 255, 0.8)',
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
          return `${context.parsed.x}/100`;
        },
      },
    },
  },
}));
</script>

<style scoped>
.bar-chart-container {
  width: 100%;
  max-width: 700px;
  height: 320px;
  margin: 0 auto;
  padding: 1rem;
}

@media (max-width: 968px) {
  .bar-chart-container {
    max-width: 600px;
    height: 300px;
  }
}

@media (max-width: 768px) {
  .bar-chart-container {
    max-width: 100%;
    height: 280px;
    padding: 0.5rem;
  }
}
</style>

