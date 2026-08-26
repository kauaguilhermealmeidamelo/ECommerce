import axios from 'axios'

// VITE_API_URL aponta pro backend Laravel de cada cliente.
// Cada deploy do admin define isso no seu .env — é o único ponto
// de configuração por loja que essa camada precisa saber.
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token')
  if (token && token !== 'undefined') {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_usuario')
      const prefixo = import.meta.env.VITE_ADMIN_PATH || 'painel'
      window.location.href = `/${prefixo}/login`
    }
    return Promise.reject(error)
  }
)

export default api