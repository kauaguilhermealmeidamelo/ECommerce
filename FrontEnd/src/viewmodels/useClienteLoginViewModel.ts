
declare global {
  interface ImportMeta {
    env: {
      VITE_API_URL?: string
      [key: string]: any
    }
  }
}

import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useClienteAuthStore } from '@/stores/clienteAuth'

export function useClienteLoginViewModel() {
  const router = useRouter()
  const auth = useClienteAuthStore()

  const email = ref('')
  const senha = ref('')
  const erro = ref<string | null>(null)
  const carregando = ref(false)

  const urlGoogle = computed(() => {
    const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
    return `${base}/auth/google`
  })

  async function entrar() {
    erro.value = null
    carregando.value = true
    try {
      await auth.login(email.value, senha.value)
      router.push({ name: 'home' })
    } catch {
      erro.value = 'E-mail ou senha inválidos.'
    } finally {
      carregando.value = false
    }
  }

  return {
    email,
    senha,
    erro,
    carregando,
    urlGoogle,
    entrar,
  }
}