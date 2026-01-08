<template>
  <Transition name="fade" mode="out-in">
    <div v-if="!isInitializing" key="app" class="main-container">
      <Header />
      <main :class="{ 'with-sidebar': true, 'sidebar-collapsed': sidebarCollapsed }">
        <router-view v-slot="{ Component }">
          <Transition name="fade" mode="out-in">
            <component :is="Component" />
          </Transition>
        </router-view>
      </main>
    </div>
    <div v-else key="loading" class="app-loading">
      <div class="loading-spinner"></div>
      <p>Загрузка...</p>
    </div>
  </Transition>
</template>

<script lang="ts" setup>
import { inject, ref, watch, onMounted, type Ref } from "vue";
import { useAuthStore } from "@/features/auth/stores/auth";
import Header from "../shared/ui/organism/Header.vue";

const sidebarCollapsed = inject<Ref<boolean>>('sidebarCollapsed', { value: false } as Ref<boolean>);
const authStore = useAuthStore();
const isInitializing = ref(true);

const stopWatchingProfile = watch(
  () => authStore.isProfileLoading,
  (isLoading) => {
    if (!isLoading) {
      setTimeout(() => {
        isInitializing.value = false;
        stopWatchingProfile();
      }, 100);
    }
  },
  { immediate: true }
);

onMounted(() => {
  setTimeout(() => {
    if (isInitializing.value) {
      isInitializing.value = false;
    }
  }, 3000);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.app-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #0d0d0d 0%, #1a1a2e 100%);
  color: white;
}

.app-loading .loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top-color: #646cff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.app-loading p {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

