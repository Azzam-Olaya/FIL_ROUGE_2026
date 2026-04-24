import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import './style.css';

const app = createApp(App);
// Pinia — gestionnaire d'état partagé entre les composants navbar et le dashboard
app.use(createPinia());
app.use(router);
app.mount('#app');
