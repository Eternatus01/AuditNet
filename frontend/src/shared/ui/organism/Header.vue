<template>
  <header>
    <aside
      class="sidebar"
      :class="{ 'is-expanded': !isCollapsed, 'is-collapsed': isCollapsed }"
    >
      <!-- Шапка сайдбара -->
      <div class="sidebar-header">
        <div class="logo-area">
          <svg
            class="logo-icon"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
            <polyline points="2 17 12 22 22 17"></polyline>
            <polyline points="2 12 12 17 22 12"></polyline>
          </svg>
          <span class="logo-text">AuditNet {{ name }}</span>
        </div>
        <button class="toggle-btn" @click="toggleSidebar">
          <svg
            v-if="isCollapsed"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
      </div>

      <!-- Меню -->
      <nav class="nav-list">
        <!-- Группа для НЕ авторизованного пользователя -->
        <template v-if="!isLogged">
          <div class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: currentPath === 'register' }"
              @click.prevent="$router.push({ name: 'register' })"
            >
              <div class="nav-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="8.5" cy="7" r="4"></circle>
                  <line x1="20" y1="8" x2="20" y2="14"></line>
                  <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
              </div>
              <span class="link-text">Регистрация</span>
            </a>
          </div>

          <div class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: currentPath === 'login' }"
              @click.prevent="$router.push({ name: 'login' })"
            >
              <div class="nav-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                  <polyline points="10 17 15 12 10 7"></polyline>
                  <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
              </div>
              <span class="link-text">Войти</span>
            </a>
          </div>
        </template>

        <!-- Группа для авторизованного пользователя -->
        <template v-if="isLogged">
          <div class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: route.name === 'dashboard' }"
              @click.prevent="$router.push({ name: 'dashboard' })"
            >
              <div class="nav-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
              </div>
              <span class="link-text">Dashboard</span>
            </a>
          </div>
          <div class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: route.name === 'profile' }"
              @click.prevent="$router.push({ name: 'profile' })"
            >
              <div class="nav-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
              </div>
              <span class="link-text">Профиль</span>
            </a>
          </div>
        </template>
      </nav>

      <div class="sidebar-footer" v-if="isLogged">
        <div class="nav-item">
          <a href="#" class="nav-link" @click.prevent="signOut">
            <div class="nav-icon">
              <svg
                style="color: #ff6b6b"
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
            </div>
            <span class="link-text">Выход</span>
          </a>
        </div>
      </div>
    </aside>
  </header>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/auth";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const name = computed(() => authStore.user?.name);
const isLogged = computed(() => authStore.isAuthenticated)
const isCollapsed = ref(false);

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const signOut = async () => {
  await authStore.logout();
};
</script>

<style scoped>
.app-layout {
  display: flex;
  width: 100%;
  height: 100vh;
  overflow: hidden;
}
</style>
