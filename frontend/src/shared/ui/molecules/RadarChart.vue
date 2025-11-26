<template>
  <div class="radar-chart-container">
    <Radar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Radar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
  type ChartData,
  type ChartOptions,
} from 'chart.js';

// Регистрируем необходимые компоненты Chart.js
ChartJS.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

interface RadarChartProps {
  performanceScore: number | string;
  accessibilityScore: number | string;
  bestPracticesScore: number | string;
  seoScore: number | string;
}

const props = defineProps<RadarChartProps>();

// Конвертируем значения в числа
const getNumericValue = (value: number | string): number => {
  if (typeof value === 'number') return value;
  if (value === '--' || value === 'N/A') return 0;
  return parseFloat(value) || 0;
};

const chartData = computed<ChartData<'radar'>>(() => ({
  labels: ['Performance', 'Accessibility', 'Best Practices', 'SEO'],
  datasets: [
    {
      label: 'Scores',
      data: [
        getNumericValue(props.performanceScore),
        getNumericValue(props.accessibilityScore),
        getNumericValue(props.bestPracticesScore),
        getNumericValue(props.seoScore),
      ],
      backgroundColor: 'rgba(100, 108, 255, 0.3)',
      borderColor: 'rgba(100, 108, 255, 1)',
      borderWidth: 3,
      pointBackgroundColor: 'rgba(100, 108, 255, 1)',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: 'rgba(100, 108, 255, 1)',
      pointRadius: 5,
      pointHoverRadius: 8,
      pointBorderWidth: 2,
    },
  ],
}));

const chartOptions = computed<ChartOptions<'radar'>>(() => ({
  responsive: true,
  maintainAspectRatio: true,
  scales: {
    r: {
      beginAtZero: true,
      max: 100,
      min: 0,
      ticks: {
        stepSize: 25,
        color: 'rgba(255, 255, 255, 0.7)',
        backdropColor: 'transparent',
        font: {
          size: 12,
          weight: '500',
        },
        callback: function(value) {
          return value;
        },
      },
      grid: {
        color: 'rgba(255, 255, 255, 0.15)',
        lineWidth: 1,
      },
      angleLines: {
        color: 'rgba(255, 255, 255, 0.15)',
        lineWidth: 1,
      },
      pointLabels: {
        color: 'rgba(255, 255, 255, 0.95)',
        font: {
          size: 14,
          weight: '700',
        },
        padding: 15,
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
        size: 13,
        weight: '500',
      },
      callbacks: {
        label: (context) => {
          return `Score: ${context.parsed.r}/100`;
        },
      },
    },
  },
}));
</script>

<style scoped>
.radar-chart-container {
  width: 100%;
  max-width: 500px;
  height: 450px;
  margin: 0 auto;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 968px) {
  .radar-chart-container {
    max-width: 400px;
    height: 380px;
  }
}

@media (max-width: 768px) {
  .radar-chart-container {
    max-width: 100%;
    height: 350px;
    padding: 1rem;
  }
}
</style>

