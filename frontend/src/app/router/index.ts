import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import Home from "@/features/home/pages/Home.vue";
import Register from "@/features/auth/pages/Register.vue";
import Login from "@/features/auth/pages/Login.vue";
import Dashboard from "@/features/dashboard/pages/Dashboard.vue";
import Profile from '@/features/profile/pages/Profile.vue'
import { useAuthStore } from "@/features/auth/stores/auth";

const routes: RouteRecordRaw[] = [
  { path: "/", name: "home", component: Home },
  {
    path: "/register",
    name: "register",
    component: Register,
    meta: { guestOnly: true },
  },
  {
    path: "/login",
    name: "login",
    component: Login,
    meta: { guestOnly: true },
  },
  {
    path: "/dashboard",
    name: "dashboard",
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: "/profile",
    name: "profile",
    component: Profile,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: "login", query: { redirect: to.fullPath } });
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: "home" });
  }

  return next();
});

export default router;
