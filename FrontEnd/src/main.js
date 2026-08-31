import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

// 1. Importações do Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aplicarTema } from './theme/aplicarTema'

// 2. Instanciando o Vuetify
const vuetify = createVuetify({
  components,
  directives,
})

aplicarTema()

const app = createApp(App)

app.use(createPinia())
app.use(router)
// 3. Registrando o Vuetify no Vue
app.use(vuetify)

app.mount('#app')