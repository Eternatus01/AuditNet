<template>
  <div class="sidebar-nav-item">
    <component
      :is="isButton ? 'button' : 'router-link'"
      :to="!isButton ? to : undefined"
      :class="['nav-link', { active: isActive, danger: variant === 'danger' }]"
      :aria-label="ariaLabel"
      @click="handleClick"
    >
      <div class="nav-icon">
        <slot name="icon" />
      </div>
      <span class="link-text">
        <slot>{{ label }}</slot>
      </span>
    </component>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";

const props = withDefaults(
  defineProps<{
    to?: string;
    label?: string;
    ariaLabel?: string;
    isButton?: boolean;
    variant?: "default" | "danger";
    routeName?: string;
  }>(),
  {
    to: "/",
    label: "",
    ariaLabel: "",
    isButton: false,
    variant: "default",
    routeName: "",
  }
);

const emit = defineEmits<{
  click: [];
}>();

const route = useRoute();

const isActive = computed(() => {
  if (props.isButton) return false;
  return props.routeName ? route.name === props.routeName : false;
});

const handleClick = (event: Event) => {
  if (props.isButton) {
    event.preventDefault();
    emit("click");
  }
};
</script>

<style scoped>
.sidebar-nav-item {
  width: 100%;
  min-width: 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.8125rem 1.25rem;
  color: var(--text-secondary, rgba(255, 255, 255, 0.65));
  text-decoration: none;
  border-radius: var(--radius-md, 14px);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
  font-size: 1.125rem;
  font-weight: 500;
  min-width: 0;
  overflow: hidden;
  position: relative;
}

.nav-link:hover {
  background-color: var(--bg-tertiary, rgba(39, 39, 42, 0.6));
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
  transform: translateX(2px);
}

.nav-link.active {
  background: transparent;
  color: var(--text-primary, rgba(255, 255, 255, 0.92));
}

.nav-link.danger .nav-icon {
  color: var(--danger-color, #ef4444);
}

.nav-link.danger:hover {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--danger-color, #ef4444);
}

.nav-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.nav-icon :deep(svg) {
  width: 100%;
  height: 100%;
  stroke-width: 2;
}

.link-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

:deep(.sidebar.is-collapsed) .nav-link {
  justify-content: center;
  padding: 0.75rem;
}

:deep(.sidebar.is-collapsed) .nav-icon {
  margin: 0;
}
</style>

