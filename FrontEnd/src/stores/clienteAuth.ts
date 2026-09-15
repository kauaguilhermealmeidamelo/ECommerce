import { defineStore } from 'pinia'
import apiLoja from '@/services/apiLoja'

interface ClienteUsuario {
  id: number | string
  name: string
  email: string
  telefone?: string
  [key: string]: any
}

interface ClienteAuthState {
  token: string | null
  usuario: ClienteUsuario | null
}

interface RegistroPayload {
  name: string
  email: string
  senha: string
  senha_confirmation: string
  telefone?: string
}

function safeJSONParse(key: string): any {
  const item = localStorage.getItem(key)
  if (!item || item === 'undefined' || item === 'null') return null
  try {
    return JSON.parse(item)
  } catch {
    localStorage.removeItem(key)
    return null
  }
}

export const useClienteAuthStore = defineStore('clienteAuth', {
  state: (): ClienteAuthState => ({
    token: localStorage.getItem('cliente_token') !== 'undefined' ? localStorage.getItem('cliente_token') : null,
    usuario: safeJSONParse('cliente_usuario'),
  }),

  getters: {
    autenticado: (state): boolean => !!state.token && state.token !== 'undefined',
    primeiroNome: (state): string => state.usuario?.name?.split(' ')[0] ?? '',
  },

  actions: {
    async login(email: string, senha: string): Promise<void> {
      const { data } = await apiLoja.post('/auth/login', { email, senha })
      this.definirSessao(data)
    },

    async registrar(payload: RegistroPayload): Promise<void> {
      const { data } = await apiLoja.post('/auth/registro', payload)
      this.definirSessao(data)
    },

    definirSessao(data: { token: string; usuario: ClienteUsuario }): void {
      this.token = data.token
      this.usuario = data.usuario
      localStorage.setItem('cliente_token', data.token)
      localStorage.setItem('cliente_usuario', JSON.stringify(data.usuario))
    },

    logout(): void {
      this.token = null
      this.usuario = null
      localStorage.removeItem('cliente_token')
      localStorage.removeItem('cliente_usuario')
      localStorage.removeItem('loja_sessao_id') // força uma sessão/carrinho novo de visitante
    },
  },
})