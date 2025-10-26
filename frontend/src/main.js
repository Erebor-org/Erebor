import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import './index.css'
import './config/axios' // ✅ Import axios configuration to add JWT token to requests


const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
