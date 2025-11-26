<template>
  <div class="sidebar-nav-item">
    <component
      :is="isButton ? 'button' : 'router-link'"
      :to="!isButton ? to : undefined"
      :class="['nav-link', { active: isActive, danger: variant === 'danger' }]"
      :aria-label="ariaLabel"
      @click="isButton ? handleClick : undefined"
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

const handleClick = () => {
  emit("click");
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
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s ease-in-out;
  cursor: pointer;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
  font-size: 1rem;
  min-width: 0;
  overflow: hidden;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.95);
}

.nav-link.active {
  background-color: rgba(100, 108, 255, 0.15);
  color: #646cff;
}

.nav-link.danger .nav-icon {
  color: #ff6b6b;
}

.nav-link.danger:hover {
  background-color: rgba(255, 107, 107, 0.1);
  color: #ff6b6b;
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
}

.link-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

/* Стили для свернутого сайдбара */
:deep(.sidebar.is-collapsed) .nav-link {
  justify-content: center;
  padding: 0.75rem;
}

:deep(.sidebar.is-collapsed) .nav-icon {
  margin: 0;
}
</style>

