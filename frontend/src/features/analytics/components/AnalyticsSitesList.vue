<template>
  <div class="sites-section">
    <h2 class="section-title">Выберите сайт</h2>
    <p class="section-subtitle">Нажмите на карточку, чтобы просмотреть аналитику производительности</p>
    
    <div class="sites-grid">
      <SiteCard
        v-for="siteData in sitesData"
        :key="siteData.url"
        :url="siteData.url"
        :audit-count="siteData.count"
        :last-audit-date="siteData.lastAudit"
        :is-selected="false"
        @select="$emit('select', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import SiteCard from './SiteCard.vue';

interface SiteData {
  url: string;
  count: number;
  lastAudit: string;
}

interface Props {
  sitesData: SiteData[];
}

defineProps<Props>();

defineEmits<{
  select: [url: string];
}>();
</script>

<style scoped>
.sites-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.75rem 0;
  letter-spacing: -0.3px;
}

.section-subtitle {
  color: rgba(255, 255, 255, 0.65);
  font-size: 1rem;
  margin: 0 0 2.5rem 0;
  line-height: 1.5;
}

.sites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .sites-grid {
    grid-template-columns: 1fr;
  }
}
</style>
