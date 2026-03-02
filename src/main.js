import { createApp } from 'vue'
import { createPinia } from 'pinia'
import "./assets/style.css"  
import App from './App.vue'
import router from "./router"
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const pinia = createPinia()

createApp(App)
  .use(pinia)
  .use(router)
  .use(Toast, {
    position: POSITION.TOP_RIGHT,
    timeout: 3000,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false
  })
  .mount("#app")