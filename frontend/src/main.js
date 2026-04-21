import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
// import './style.css'; // Removed in favor of Tailwind CDN in index.html

const app = createApp(App);
app.use(router);
app.mount('#app');
