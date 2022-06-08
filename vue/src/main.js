import { createApp } from 'vue';
import App from './App.vue';
import Toast from 'vue-toastification';
import "vue-toastification/dist/index.css";
import ContextMenu from '@imengyu/vue3-context-menu';
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css';
import CKEditor from '@ckeditor/ckeditor5-vue';

export const app = createApp(App);
app.use(ContextMenu);
app.use(Toast);
app.use(CKEditor);
app.mount('#app');
