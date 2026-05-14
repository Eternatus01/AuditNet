<template>
  <div class="comparison-card" @click="handleClick">
    <div class="comparison-main">
      <div class="comparison-title">
        <IconLucideGitCompare />
        <div>
          <span>Сравнение сайтов</span>
          <strong>{{ comparison.title || hostSummary }}</strong>
        </div>
      </div>
      <div class="comparison-date">
        <IconLucideClock />
        <span>{{ formattedDate }}</span>
      </div>
    </div>

    <div class="comparison-sites">
      <span
        v-for="site in comparison.sites"
        :key="site.id"
        class="site-pill"
        :class="{ failed: Boolean(site.error_message) }"
      >
        {{ getHostname(site.url) }}
      </span>
    </div>

    <div class="comparison-stats">
      <div class="stat-item">
        <span>Сайтов</span>
        <strong>{{ comparison.sites.length }}</strong>
      </div>
      <div class="stat-item" :class="getScoreClass(averageScore)">
        <span>Средняя оценка</span>
        <strong>{{ averageScore ?? "--" }}</strong>
      </div>
      <div class="stat-item" :class="getScoreClass(bestScore)">
        <span>Лучший сайт</span>
        <strong>{{ bestScore ?? "--" }}</strong>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { AuditComparison, ComparisonSite, ScoreClass } from "../types";
import IconLucideClock from "~icons/lucide/clock";
import IconLucideGitCompare from "~icons/lucide/git-compare";

interface Props {
  comparison: AuditComparison;
  formatDate: (date: string) => string;
  getScoreClass: (score: number | null) => ScoreClass;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  click: [id: number];
}>();

const completedSites = computed(() => props.comparison.sites.filter((site) => !site.error_message));

const getSiteAverageScore = (site: ComparisonSite): number | null => {
  const scores = [site.performance, site.accessibility, site.best_practices, site.seo].filter(
    (score): score is number => typeof score === "number"
  );

  if (scores.length === 0) return null;
  return Math.round(scores.reduce((sum, score) => sum + score, 0) / scores.length);
};

const siteAverageScores = computed(() =>
  completedSites.value
    .map(getSiteAverageScore)
    .filter((score): score is number => typeof score === "number")
);

const averageScore = computed(() => {
  if (siteAverageScores.value.length === 0) return null;
  return Math.round(siteAverageScores.value.reduce((sum, score) => sum + score, 0) / siteAverageScores.value.length);
});

const bestScore = computed(() => {
  if (siteAverageScores.value.length === 0) return null;
  return Math.max(...siteAverageScores.value);
});

const formattedDate = computed(() => props.formatDate(props.comparison.created_at));

const hostSummary = computed(() => props.comparison.sites.map((site) => getHostname(site.url)).join(" vs "));

const getHostname = (url: string): string => {
  try {
    return new URL(url).hostname.replace(/^www\./, "");
  } catch {
    return url;
  }
};

const handleClick = () => {
  emit("click", props.comparison.id);
};
</script>

<style scoped>
.comparison-card {
  background: linear-gradient(135deg, rgba(8, 145, 178, 0.12), rgba(124, 58, 237, 0.12));
  border: 1px solid rgba(8, 145, 178, 0.35);
  border-radius: 12px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-width: 0;
}

.comparison-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  border-color: #06b6d4;
}

.comparison-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
}

.comparison-title,
.comparison-date {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.comparison-title svg,
.comparison-date svg {
  width: 18px;
  height: 18px;
  color: #06b6d4;
  flex-shrink: 0;
}

.comparison-title span {
  display: block;
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.82rem;
}

.comparison-title strong {
  display: block;
  max-width: 720px;
  overflow: hidden;
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.08rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.comparison-date {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.9rem;
}

.comparison-sites {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.site-pill {
  padding: 0.35rem 0.65rem;
  border-radius: 999px;
  background: rgba(6, 182, 212, 0.14);
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.85rem;
}

.site-pill.failed {
  background: rgba(239, 68, 68, 0.16);
  color: #fca5a5;
}

.comparison-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  padding: 0.75rem;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.04);
}

.stat-item span {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.85rem;
}

.stat-item strong {
  color: rgba(255, 255, 255, 0.92);
  font-size: 1.5rem;
}

.stat-item.good strong {
  color: #4caf50;
}

.stat-item.average strong {
  color: #ffc107;
}

.stat-item.poor strong {
  color: #f44336;
}
</style>
