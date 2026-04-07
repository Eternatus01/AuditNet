<template>
  <header>
    <Sidebar
      :collapsed="isCollapsed"
      :is-authenticated="isLogged"
      :user-name="userName"
      logo-text="AuditNet"
      @toggle="toggleSidebar"
      @logout="signOut"
    />

    <!-- Overlay для мобилки -->
    <Transition name="overlay-fade">
      <div
        v-if="isMobile && !isCollapsed"
        class="mobile-overlay"
        @click="isCollapsed = true"
      />
    </Transition>

    <!-- Кнопка открытия меню на мобилке (видна только когда сайдбар закрыт) -->
    <button
      v-if="isMobile && isCollapsed"
      class="mobile-menu-btn"
      aria-label="Открыть меню"
      @click="isCollapsed = false"
    >
      <IconLucideMenu />
    </button>
  </header>
</template>

<script setup lang="ts">
import { computed, ref, provide, watch, onMounted, onUnmounted } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/auth";
import Sidebar from "./Sidebar.vue";
import IconLucideMenu from "~icons/lucide/menu";

const authStore = useAuthStore();
const router = useRouter();

const { isAuthenticated } = storeToRefs(authStore);

const isMobile = ref(false);
const isCollapsed = ref(typeof window !== 'undefined' && window.innerWidth <= 768);

const isLogged = isAuthenticated;
const userName = computed(() => authStore.user?.name ?? '');

const checkMobile = () => {
  const mobile = window.innerWidth <= 768;
  isMobile.value = mobile;
  if (mobile && !isCollapsed.value) {
    isCollapsed.value = true;
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const signOut = async () => {
  await authStore.logout();
  if (isMobile.value) isCollapsed.value = true;
  router.push('/dashboard');
};

provide('sidebarCollapsed', isCollapsed);

watch(isCollapsed, (collapsed) => {
  document.documentElement.style.setProperty(
    '--sidebar-current-width',
    collapsed ? '80px' : '260px'
  );
}, { immediate: true });
</script>

<style scoped>
header {
  position: relative;
}

.mobile-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 150;
  backdrop-filter: blur(2px);
}

.overlay-fade-enter-active,
.overlay-fade-leave-active {
  transition: opacity 0.25s ease;
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
  opacity: 0;
}

.mobile-menu-btn {
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 190;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--sidebar-bg, #18181b);
  border: 1px solid var(--border-color, #27272a);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  padding: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  transition: background 0.2s ease, border-color 0.2s ease;
}

.mobile-menu-btn:hover {
  background: var(--bg-tertiary, #27272a);
  border-color: var(--border-color-hover, #3f3f46);
}

.mobile-menu-btn svg {
  width: 20px;
  height: 20px;
}
</style>
