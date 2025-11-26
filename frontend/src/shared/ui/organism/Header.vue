<template>
  <header>
    <Sidebar
      :collapsed="isCollapsed"
      :is-authenticated="isLogged"
      logo-text="AuditNet"
      :user-name="userName"
      @toggle="toggleSidebar"
      @logout="signOut"
    />
  </header>
</template>

<script setup lang="ts">
import { computed, ref, provide, watch } from "vue";
import { useAuthStore } from "@/features/auth/stores/auth";
import Sidebar from "@/shared/ui/organism/Sidebar.vue";

const authStore = useAuthStore();
const isLogged = computed(() => authStore.isAuthenticated);
const isCollapsed = ref(false);

const userName = computed(() => authStore.user?.name || "");

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const signOut = async () => {
  await authStore.logout();
};

// Предоставляем состояние sidebar для App.vue
provide('sidebarCollapsed', isCollapsed);

// Обновляем CSS переменную при изменении состояния
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
</style>
