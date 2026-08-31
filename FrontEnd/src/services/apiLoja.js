import axios from 'axios'

// Mesma ideia do services/api.js do painel admin, mas com chave de token
// própria (cliente_token) — login de cliente e login de admin nunca devem
// compartilhar o mesmo token. Se a vitrine já tiver um api.js central,
// troque este arquivo pra reexportar aquele e só trocar a chave do token.
const apiLoja = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

apiLoja.interceptors.request.use((config) => {
  const token = localStorage.getItem('cliente_token')
  if (token && token !== 'undefined') {
    config.headers.Authorization = `Bearer ${token}`
  }

  // Reaproveita o mesmo X-Session-Id do carrinho — necessário pro merge
  // guest→autenticado funcionar mesmo em chamadas de conta/pedidos.
  let sessaoId = localStorage.getItem('loja_sessao_id')
  if (!sessaoId) {
    sessaoId = crypto.randomUUID()
    localStorage.setItem('loja_sessao_id', sessaoId)
  }
  config.headers['X-Session-Id'] = sessaoId

  return config
})

apiLoja.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('cliente_token')
      localStorage.removeItem('cliente_usuario')
    }
    return Promise.reject(error)
  }
)

export default apiLoja
