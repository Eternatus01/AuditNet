<template>
  <div v-if="!isInitializing" class="main-container">
    <Header />
    <main :class="{ 'with-sidebar': true, 'sidebar-collapsed': sidebarCollapsed }">
      <router-view :key="$route.fullPath" />
    </main>
  </div>
  <div v-else class="app-loading">
    <LoadingState text="Загрузка..." size="lg" />
  </div>
</template>

<script lang="ts" setup>
import { inject, ref, watch, type Ref } from "vue";
import { useAuthStore } from "@/features/auth/stores/auth";
import Header from "../shared/ui/organisms/Header.vue";
import LoadingState from "@/shared/ui/molecules/LoadingState.vue";

const sidebarCollapsed = inject<Ref<boolean>>('sidebarCollapsed', { value: false } as Ref<boolean>);
const authStore = useAuthStore();
const isInitializing = ref(authStore.isProfileLoading);

if (authStore.isProfileLoading) {
  const stop = watch(
    () => authStore.isProfileLoading,
    (loading) => {
      if (!loading) {
        isInitializing.value = false;
        stop();
      }
    }
  );
  setTimeout(() => { isInitializing.value = false; }, 3000);
}
</script>

<style scoped>
.app-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #0d0d0d 0%, #1a1a2e 100%);
}
</style>

