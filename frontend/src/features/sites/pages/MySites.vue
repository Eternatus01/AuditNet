<template>
  <div class="sites-container">
    <div class="sites-hero">
      <div class="hero-text">
        <div class="hero-badge">
          <IconLucideMonitor />
          <span>Мониторинг сайтов</span>
        </div>
        <h1>Мои сайты</h1>
        <p class="sites-subtitle">
          Добавьте до {{ MAX_SITES }} сайтов и выберите день недели — каждую неделю
          аудит запустится автоматически, а изменения появятся в аналитике.
        </p>
      </div>

      <div class="hero-counter">
        <div class="counter-ring" :style="{ '--p': (sites.length / MAX_SITES) * 100 }">
          <div class="counter-inner">
            <span class="counter-num">{{ sites.length }}</span>
            <span class="counter-total">из {{ MAX_SITES }}</span>
          </div>
        </div>
        <span class="counter-label">сайтов добавлено</span>
      </div>
    </div>

    <LoadingState v-if="isLoading" text="Загрузка сайтов..." size="lg" />

    <template v-else>
      <div v-if="error" class="error-state">{{ error }}</div>

      <div class="sites-grid">
        <transition-group name="card">
          <div v-for="site in sites" :key="site.id" class="site-card" :class="{ 'is-paused': !site.is_active }">
            <div class="card-glow"></div>

            <div class="card-top">
              <div class="site-avatar">{{ siteInitial(site) }}</div>
              <div class="site-info">
                <h3 class="site-name">{{ site.name || hostOf(site.url) }}</h3>
                <a class="site-url" :href="site.url" target="_blank" rel="noopener">
                  <IconLucideLink />
                  {{ hostOf(site.url) }}
                </a>
              </div>
              <button class="icon-btn danger" title="Удалить сайт" @click="handleDelete(site)">
                <IconLucideTrash2 />
              </button>
            </div>

            <div class="schedule-row">
              <div class="day-badge">
                <IconLucideCalendarClock />
                <span>Каждую {{ weekdayAccusative(site.schedule_day) }}</span>
              </div>
              <button
                class="status-toggle"
                :class="{ active: site.is_active }"
                :title="site.is_active ? 'Поставить на паузу' : 'Включить'"
                @click="toggleActive(site)"
              >
                <span class="toggle-knob"></span>
                <span class="toggle-text">{{ site.is_active ? 'Активен' : 'Пауза' }}</span>
              </button>
            </div>

            <div class="day-picker">
              <button
                v-for="day in WEEKDAYS"
                :key="day.value"
                class="day-chip"
                :class="{ selected: site.schedule_day === day.value }"
                :title="day.label"
                @click="changeDay(site, day.value)"
              >
                {{ day.short }}
              </button>
            </div>

            <div v-if="site.last_audit && hasScores(site)" class="scores-row">
              <div
                v-for="metric in scoreMetrics(site)"
                :key="metric.key"
                class="score-ring"
                :class="metric.cls"
                :style="{ '--p': metric.value ?? 0 }"
              >
                <span class="score-val">{{ metric.value ?? '—' }}</span>
                <span class="score-key">{{ metric.label }}</span>
              </div>
            </div>
            <div v-else class="no-audit-yet">
              <IconLucideHourglass />
              <span>Аудит ещё не проводился</span>
            </div>

            <div class="card-footer">
              <span class="last-run">
                <IconLucideClock />
                {{ site.last_run_at ? formatDate(site.last_run_at) : 'нет запусков' }}
              </span>
              <div class="card-actions">
                <button class="action-btn ghost" @click="goToAnalytics(site.url)">
                  <IconLucideTrendingUp />
                  Аналитика
                </button>
                <button
                  class="action-btn primary"
                  :disabled="runningId === site.id"
                  @click="handleRun(site.id)"
                >
                  <IconLucideLoader2 v-if="runningId === site.id" class="spinner" />
                  <IconLucidePlay v-else />
                  {{ runningId === site.id ? 'Запуск...' : 'Запустить' }}
                </button>
                <button class="action-btn danger" @click="handleDelete(site)">
                  <IconLucideTrash2 />
                  Удалить
                </button>
              </div>
            </div>
          </div>
        </transition-group>

        <div v-if="sites.length < MAX_SITES" class="add-card">
          <div class="add-head">
            <div class="add-icon">
              <IconLucidePlus />
            </div>
            <h3>Добавить сайт</h3>
          </div>

          <form class="add-form" @submit.prevent="handleCreate">
            <div class="field">
              <label class="field-label">Адрес сайта</label>
              <div class="url-field" :class="{ 'has-error': urlError, 'is-valid': urlValid }">
                <IconLucideLink class="field-icon" />
                <input
                  v-model="form.url"
                  type="text"
                  class="field-input"
                  placeholder="example.com"
                  autocomplete="url"
                  @input="onUrlInput"
                />
                <IconLucideCheck v-if="urlValid" class="field-check" />
              </div>
              <transition name="fade">
                <p v-if="urlError" class="field-error">{{ urlError }}</p>
              </transition>
            </div>

            <div class="field">
              <label class="field-label">Название <span class="optional">(необязательно)</span></label>
              <input
                v-model="form.name"
                type="text"
                class="field-input solo"
                placeholder="Мой блог"
              />
            </div>

            <div class="field">
              <label class="field-label">День еженедельного анализа</label>
              <div class="day-picker">
                <button
                  v-for="day in WEEKDAYS"
                  :key="day.value"
                  type="button"
                  class="day-chip"
                  :class="{ selected: form.schedule_day === day.value }"
                  :title="day.label"
                  @click="form.schedule_day = day.value"
                >
                  {{ day.short }}
                </button>
              </div>
            </div>

            <transition name="fade">
              <p v-if="formError" class="field-error">{{ formError }}</p>
            </transition>

            <button
              type="submit"
              class="submit-btn"
              :disabled="isCreating || !urlValid"
            >
              <IconLucideLoader2 v-if="isCreating" class="spinner" />
              <IconLucidePlus v-else />
              {{ isCreating ? 'Добавление...' : 'Добавить сайт' }}
            </button>
          </form>
        </div>
      </div>

      <div v-if="sites.length === 0" class="hint">
        Пока нет добавленных сайтов. Добавьте первый сайт в форме выше.
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useSitesApi } from '../composables/useSitesApi';
import { WEEKDAYS, type MonitoredSite } from '../types';
import LoadingState from '@/shared/ui/molecules/LoadingState.vue';
import { logger } from '@/shared/utils/logger';
import IconLucideMonitor from '~icons/lucide/monitor';
import IconLucideTrash2 from '~icons/lucide/trash-2';
import IconLucideCalendarClock from '~icons/lucide/calendar-clock';
import IconLucideClock from '~icons/lucide/clock';
import IconLucideLink from '~icons/lucide/link';
import IconLucidePlus from '~icons/lucide/plus';
import IconLucidePlay from '~icons/lucide/play';
import IconLucideTrendingUp from '~icons/lucide/trending-up';
import IconLucideLoader2 from '~icons/lucide/loader-2';
import IconLucideCheck from '~icons/lucide/check';
import IconLucideHourglass from '~icons/lucide/hourglass';

const MAX_SITES = 3;

const router = useRouter();
const sitesApi = useSitesApi();

const sites = ref<MonitoredSite[]>([]);
const isLoading = ref(false);
const isCreating = ref(false);
const runningId = ref<number | null>(null);
const error = ref<string | null>(null);
const formError = ref<string | null>(null);
const urlError = ref<string | null>(null);
const urlValid = ref(false);

const form = reactive({
  url: '',
  name: '',
  schedule_day: 3,
});

const normalizeUrl = (url: string): string => {
  if (!url) return url;
  const trimmed = url.trim();
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
    return trimmed;
  }
  return `https://${trimmed}`;
};

const validateUrl = (url: string): { valid: boolean; error: string | null } => {
  if (!url) {
    return { valid: false, error: null };
  }
  try {
    const urlObj = new URL(normalizeUrl(url));
    if (!['http:', 'https:'].includes(urlObj.protocol)) {
      return { valid: false, error: 'URL должен начинаться с http:// или https://' };
    }
    if (!urlObj.hostname || !urlObj.hostname.includes('.') || urlObj.hostname.length < 4) {
      return { valid: false, error: 'Укажите корректный домен' };
    }
    return { valid: true, error: null };
  } catch {
    return { valid: false, error: 'Некорректный формат URL. Пример: https://example.com' };
  }
};

const onUrlInput = () => {
  const result = validateUrl(form.url);
  urlValid.value = result.valid;
  urlError.value = form.url ? result.error : null;
};

const loadSites = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await sitesApi.getSites();
    if (response.success) {
      sites.value = response.data;
    }
  } catch (err) {
    logger.error('Ошибка загрузки сайтов:', err);
    error.value = 'Не удалось загрузить список сайтов';
  } finally {
    isLoading.value = false;
  }
};

const handleCreate = async () => {
  formError.value = null;
  const result = validateUrl(form.url);
  if (!result.valid) {
    urlError.value = result.error || 'Введите корректный URL';
    urlValid.value = false;
    return;
  }

  isCreating.value = true;
  try {
    const response = await sitesApi.createSite({
      url: normalizeUrl(form.url),
      name: form.name || null,
      schedule_day: form.schedule_day,
    });
    if (response.success) {
      sites.value.push(response.data);
      form.url = '';
      form.name = '';
      form.schedule_day = 3;
      urlValid.value = false;
      urlError.value = null;
    }
  } catch (err: any) {
    logger.error('Ошибка добавления сайта:', err);
    formError.value = err?.response?.data?.message || 'Не удалось добавить сайт';
  } finally {
    isCreating.value = false;
  }
};

const changeDay = async (site: MonitoredSite, value: number) => {
  if (site.schedule_day === value) return;
  const prev = site.schedule_day;
  site.schedule_day = value;
  try {
    const response = await sitesApi.updateSite(site.id, { schedule_day: value });
    if (response.success) {
      site.schedule_day = response.data.schedule_day;
    }
  } catch (err) {
    site.schedule_day = prev;
    logger.error('Ошибка обновления дня:', err);
  }
};

const toggleActive = async (site: MonitoredSite) => {
  const next = !site.is_active;
  site.is_active = next;
  try {
    const response = await sitesApi.updateSite(site.id, { is_active: next });
    if (response.success) {
      site.is_active = response.data.is_active;
    }
  } catch (err) {
    site.is_active = !next;
    logger.error('Ошибка обновления статуса:', err);
  }
};

const handleDelete = async (site: MonitoredSite) => {
  const label = site.name || hostOf(site.url);
  if (!window.confirm(`Удалить сайт «${label}» из мониторинга?`)) {
    return;
  }

  try {
    const response = await sitesApi.deleteSite(site.id);
    if (response.success) {
      sites.value = sites.value.filter((s) => s.id !== site.id);
    }
  } catch (err) {
    logger.error('Ошибка удаления сайта:', err);
  }
};

const handleRun = async (id: number) => {
  runningId.value = id;
  try {
    await sitesApi.runSite(id);
  } catch (err) {
    logger.error('Ошибка запуска аудита:', err);
  } finally {
    runningId.value = null;
  }
};

const goToAnalytics = (url: string) => {
  router.push({ name: 'analytics', query: { url } });
};

const hostOf = (url: string) => {
  try {
    return new URL(url).hostname.replace(/^www\./, '');
  } catch {
    return url;
  }
};

const siteInitial = (site: MonitoredSite) => {
  const base = site.name || hostOf(site.url);
  return base.charAt(0).toUpperCase();
};

const weekdayAccusative = (value: number) => {
  const names: Record<number, string> = {
    1: 'понедельник',
    2: 'вторник',
    3: 'среду',
    4: 'четверг',
    5: 'пятницу',
    6: 'субботу',
    7: 'воскресенье',
  };
  return names[value] || '';
};

const hasScores = (site: MonitoredSite) => {
  const a = site.last_audit;
  return !!a && (a.performance !== null || a.accessibility !== null || a.best_practices !== null || a.seo !== null);
};

const scoreMetrics = (site: MonitoredSite) => {
  const a = site.last_audit!;
  return [
    { key: 'performance', label: 'Произв.', value: a.performance, cls: scoreClass(a.performance) },
    { key: 'accessibility', label: 'Дост.', value: a.accessibility, cls: scoreClass(a.accessibility) },
    { key: 'best_practices', label: 'Практ.', value: a.best_practices, cls: scoreClass(a.best_practices) },
    { key: 'seo', label: 'SEO', value: a.seo, cls: scoreClass(a.seo) },
  ];
};

const scoreClass = (score: number | null) => {
  if (score === null) return 'neutral';
  if (score >= 90) return 'good';
  if (score >= 50) return 'average';
  return 'poor';
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
};

onMounted(() => {
  loadSites();
});
</script>

<style scoped>
.sites-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.sites-hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 2.5rem;
  padding: 2.5rem;
  border-radius: 24px;
  background:
    radial-gradient(circle at 0% 0%, rgba(100, 108, 255, 0.18), transparent 55%),
    radial-gradient(circle at 100% 100%, rgba(156, 39, 176, 0.18), transparent 55%),
    linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border: 1px solid rgba(100, 108, 255, 0.18);
  position: relative;
  overflow: hidden;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.875rem;
  background: rgba(100, 108, 255, 0.15);
  border: 1px solid rgba(100, 108, 255, 0.3);
  border-radius: 999px;
  color: #b9bdff;
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.hero-badge svg {
  width: 16px;
  height: 16px;
}

.hero-text h1 {
  font-size: 2.6rem;
  font-weight: 700;
  margin: 0 0 0.75rem 0;
  color: #fff;
  letter-spacing: -0.5px;
}

.sites-subtitle {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.05rem;
  margin: 0;
  max-width: 560px;
  line-height: 1.5;
}

.hero-counter {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.counter-ring {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: conic-gradient(#646cff calc(var(--p) * 1%), rgba(255, 255, 255, 0.08) 0);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.5s ease;
}

.counter-inner {
  width: 92px;
  height: 92px;
  border-radius: 50%;
  background: #15152a;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.1rem;
}

.counter-num {
  font-size: 2.25rem;
  font-weight: 700;
  color: #fff;
  line-height: 1;
}

.counter-total {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.55);
}

.counter-label {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
}

.error-state {
  color: #ff6b6b;
  text-align: center;
  margin-bottom: 1.5rem;
}

.sites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.5rem;
}

.sites-grid > span {
  display: contents;
}

.site-card,
.add-card {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border: 1px solid rgba(100, 108, 255, 0.15);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
  position: relative;
  overflow: hidden;
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.35s ease, border-color 0.35s ease;
}

.site-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 48px rgba(100, 108, 255, 0.22);
  border-color: rgba(100, 108, 255, 0.4);
}

.site-card.is-paused {
  opacity: 0.72;
}

.card-glow {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #646cff 0%, #9c27b0 100%);
  opacity: 0;
  transition: opacity 0.35s ease;
}

.site-card:hover .card-glow {
  opacity: 1;
}

.card-top {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.site-avatar {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  border-radius: 14px;
  flex-shrink: 0;
  color: #fff;
  font-size: 1.35rem;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(100, 108, 255, 0.35);
}

.site-info {
  flex: 1;
  min-width: 0;
}

.site-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.3rem 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.site-url {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.55);
  text-decoration: none;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  transition: color 0.2s ease;
}

.site-url:hover {
  color: #b9bdff;
}

.site-url svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.icon-btn {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f87171;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.icon-btn svg {
  width: 18px;
  height: 18px;
}

.icon-btn.danger:hover {
  background: rgba(239, 68, 68, 0.25);
  border-color: #ef4444;
  color: #ef4444;
}

.schedule-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.day-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.85rem;
  color: #b9bdff;
  background: rgba(100, 108, 255, 0.12);
  padding: 0.4rem 0.75rem;
  border-radius: 999px;
}

.day-badge svg {
  width: 15px;
  height: 15px;
}

.status-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.06);
  padding: 0.3rem 0.7rem 0.3rem 0.35rem;
  border-radius: 999px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  font-weight: 600;
  transition: all 0.25s ease;
}

.toggle-knob {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  transition: all 0.25s ease;
}

.status-toggle.active {
  background: rgba(76, 175, 80, 0.15);
  color: #4caf50;
}

.status-toggle.active .toggle-knob {
  background: #4caf50;
  box-shadow: 0 0 8px rgba(76, 175, 80, 0.6);
}

.day-picker {
  display: flex;
  gap: 0.35rem;
  flex-wrap: wrap;
}

.day-chip {
  flex: 1;
  min-width: 36px;
  padding: 0.45rem 0;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(15, 15, 30, 0.5);
  color: rgba(255, 255, 255, 0.6);
  border-radius: 9px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.day-chip:hover {
  border-color: rgba(100, 108, 255, 0.5);
  color: #fff;
}

.day-chip.selected {
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  border-color: transparent;
  color: #fff;
  box-shadow: 0 4px 12px rgba(100, 108, 255, 0.35);
}

.scores-row {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
  padding-top: 0.5rem;
}

.score-ring {
  --p: 0;
  width: 64px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
}

.score-ring::before {
  content: '';
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background:
    radial-gradient(closest-side, #1a1a2e 78%, transparent 79% 100%),
    conic-gradient(var(--ring-color) calc(var(--p) * 1%), rgba(255, 255, 255, 0.08) 0);
  display: grid;
  place-content: center;
}

.score-ring {
  position: relative;
}

.score-val {
  position: absolute;
  top: 16px;
  font-size: 0.95rem;
  font-weight: 700;
  color: #fff;
}

.score-key {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.55);
}

.score-ring.good {
  --ring-color: #4caf50;
}

.score-ring.average {
  --ring-color: #ff9800;
}

.score-ring.poor {
  --ring-color: #f44336;
}

.score-ring.neutral {
  --ring-color: rgba(255, 255, 255, 0.25);
}

.no-audit-yet {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.85rem;
}

.no-audit-yet svg {
  width: 16px;
  height: 16px;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin-top: auto;
  flex-wrap: wrap;
}

.last-run {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.55);
}

.last-run svg {
  width: 14px;
  height: 14px;
}

.card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border: none;
  cursor: pointer;
  padding: 0.5rem 0.85rem;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  transition: all 0.2s ease;
}

.action-btn svg {
  width: 15px;
  height: 15px;
}

.action-btn.ghost {
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.75);
}

.action-btn.ghost:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}

.action-btn.primary {
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  color: #fff;
  box-shadow: 0 4px 12px rgba(100, 108, 255, 0.3);
}

.action-btn.primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(100, 108, 255, 0.45);
}

.action-btn.danger {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.35);
}

.action-btn.danger:hover {
  background: rgba(239, 68, 68, 0.28);
  color: #ef4444;
  border-color: #ef4444;
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Add card */
.add-card {
  border-style: dashed;
  border-color: rgba(100, 108, 255, 0.3);
  justify-content: flex-start;
  gap: 1.25rem;
}

.add-head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.add-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(100, 108, 255, 0.15);
  color: #b9bdff;
}

.add-icon svg {
  width: 22px;
  height: 22px;
}

.add-head h3 {
  margin: 0;
  color: #fff;
  font-size: 1.15rem;
}

.add-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.field-label {
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.optional {
  color: rgba(255, 255, 255, 0.4);
  font-weight: 400;
}

.url-field {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(15, 15, 30, 0.8);
  border: 1px solid rgba(100, 108, 255, 0.2);
  border-radius: 10px;
  padding: 0 0.75rem;
  transition: all 0.25s ease;
}

.url-field.has-error {
  border-color: #ff4e42;
  box-shadow: 0 0 0 3px rgba(255, 78, 66, 0.1);
}

.url-field.is-valid {
  border-color: #0cce6b;
  box-shadow: 0 0 0 3px rgba(12, 206, 107, 0.1);
}

.field-icon {
  width: 16px;
  height: 16px;
  color: rgba(255, 255, 255, 0.45);
  flex-shrink: 0;
}

.field-check {
  width: 16px;
  height: 16px;
  color: #0cce6b;
  flex-shrink: 0;
}

.field-input {
  flex: 1;
  min-width: 0;
  background: transparent;
  border: none;
  padding: 0.7rem 0;
  color: #fff;
  font-size: 0.92rem;
  font-family: inherit;
}

.field-input:focus {
  outline: none;
}

.field-input.solo {
  background: rgba(15, 15, 30, 0.8);
  border: 1px solid rgba(100, 108, 255, 0.2);
  border-radius: 10px;
  padding: 0.7rem 0.75rem;
}

.field-input.solo:focus {
  border-color: #646cff;
}

.field-error {
  margin: 0;
  font-size: 0.82rem;
  color: #ff6b6b;
}

.submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  border: none;
  cursor: pointer;
  padding: 0.85rem;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  font-family: inherit;
  color: #fff;
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  box-shadow: 0 4px 14px rgba(100, 108, 255, 0.35);
  transition: all 0.2s ease;
  margin-top: 0.25rem;
}

.submit-btn svg {
  width: 18px;
  height: 18px;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(100, 108, 255, 0.5);
}

.submit-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.spinner {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.hint {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 2rem;
}

/* transitions */
.card-enter-active,
.card-leave-active {
  transition: all 0.4s ease;
}

.card-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.96);
}

.card-leave-to {
  opacity: 0;
  transform: scale(0.92);
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

@media (max-width: 768px) {
  .sites-container {
    padding: 1rem;
  }

  .sites-hero {
    flex-direction: column;
    text-align: center;
    padding: 1.75rem;
  }

  .hero-text h1 {
    font-size: 2rem;
  }

  .sites-grid {
    grid-template-columns: 1fr;
  }
}
</style>
