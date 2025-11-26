<template>
  <nav class="sidebar-nav">
    <!-- Меню для НЕ авторизованного пользователя -->
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

    <!-- Меню для авторизованного пользователя -->
    <template v-if="isAuthenticated">
      <SidebarNavItem
        to="/dashboard"
        label="Dashboard"
        aria-label="Dashboard"
        route-name="dashboard"
      >
        <template #icon>
          <IconLucideLayoutDashboard />
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
import IconLucideLayoutDashboard from "~icons/lucide/layout-dashboard";
import IconLucideHistory from "~icons/lucide/history";
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

