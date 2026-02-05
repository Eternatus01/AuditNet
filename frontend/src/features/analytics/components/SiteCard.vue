<template>
  <div 
    class="site-card" 
    :class="{ 'is-selected': isSelected }"
    @click="handleClick"
  >
    <div class="site-header">
      <div class="site-icon">
        <IconLucideGlobe />
      </div>
      <div class="site-info">
        <h3 class="site-url">{{ url }}</h3>
        <p class="site-meta">{{ auditCount }} {{ auditCountLabel }}</p>
      </div>
    </div>
    
    <div class="site-footer">
      <div class="site-stat">
        <IconLucideCalendar />
        <span>{{ lastAuditDate }}</span>
      </div>
      <div class="site-action">
        <IconLucideChevronRight />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import IconLucideGlobe from '~icons/lucide/globe';
import IconLucideCalendar from '~icons/lucide/calendar';
import IconLucideChevronRight from '~icons/lucide/chevron-right';

interface Props {
  url: string;
  auditCount: number;
  lastAuditDate: string;
  isSelected?: boolean;
}

interface Emits {
  (e: 'select', url: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  isSelected: false,
});

const emit = defineEmits<Emits>();

const auditCountLabel = computed(() => {
  const count = props.auditCount;
  if (count === 1) return 'проверка';
  if (count >= 2 && count <= 4) return 'проверки';
  return 'проверок';
});

const handleClick = () => {
  emit('select', props.url);
};
</script>

<style scoped>
.site-card {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border: 1px solid rgba(100, 108, 255, 0.15);
  border-radius: 20px;
  padding: 1.75rem;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  position: relative;
  overflow: hidden;
}

.site-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #646cff 0%, #9c27b0 100%);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.site-card:hover::before {
  opacity: 1;
}

.site-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(100, 108, 255, 0.25);
  border-color: rgba(100, 108, 255, 0.4);
  background: linear-gradient(135deg, rgba(26, 26, 46, 1) 0%, rgba(30, 30, 50, 1) 100%);
}

.site-card.is-selected {
  border-color: #646cff;
  background: linear-gradient(135deg, rgba(100, 108, 255, 0.15) 0%, rgba(156, 39, 176, 0.15) 100%);
  box-shadow: 0 12px 32px rgba(100, 108, 255, 0.4);
}

.site-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.site-icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  border-radius: 16px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(100, 108, 255, 0.3);
  transition: transform 0.3s ease;
}

.site-card:hover .site-icon {
  transform: scale(1.05);
}

.site-icon svg {
  width: 28px;
  height: 28px;
  color: #fff;
}

.site-info {
  flex: 1;
  min-width: 0;
}

.site-url {
  font-size: 1.125rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.5rem 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  line-height: 1.3;
  letter-spacing: -0.2px;
}

.site-meta {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.65);
  margin: 0;
  font-weight: 500;
}

.site-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin-top: auto;
}

.site-stat {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.site-stat svg {
  width: 18px;
  height: 18px;
  color: #646cff;
  opacity: 0.9;
}

.site-action {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: rgba(100, 108, 255, 0.12);
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.site-action svg {
  width: 20px;
  height: 20px;
  color: #646cff;
  transition: transform 0.3s ease;
}

.site-card:hover .site-action {
  background: rgba(100, 108, 255, 0.25);
  transform: scale(1.05);
}

.site-card:hover .site-action svg {
  transform: translateX(3px);
}

@media (max-width: 768px) {
  .site-card {
    padding: 1rem;
  }

  .site-icon {
    width: 40px;
    height: 40px;
  }

  .site-icon svg {
    width: 20px;
    height: 20px;
  }

  .site-url {
    font-size: 1rem;
  }
}
</style>
