import { createApp } from 'vue'
import '@/app/style.css'
import { createPinia } from 'pinia'
import App from './app/App.vue'
import router from './app/router/index'

createApp(App).use(createPinia()).use(router).mount('#app')
