<template>
  <div class="analytics-header-section">
    <button class="back-button" @click="$emit('back')">
      <IconLucideArrowLeft />
      <span>Назад</span>
    </button>
    <div class="site-info-header">
      <div class="site-url-display">
        <IconLucideGlobe class="url-icon" />
        <h2>{{ url }}</h2>
      </div>
      <div class="header-stats">
        <div class="mini-stat">
          <IconLucideActivity />
          <span>{{ totalAudits }} проверок</span>
        </div>
        <div class="mini-stat">
          <IconLucideClock />
          <span>{{ lastAudit }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import IconLucideArrowLeft from '~icons/lucide/arrow-left';
import IconLucideGlobe from '~icons/lucide/globe';
import IconLucideActivity from '~icons/lucide/activity';
import IconLucideClock from '~icons/lucide/clock';

interface Props {
  url: string;
  totalAudits: number;
  lastAudit: string;
}

defineProps<Props>();

defineEmits<{
  back: [];
}>();
</script>

<style scoped>
.analytics-header-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  align-self: flex-start;
}

.back-button:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(100, 108, 255, 0.3);
  color: #646cff;
  transform: translateX(-4px);
}

.back-button svg {
  width: 18px;
  height: 18px;
}

.site-info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.site-url-display {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.url-icon {
  width: 32px;
  height: 32px;
  color: #646cff;
  padding: 8px;
  background: rgba(100, 108, 255, 0.15);
  border-radius: 10px;
}

.site-url-display h2 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
  letter-spacing: -0.3px;
}

.header-stats {
  display: flex;
  gap: 1.5rem;
}

.mini-stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
}

.mini-stat svg {
  width: 18px;
  height: 18px;
  color: #646cff;
}

@media (max-width: 768px) {
  .site-info-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .site-url-display h2 {
    font-size: 1.35rem;
  }

  .header-stats {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
