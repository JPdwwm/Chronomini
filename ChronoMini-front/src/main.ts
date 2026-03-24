import './assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import persistedState from 'pinia-plugin-persistedstate';
import App from './App.vue';
import router from './router';
import Vue3Toastify, { type ToastContainerOptions } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';


/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import specific icons */
import { faEye, faEyeSlash, faUserSecret, faChild,faPlay, faStop, faDownload, faPause, faPen, faCheck, faBan, faSpinner,
          faPlus,faTrash, faClockRotateLeft, faCalendar, faArrowRight, faArrowLeft, faClock, faTimes, faCalculator,
           faCheckCircle, faCopy, faArrowsLeftRight,faFilePdf,faRotateLeft, faLock,faUsers,faEnvelope,faEdit } from '@fortawesome/free-solid-svg-icons'
import { faYoutube, faInstagram, faLinkedinIn } from '@fortawesome/free-brands-svg-icons'

const pinia = createPinia();
pinia.use(persistedState);

const app = createApp(App);
app.component('font-awesome-icon', FontAwesomeIcon)
app.use(pinia); 
app.use(router);

// Configuration des toasts
app.use(Vue3Toastify, {
  autoClose: 3000,
  clearOnUrlChange: false,
  containerId: "toast-container",
  hideProgressBar: false,
  newestOnTop: true,
  position: "top-right",
  theme: "light",
} as ToastContainerOptions)

/* add icons to the library */
library.add(faUserSecret, faEye, faEyeSlash,faChild, faPlay, faStop, faDownload, faYoutube, faInstagram, faLinkedinIn, faPause, faPen, faCheck, faBan, faSpinner, faPlus, faTrash,faClockRotateLeft,faCalendar,faArrowRight, faArrowLeft, faClock, faTimes, faCalculator, faCheckCircle, faCopy, faArrowsLeftRight,faFilePdf,faRotateLeft, faLock,faUsers,faEnvelope,faEdit);

app.mount('#app');
