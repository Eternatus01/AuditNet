<template>
  <div class="sites-container">
    <div class="sites-header">
      <h1>Мои сайты</h1>
      <p class="sites-subtitle">
        Добавьте до {{ MAX_SITES }} сайтов и выберите день недели — каждую неделю
        будет автоматически выполняться аудит, а изменения покажутся в аналитике.
      </p>
    </div>

    <LoadingState v-if="isLoading" text="Загрузка сайтов..." size="lg" />

    <template v-else>
      <div v-if="error" class="error-state">{{ error }}</div>

      <div class="sites-grid">
        <div v-for="site in sites" :key="site.id" class="site-card">
          <div class="card-top">
            <div class="site-icon">
              <IconLucideGlobe />
            </div>
            <div class="site-info">
              <h3 class="site-name">{{ site.name || site.url }}</h3>
              <p class="site-url">{{ site.url }}</p>
            </div>
            <button
              class="icon-btn danger"
              title="Удалить сайт"
              @click="handleDelete(site.id)"
            >
              <IconLucideTrash2 />
            </button>
          </div>

          <div class="card-controls">
            <label class="control">
              <span class="control-label">День анализа</span>
              <select
                class="control-select"
                :value="site.schedule_day"
                @change="handleDayChange(site, $event)"
              >
                <option v-for="day in WEEKDAYS" :key="day.value" :value="day.value">
                  {{ day.label }}
                </option>
              </select>
            </label>

            <label class="control toggle">
              <span class="control-label">Активен</span>
              <input
                type="checkbox"
                :checked="site.is_active"
                @change="handleActiveChange(site, $event)"
              />
            </label>
          </div>

          <div class="card-meta">
            <div class="meta-row">
              <IconLucideCalendarClock />
              <span>
                Последний запуск:
                {{ site.last_run_at ? formatDate(site.last_run_at) : 'ещё не запускался' }}
              </span>
            </div>
            <div v-if="site.last_audit" class="meta-scores">
              <span class="score" :class="scoreClass(site.last_audit.performance)">
                P: {{ site.last_audit.performance ?? '—' }}
              </span>
              <span class="score" :class="scoreClass(site.last_audit.accessibility)">
                A: {{ site.last_audit.accessibility ?? '—' }}
              </span>
              <span class="score" :class="scoreClass(site.last_audit.best_practices)">
                BP: {{ site.last_audit.best_practices ?? '—' }}
              </span>
              <span class="score" :class="scoreClass(site.last_audit.seo)">
                SEO: {{ site.last_audit.seo ?? '—' }}
              </span>
            </div>
          </div>

          <div class="card-actions">
            <Button
              variant="primary"
              size="sm"
              :loading="runningId === site.id"
              @click="handleRun(site.id)"
            >
              Запустить сейчас
            </Button>
            <Button
              variant="ghost"
              size="sm"
              @click="goToAnalytics(site.url)"
            >
              Аналитика
            </Button>
          </div>
        </div>

        <div v-if="sites.length < MAX_SITES" class="add-card">
          <h3>Добавить сайт</h3>
          <form class="add-form" @submit.prevent="handleCreate">
            <input
              v-model="form.url"
              type="url"
              class="text-input"
              placeholder="https://example.com"
              required
            />
            <input
              v-model="form.name"
              type="text"
              class="text-input"
              placeholder="Название (необязательно)"
            />
            <select v-model.number="form.schedule_day" class="control-select">
              <option v-for="day in WEEKDAYS" :key="day.value" :value="day.value">
                {{ day.label }}
              </option>
            </select>
            <p v-if="formError" class="form-error">{{ formError }}</p>
            <Button type="submit" variant="primary" full-width :loading="isCreating">
              Добавить
            </Button>
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
import Button from '@/shared/ui/atoms/Button.vue';
import LoadingState from '@/shared/ui/molecules/LoadingState.vue';
import { logger } from '@/shared/utils/logger';
import IconLucideGlobe from '~icons/lucide/globe';
import IconLucideTrash2 from '~icons/lucide/trash-2';
import IconLucideCalendarClock from '~icons/lucide/calendar-clock';

const MAX_SITES = 3;

const router = useRouter();
const sitesApi = useSitesApi();

const sites = ref<MonitoredSite[]>([]);
const isLoading = ref(false);
const isCreating = ref(false);
const runningId = ref<number | null>(null);
const error = ref<string | null>(null);
const formError = ref<string | null>(null);

const form = reactive({
  url: '',
  name: '',
  schedule_day: 3,
});

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
  isCreating.value = true;
  try {
    const response = await sitesApi.createSite({
      url: form.url,
      name: form.name || null,
      schedule_day: form.schedule_day,
    });
    if (response.success) {
      sites.value.push(response.data);
      form.url = '';
      form.name = '';
      form.schedule_day = 3;
    }
  } catch (err: any) {
    logger.error('Ошибка добавления сайта:', err);
    formError.value =
      err?.response?.data?.message || 'Не удалось добавить сайт';
  } finally {
    isCreating.value = false;
  }
};

const handleDayChange = async (site: MonitoredSite, event: Event) => {
  const value = Number((event.target as HTMLSelectElement).value);
  try {
    const response = await sitesApi.updateSite(site.id, { schedule_day: value });
    if (response.success) {
      site.schedule_day = response.data.schedule_day;
    }
  } catch (err) {
    logger.error('Ошибка обновления дня:', err);
  }
};

const handleActiveChange = async (site: MonitoredSite, event: Event) => {
  const value = (event.target as HTMLInputElement).checked;
  try {
    const response = await sitesApi.updateSite(site.id, { is_active: value });
    if (response.success) {
      site.is_active = response.data.is_active;
    }
  } catch (err) {
    logger.error('Ошибка обновления статуса:', err);
  }
};

const handleDelete = async (id: number) => {
  try {
    const response = await sitesApi.deleteSite(id);
    if (response.success) {
      sites.value = sites.value.filter((s) => s.id !== id);
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
    year: 'numeric',
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

.sites-header {
  margin-bottom: 2.5rem;
  text-align: center;
  padding: 2rem 0;
}

.sites-header h1 {
  font-size: 2.75rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
  color: #fff;
  letter-spacing: -0.5px;
}

.sites-subtitle {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.1rem;
  margin: 0 auto;
  max-width: 640px;
}

.error-state {
  color: #ff6b6b;
  text-align: center;
  margin-bottom: 1.5rem;
}

.sites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.site-card,
.add-card {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(30, 30, 50, 0.95) 100%);
  border: 1px solid rgba(100, 108, 255, 0.15);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-top {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.site-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #646cff 0%, #9c27b0 100%);
  border-radius: 14px;
  flex-shrink: 0;
}

.site-icon svg {
  width: 24px;
  height: 24px;
  color: #fff;
}

.site-info {
  flex: 1;
  min-width: 0;
}

.site-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 0.25rem 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.site-url {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.55);
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.icon-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  width: 34px;
  height: 34px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.5);
  transition: all 0.2s ease;
}

.icon-btn svg {
  width: 18px;
  height: 18px;
}

.icon-btn.danger:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.card-controls {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
}

.control {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  flex: 1;
}

.control-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.65);
}

.control-select,
.text-input {
  background: rgba(15, 15, 30, 0.8);
  border: 1px solid rgba(100, 108, 255, 0.2);
  border-radius: 10px;
  padding: 0.6rem 0.75rem;
  color: #fff;
  font-size: 0.9rem;
  font-family: inherit;
}

.control-select:focus,
.text-input:focus {
  outline: none;
  border-color: #646cff;
}

.control.toggle {
  flex: 0 0 auto;
  align-items: center;
  flex-direction: row;
  gap: 0.5rem;
}

.control.toggle input {
  width: 18px;
  height: 18px;
  accent-color: #646cff;
}

.card-meta {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.meta-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
}

.meta-row svg {
  width: 16px;
  height: 16px;
  color: #646cff;
}

.meta-scores {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.score {
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.2rem 0.55rem;
  border-radius: 8px;
}

.score.good {
  background: rgba(76, 175, 80, 0.15);
  color: #4caf50;
}

.score.average {
  background: rgba(255, 152, 0, 0.15);
  color: #ff9800;
}

.score.poor {
  background: rgba(244, 67, 54, 0.15);
  color: #f44336;
}

.score.neutral {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.6);
}

.card-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: auto;
}

.add-card {
  border-style: dashed;
  justify-content: flex-start;
}

.add-card h3 {
  margin: 0;
  color: #fff;
  font-size: 1.1rem;
}

.add-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-error {
  color: #ff6b6b;
  font-size: 0.85rem;
  margin: 0;
}

.hint {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 2rem;
}

@media (max-width: 768px) {
  .sites-container {
    padding: 1rem;
  }

  .sites-header h1 {
    font-size: 2rem;
  }
}
</style>
