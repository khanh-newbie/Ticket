import './assets/main.css'
import './assets/output.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import vue3GoogleLogin from "vue3-google-login";
import { createPinia } from "pinia";
const pinia = createPinia();
const app = createApp(App);

app.use(vue3GoogleLogin, {
  clientId: "239347645874-itv40jo84430r1q7huhg7ao6aaso9jr7.apps.googleusercontent.com",
});
app.use(pinia);
app.use(router);

app.mount('#app')
