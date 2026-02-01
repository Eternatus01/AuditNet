<template>
  <aside
    class="sidebar"
    :class="{ 'is-expanded': !collapsed, 'is-collapsed': collapsed }"
  >
    <div class="sidebar-header">
      <SidebarLogo :text="logoText" :collapsed="collapsed" />
      
      <IconButton
        class="toggle-btn"
        variant="ghost"
        size="md"
        :aria-label="collapsed ? 'Expand menu' : 'Collapse menu'"
        :aria-expanded="!collapsed"
        @click="emit('toggle')"
      >
        <IconLucideMenu v-if="collapsed" />
        <IconLucideChevronLeft v-else />
      </IconButton>
    </div>

    <SidebarNav :is-authenticated="isAuthenticated" />

    <div v-if="isAuthenticated" class="sidebar-footer">
      <div v-if="userName && !collapsed" class="user-name">
        {{ userName }}
      </div>
      <SidebarNavItem
        is-button
        label="Logout"
        aria-label="Logout from account"
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
import SidebarNav from "@/shared/ui/organisms/SidebarNav.vue";
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
  background-color: var(--sidebar-bg, #18181b);
  border-right: 1px solid var(--border-color, #27272a);
  display: flex;
  flex-direction: column;
  transition: width var(--transition-speed, 0.3s) cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 100;
  width: 280px;
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
  padding: 1.5rem 1.25rem;
  border-bottom: 1px solid var(--border-color, #27272a);
  gap: 0.75rem;
  min-width: 0;
  flex-shrink: 0;
}

.sidebar.is-collapsed .sidebar-header {
  flex-direction: column;
  gap: 1rem;
  padding: 1.5rem 0.5rem 2rem;
  align-items: center;
  position: relative;
}

.sidebar.is-collapsed .sidebar-header > * {
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}

.sidebar-footer {
  padding: 1.25rem;
  border-top: 1px solid var(--border-color, #27272a);
  margin-top: auto;
  min-width: 0;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.sidebar.is-collapsed .sidebar-footer {
  padding: 0.75rem;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
  padding: 0.625rem 1rem;
  text-align: center;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.15), rgba(124, 58, 237, 0.05));
  border: 1px solid rgba(124, 58, 237, 0.2);
  border-radius: var(--radius-md, 12px);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.toggle-btn {
  flex-shrink: 0;
}

.sidebar.is-collapsed :deep(.link-text),
.sidebar.is-collapsed :deep(.logo-text) {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

.sidebar :deep(.link-text),
.sidebar :deep(.logo-text) {
  transition: opacity 0.2s ease-in-out, width 0.2s ease-in-out;
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    max-width: 280px;
    transform: translateX(0);
  }

  .sidebar.is-collapsed {
    transform: translateX(-100%);
  }
}
</style>

