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
import { storeToRefs } from "pinia";
import { useAuthStore } from "@/features/auth/stores/auth";
import Sidebar from "@/shared/ui/organism/Sidebar.vue";

const authStore = useAuthStore();

// ✅ Используем storeToRefs для получения реактивных свойств
const { isAuthenticated, user } = storeToRefs(authStore);

const isCollapsed = ref(false);

// ✅ Computed для isLogged (он ссылается на computed из store, но можно упростить)
const isLogged = isAuthenticated;

// ✅ Computed для userName (есть логика: || "")
const userName = computed(() => user.value?.name || "");

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
