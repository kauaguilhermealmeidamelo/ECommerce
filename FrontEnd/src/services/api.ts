import axios, { type InternalAxiosRequestConfig, type AxiosResponse, type AxiosError } from 'axios'

const viteEnv = (import.meta as unknown as { env: Record<string, any> }).env

const api = axios.create({
  baseURL: viteEnv.VITE_API_URL || 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = localStorage.getItem('admin_token')
  if (token && token !== 'undefined' && config.headers) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response: AxiosResponse) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_usuario')
      const prefixo = viteEnv.VITE_ADMIN_PATH || 'painel'
      window.location.href = `/${prefixo}/login`
    }
    return Promise.reject(error)
  }
)

export default api