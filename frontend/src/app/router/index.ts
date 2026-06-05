import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import { watch } from "vue";
import { useAuthStore } from "@/features/auth/stores/auth";

const routes: RouteRecordRaw[] = [
  {
    path: "/",
    name: "home",
    redirect: "/dashboard",
  },
  {
    path: "/dashboard",
    name: "dashboard",
    component: () => import("@/features/dashboard/pages/Dashboard.vue"),
  },
  {
    path: "/comparison",
    name: "comparison",
    component: () => import("@/features/comparison/pages/Comparison.vue"),
  },
  {
    path: "/report/:token",
    name: "public-report",
    component: () => import("@/features/public-report/pages/PublicReport.vue"),
  },
  {
    path: "/comparison-report/:token",
    name: "public-comparison-report",
    component: () => import("@/features/comparison/pages/PublicComparisonReport.vue"),
  },
  {
    path: "/history",
    name: "history",
    component: () => import("@/features/history/pages/History.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/history/comparisons/:id",
    name: "comparison-detail",
    component: () => import("@/features/comparison/pages/ComparisonDetail.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/history/:id",
    name: "history-detail",
    component: () => import("@/features/history/pages/HistoryDetail.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/sites",
    name: "sites",
    component: () => import("@/features/sites/pages/MySites.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/analytics",
    name: "analytics",
    component: () => import("@/features/analytics/pages/Analytics.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/profile",
    name: "profile",
    component: () => import("@/features/profile/pages/Profile.vue"),
  },
  {
    path: "/terms",
    name: "terms",
    component: () => import("@/features/auth/pages/Terms.vue"),
  },
  {
    path: "/reset-password",
    name: "reset-password",
    component: () => import("@/features/auth/pages/ResetPassword.vue"),
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
    await new Promise<void>((resolve) => {
      const stop = watch(
        () => authStore.isProfileLoading,
        (loading) => {
          if (!loading) {
            stop();
            resolve();
          }
        }
      );
      setTimeout(() => { stop(); resolve(); }, 3000);
    });
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: "profile", query: { redirect: to.fullPath } });
  }

  return next();
});

export default router;
