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
import { useRouter } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/auth";
import Sidebar from "./Sidebar.vue";

const authStore = useAuthStore();
const router = useRouter();

const { isAuthenticated, user } = storeToRefs(authStore);

const isCollapsed = ref(false);

const isLogged = isAuthenticated;

const userName = computed(() => user.value?.name || "");

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const signOut = async () => {
  await authStore.logout();
  router.push('/login');
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
</style>
