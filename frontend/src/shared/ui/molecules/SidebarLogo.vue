<template>
  <div class="sidebar-logo">
    <div class="logo-icon">
      <slot name="icon">
        <IconLucideLayers />
      </slot>
    </div>
    <span v-if="!collapsed" class="logo-text">
      <slot>{{ text }}</slot>
    </span>
  </div>
</template>

<script setup lang="ts">
import IconLucideLayers from "~icons/lucide/layers";

withDefaults(
  defineProps<{
    text?: string;
    collapsed?: boolean;
  }>(),
  {
    text: "AuditNet",
    collapsed: false,
  }
);
</script>

<style scoped>
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  min-width: 0;
  overflow: hidden;
}

/* В свернутом состоянии только иконка */
:deep(.sidebar.is-collapsed) .sidebar-logo {
  padding: 0;
  justify-content: center;
  flex: 0 0 auto;
}

.logo-icon {
  width: 28px;
  height: 28px;
  min-width: 28px;
  min-height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #646cff;
  flex-shrink: 0;
}

.logo-icon :deep(svg) {
  width: 100%;
  height: 100%;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.95);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>

