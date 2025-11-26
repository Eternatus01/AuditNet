<template>
  <div class="not-found-container">
    <div class="not-found-content">
      <div class="error-code">
        <IconLucideAlertCircle class="icon-404" />
        <h1>404</h1>
      </div>
      
      <h2 class="error-title">Страница не найдена</h2>
      
      <p class="error-description">
        К сожалению, запрашиваемая страница не существует или была удалена.
        Проверьте правильность введённого адреса.
      </p>

      <div class="error-actions">
        <Button variant="primary" size="lg" @click="goHome">
          <template #icon-left>
            <IconLucideHome />
          </template>
          На главную
        </Button>
        <Button variant="secondary" size="lg" @click="goBack">
          <template #icon-left>
            <IconLucideArrowLeft />
          </template>
          Назад
        </Button>
      </div>

      <div class="helpful-links">
        <h3>Полезные ссылки:</h3>
        <ul>
          <li v-if="!isAuthenticated">
            <router-link to="/login">
              <IconLucideLogIn />
              Войти в систему
            </router-link>
          </li>
          <li v-if="isAuthenticated">
            <router-link to="/dashboard">
              <IconLucideLayoutDashboard />
              Dashboard
            </router-link>
          </li>
          <li v-if="isAuthenticated">
            <router-link to="/history">
              <IconLucideHistory />
              История аудитов
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/features/auth/stores/auth';
import { Button } from '@/shared/ui/atoms';
import IconLucideAlertCircle from '~icons/lucide/alert-circle';
import IconLucideHome from '~icons/lucide/home';
import IconLucideArrowLeft from '~icons/lucide/arrow-left';
import IconLucideLogIn from '~icons/lucide/log-in';
import IconLucideLayoutDashboard from '~icons/lucide/layout-dashboard';
import IconLucideHistory from '~icons/lucide/history';

const router = useRouter();
const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isAuthenticated);

const goHome = () => {
  router.push({ name: 'home' });
};

const goBack = () => {
  router.back();
};
</script>

<style scoped>
.not-found-container {
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, rgba(100, 108, 255, 0.05) 0%, rgba(255, 107, 107, 0.05) 100%);
}

.not-found-content {
  max-width: 600px;
  width: 100%;
  text-align: center;
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.error-code {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.icon-404 {
  width: 80px;
  height: 80px;
  color: #ff6b6b;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.error-code h1 {
  font-size: 8rem;
  font-weight: 900;
  margin: 0;
  background: linear-gradient(135deg, #646cff 0%, #ff6b6b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1;
}

.error-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: rgba(255, 255, 255, 0.9);
}

.error-description {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.6;
  margin-bottom: 2.5rem;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 3rem;
}

.helpful-links {
  background: rgba(26, 26, 26, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  backdrop-filter: blur(10px);
}

.helpful-links h3 {
  font-size: 1.2rem;
  margin-bottom: 1rem;
  color: rgba(255, 255, 255, 0.9);
}

.helpful-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.helpful-links a {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 1rem;
}

.helpful-links a:hover {
  background: rgba(100, 108, 255, 0.1);
  border-color: #646cff;
  color: #646cff;
  transform: translateX(5px);
}

.helpful-links svg {
  width: 20px;
  height: 20px;
}

@media (max-width: 768px) {
  .error-code h1 {
    font-size: 5rem;
  }

  .icon-404 {
    width: 60px;
    height: 60px;
  }

  .error-title {
    font-size: 1.5rem;
  }

  .error-description {
    font-size: 1rem;
  }

  .error-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
    justify-content: center;
  }

  .helpful-links {
    padding: 1.5rem;
  }
}
</style>

