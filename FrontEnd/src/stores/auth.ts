import { defineStore } from 'pinia'
import api from '@/services/api'

interface Usuario {
  id: number | string
  nome: string
  email: string
  [key: string]: any
}

interface AuthState {
  token: string | null
  usuario: Usuario | null
  usuarioIdPendente2fa: number | string | null
}

// Função auxiliar segura para ler o localStorage sem quebrar com "undefined" ou dados corrompidos
function safeJSONParse(key: string): any {
  const item = localStorage.getItem(key)
  if (!item || item === 'undefined' || item === 'null') {
    return null
  }
  try {
    return JSON.parse(item)
  } catch {
    localStorage.removeItem(key)
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('admin_token') !== 'undefined' ? localStorage.getItem('admin_token') : null,
    usuario: safeJSONParse('admin_usuario'),
    usuarioIdPendente2fa: null,
  }),

  getters: {
    autenticado: (state): boolean => !!state.token && state.token !== 'undefined',
  },

  actions: {
    async login(email: string, senha: string): Promise<{ requer2fa: boolean }> {
      const { data } = await api.post('/auth/login', { email, senha })

      if (data.requer_2fa) {
        this.usuarioIdPendente2fa = data.usuario_id
        return { requer2fa: true }
      }

      this.definirSessao(data.token, data.usuario)
      return { requer2fa: false }
    },

    async verificarDoisFatores(codigo: string): Promise<void> {
      const { data } = await api.post('/auth/verificar-2fa', {
        usuario_id: this.usuarioIdPendente2fa,
        codigo,
      })

      this.usuarioIdPendente2fa = null
      this.definirSessao(data.token, data.usuario)
    },

    definirSessao(token: string, usuario: Usuario): void {
      this.token = token
      this.usuario = usuario

      localStorage.setItem('admin_token', token)
      localStorage.setItem('admin_usuario', JSON.stringify(usuario))
    },

    logout(): void {
      this.token = null
      this.usuario = null
      this.usuarioIdPendente2fa = null
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_usuario')
    },
  },
})