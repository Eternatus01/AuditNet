import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./app/App.vue";
import router from "./app/router";
import "@/app/style.css";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia).use(router);

import { useAuthStore } from "@/features/auth/stores/auth";

const authStore = useAuthStore(pinia);
authStore.fetchProfile();

app.mount("#app");
