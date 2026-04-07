<template>
  <nav class="sidebar-nav">
    <SidebarNavItem
      to="/dashboard"
      label="Аудит"
      aria-label="Аудит сайта"
      route-name="dashboard"
    >
      <template #icon>
        <IconLucideBarChart3 />
      </template>
    </SidebarNavItem>

    <template v-if="isAuthenticated">
      <SidebarNavItem
        to="/history"
        label="История"
        aria-label="История аудитов"
        route-name="history"
      >
        <template #icon>
          <IconLucideHistory />
        </template>
      </SidebarNavItem>

      <SidebarNavItem
        to="/analytics"
        label="Аналитика"
        aria-label="Аналитика"
        route-name="analytics"
      >
        <template #icon>
          <IconLucideTrendingUp />
        </template>
      </SidebarNavItem>
    </template>

    <SidebarNavItem
      to="/profile"
      :label="isAuthenticated ? 'Профиль' : 'Войти'"
      :aria-label="isAuthenticated ? 'Профиль' : 'Войти в аккаунт'"
      route-name="profile"
    >
      <template #icon>
        <IconLucideUser />
      </template>
    </SidebarNavItem>
  </nav>
</template>

<script setup lang="ts">
import SidebarNavItem from "@/shared/ui/molecules/SidebarNavItem.vue";
import IconLucideBarChart3 from "~icons/lucide/bar-chart-3";
import IconLucideHistory from "~icons/lucide/history";
import IconLucideTrendingUp from "~icons/lucide/trending-up";
import IconLucideUser from "~icons/lucide/user";

defineProps<{
  isAuthenticated: boolean;
}>();
</script>

<style scoped>
.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 0.5rem;
  min-width: 0;
}

:deep(.sidebar.is-collapsed) .sidebar-nav {
  align-items: center;
}
</style>
