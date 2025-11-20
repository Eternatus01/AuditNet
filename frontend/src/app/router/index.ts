import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import Home from '@/features/home/pages/Home.vue'
import Register from "@/features/auth/pages/Register.vue";
import Login from "@/features/auth/pages/Login.vue";

const routes: RouteRecordRaw[] = [
  { path: "/", name: "home", component: Home },
  { path: "/register", name: "register", component: Register },
  { path: "/login", name: "login", component: Login },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
