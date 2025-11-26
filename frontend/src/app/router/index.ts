import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/auth";

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

  if (authStore.isProfileLoading) {
    const timeout = 5000;
    const startTime = Date.now();
    
    while (authStore.isProfileLoading && (Date.now() - startTime) < timeout) {
      await new Promise(resolve => setTimeout(resolve, 50));
    }
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
