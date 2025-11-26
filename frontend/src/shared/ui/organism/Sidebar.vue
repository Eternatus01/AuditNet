<template>
  <aside
    class="sidebar"
    :class="{ 'is-expanded': !collapsed, 'is-collapsed': collapsed }"
  >
    <!-- Шапка сайдбара -->
    <div class="sidebar-header">
      <SidebarLogo :text="logoText" :collapsed="collapsed" />
      
      <IconButton
        class="toggle-btn"
        variant="ghost"
        size="md"
        :aria-label="collapsed ? 'Развернуть меню' : 'Свернуть меню'"
        :aria-expanded="!collapsed"
        @click="emit('toggle')"
      >
        <IconLucideMenu v-if="collapsed" />
        <IconLucideChevronLeft v-else />
      </IconButton>
    </div>

    <!-- Навигация -->
    <SidebarNav :is-authenticated="isAuthenticated" />

    <!-- Футер с кнопкой выхода -->
    <div v-if="isAuthenticated" class="sidebar-footer">
      <div v-if="userName && !collapsed" class="user-name">
        {{ userName }}
      </div>
      <SidebarNavItem
        is-button
        label="Выход"
        aria-label="Выйти из аккаунта"
        variant="danger"
        @click="emit('logout')"
      >
        <template #icon>
          <IconLucideLogOut />
        </template>
      </SidebarNavItem>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { IconButton } from "@/shared/ui/atoms";
import SidebarLogo from "@/shared/ui/molecules/SidebarLogo.vue";
import SidebarNav from "@/shared/ui/organism/SidebarNav.vue";
import SidebarNavItem from "@/shared/ui/molecules/SidebarNavItem.vue";
import IconLucideMenu from "~icons/lucide/menu";
import IconLucideChevronLeft from "~icons/lucide/chevron-left";
import IconLucideLogOut from "~icons/lucide/log-out";

withDefaults(
  defineProps<{
    collapsed?: boolean;
    isAuthenticated?: boolean;
    logoText?: string;
    userName?: string;
  }>(),
  {
    collapsed: false,
    isAuthenticated: false,
    logoText: "AuditNet",
    userName: "",
  }
);

const emit = defineEmits<{
  toggle: [];
  logout: [];
}>();
</script>

<style scoped>
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  background-color: #1a1a1a;
  border-right: 1px solid #333;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease-in-out;
  z-index: 100;
  width: 260px;
  overflow-x: hidden;
  overflow-y: auto;
}

.sidebar.is-collapsed {
  width: 80px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #333;
  gap: 0.5rem;
  min-width: 0;
  flex-shrink: 0;
}

/* В свернутом состоянии делаем вертикальный layout */
.sidebar.is-collapsed .sidebar-header {
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.5rem 0.5rem 2rem;
  align-items: center;
  position: relative;
}

/* Убираем сжатие дочерних элементов в свернутом состоянии */
.sidebar.is-collapsed .sidebar-header > * {
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid #333;
  margin-top: auto;
  min-width: 0;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* В свернутом состоянии уменьшаем padding футера */
.sidebar.is-collapsed .sidebar-footer {
  padding: 0.5rem;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  padding: 0.5rem 1rem;
  text-align: center;
  background-color: rgba(100, 108, 255, 0.1);
  border-radius: 8px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.toggle-btn {
  flex-shrink: 0;
}

/* Скрываем текст в свернутом состоянии */
.sidebar.is-collapsed :deep(.link-text),
.sidebar.is-collapsed :deep(.logo-text) {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

/* Анимации */
.sidebar :deep(.link-text),
.sidebar :deep(.logo-text) {
  transition: opacity 0.2s ease-in-out, width 0.2s ease-in-out;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    max-width: 260px;
    transform: translateX(0);
  }

  .sidebar.is-collapsed {
    transform: translateX(-100%);
  }
}
</style>

