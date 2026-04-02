import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./app/App.vue";
import router from "./app/router";
import "@/app/style.css";
import { useAuthStore } from "@/features/auth/stores/auth";
import { logger } from "@/shared/utils/logger";

const app = createApp(App);
const pinia = createPinia();

app.config.errorHandler = (err, instance, info) => {
  logger.error("Global error:", err, instance, info);
};

app.use(pinia).use(router);

const authStore = useAuthStore();
authStore.fetchProfile();

app.mount("#app");
