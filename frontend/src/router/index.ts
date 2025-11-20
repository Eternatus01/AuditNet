import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import Home from '@pages/Home.vue'
import Register from "@pages/Register.vue";
import Login from "@pages/Login.vue";

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
