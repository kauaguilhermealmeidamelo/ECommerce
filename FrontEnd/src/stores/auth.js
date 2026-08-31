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
    // Preenchido só durante a etapa intermediária do login com 2FA —
    // nunca persistido, some assim que o token final é obtido ou a
    // página é recarregada.
    usuarioIdPendente2fa: null,
  }),

  getters: {
    autenticado: (state) => !!state.token && state.token !== 'undefined',
  },

  actions: {
    /**
     * Se a conta tiver autenticação em 2 fatores ligada, o backend não
     * devolve token aqui — devolve { requer_2fa: true, usuario_id }.
     * Retorna esse resultado pro componente decidir se mostra a tela de
     * "digite o código" (ver LoginView.vue).
     */
    async login(email, senha) {
      const { data } = await api.post('/auth/login', { email, senha })

      if (data.requer_2fa) {
        this.usuarioIdPendente2fa = data.usuario_id
        return { requer2fa: true }
      }

      this.definirSessao(data.token, data.usuario)
      return { requer2fa: false }
    },

    async verificarDoisFatores(codigo) {
      const { data } = await api.post('/auth/verificar-2fa', {
        usuario_id: this.usuarioIdPendente2fa,
        codigo,
      })

      this.usuarioIdPendente2fa = null
      this.definirSessao(data.token, data.usuario)
    },

    definirSessao(token, usuario) {
      this.token = token
      this.usuario = usuario

      localStorage.setItem('admin_token', token)
      localStorage.setItem('admin_usuario', JSON.stringify(usuario))
    },

    logout() {
      this.token = null
      this.usuario = null
      this.usuarioIdPendente2fa = null
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_usuario')
    },
  },
})