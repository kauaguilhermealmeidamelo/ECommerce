import { defineStore } from 'pinia'
import api from '@/services/api'

// Função auxiliar segura para ler o localStorage sem quebrar com "undefined" ou dados corrompidos
function safeJSONParse(key) {
  const item = localStorage.getItem(key)
  if (!item || item === 'undefined' || item === 'null') {
    return null
  }
  try {
    return JSON.parse(item)
  } catch (e) {
    localStorage.removeItem(key)
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('admin_token') !== 'undefined' ? localStorage.getItem('admin_token') : null,
    usuario: safeJSONParse('admin_usuario'),
  }),

  getters: {
    autenticado: (state) => !!state.token && state.token !== 'undefined',
  },

  actions: {
    async login(email, senha) {
      const { data } = await api.post('/auth/login', { email, senha })

      this.token = data.token
      this.usuario = data.usuario

      localStorage.setItem('admin_token', data.token)
      localStorage.setItem('admin_usuario', JSON.stringify(data.usuario))
    },

    logout() {
      this.token = null
      this.usuario = null
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_usuario')
    },
  },
})