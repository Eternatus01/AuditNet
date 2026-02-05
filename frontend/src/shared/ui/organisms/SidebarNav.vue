<template>
  <nav class="sidebar-nav">
    <template v-if="!isAuthenticated">
      <SidebarNavItem
        to="/register"
        label="Регистрация"
        aria-label="Регистрация"
        route-name="register"
      >
        <template #icon>
          <IconLucideUserPlus />
        </template>
      </SidebarNavItem>

      <SidebarNavItem
        to="/login"
        label="Войти"
        aria-label="Войти"
        route-name="login"
      >
        <template #icon>
          <IconLucideLogIn />
        </template>
      </SidebarNavItem>
    </template>

    <template v-if="isAuthenticated">
      <SidebarNavItem
        to="/dashboard"
        label="Главная"
        aria-label="Главная"
        route-name="dashboard"
      >
        <template #icon>
          <IconLucideBarChart3 />
        </template>
      </SidebarNavItem>

      <SidebarNavItem
        to="/history"
        label="История"
        aria-label="История"
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

      <SidebarNavItem
        to="/profile"
        label="Профиль"
        aria-label="Профиль"
        route-name="profile"
      >
        <template #icon>
          <IconLucideUser />
        </template>
      </SidebarNavItem>
    </template>
  </nav>
</template>

<script setup lang="ts">
import SidebarNavItem from "@/shared/ui/molecules/SidebarNavItem.vue";
import IconLucideUserPlus from "~icons/lucide/user-plus";
import IconLucideLogIn from "~icons/lucide/log-in";
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

/* В свернутом состоянии центрируем элементы */
:deep(.sidebar.is-collapsed) .sidebar-nav {
  align-items: center;
}
</style>

