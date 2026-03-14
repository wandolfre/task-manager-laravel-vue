/**
 * Application Entry Point
 *
 * Bootstraps the Vue 3 app with Pinia state management and Vue Router.
 * Mounts the root App component to the #app element in the Blade template.
 */
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router/index.js';
import App from './App.vue';
import '../css/app.css';

const app = createApp(App);

app.use(createPinia());
app.use(router);

app.mount('#app');
