import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./app/App.vue";
import router from "./app/router";
import "@/app/style.css";
import { useAuthStore } from "@/features/auth/stores/auth";

const app = createApp(App);
const pinia = createPinia();

app.config.errorHandler = (err, instance, info) => {
  console.error("Global error:", err);
  console.error("Component:", instance);
  console.error("Error info:", info);
};

app.use(pinia).use(router);

const authStore = useAuthStore();
authStore.fetchProfile();

app.mount("#app");
