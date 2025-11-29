import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/auth";
import { watch } from "vue";
import { logger } from "@/shared/utils/logger";

// Lazy loading для всех роутов
const routes: RouteRecordRaw[] = [
  {
    path: "/",
    name: "home",
    component: () => import("@/features/home/pages/Home.vue"),
  },
  {
    path: "/register",
    name: "register",
    component: () => import("@/features/auth/pages/Register.vue"),
    meta: { guestOnly: true },
  },
  {
    path: "/login",
    name: "login",
    component: () => import("@/features/auth/pages/Login.vue"),
    meta: { guestOnly: true },
  },
  {
    path: "/dashboard",
    name: "dashboard",
    component: () => import("@/features/dashboard/pages/Dashboard.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/history",
    name: "history",
    component: () => import("@/features/history/pages/History.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/history/:id",
    name: "history-detail",
    component: () => import("@/features/history/pages/HistoryDetail.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/profile",
    name: "profile",
    component: () => import("@/features/profile/pages/Profile.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/:pathMatch(.*)*",
    name: "not-found",
    component: () => import("@/features/error/pages/NotFound.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore();
  const AUTH_TIMEOUT_MS = 5000;
  
  // Ждем завершения загрузки профиля с помощью Promise + watch
  if (authStore.isProfileLoading) {
    await new Promise<void>((resolve) => {
      let unwatch: (() => void) | null = null;
      
      // Таймаут для предотвращения бесконечного ожидания
      const timeoutId = setTimeout(() => {
        if (unwatch) unwatch();
        logger.warn("Auth profile loading timeout");
        resolve();
      }, AUTH_TIMEOUT_MS);

      // Отслеживаем изменение состояния загрузки
      unwatch = watch(
        () => authStore.isProfileLoading,
        (isLoading) => {
          if (!isLoading) {
            clearTimeout(timeoutId);
            if (unwatch) unwatch();
            resolve();
          }
        },
        { immediate: true }
      );
    });
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: "login", query: { redirect: to.fullPath } });
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: "home" });
  }

  return next();
});

export default router;
