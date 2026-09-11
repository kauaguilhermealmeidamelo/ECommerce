import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export function useLoginViewModel() {
  const email = ref('')
  const senha = ref('')
  const erro = ref<string | null>(null)
  const carregando = ref(false)

  const router = useRouter()
  const auth = useAuthStore()

  async function entrar() {
    carregando.value = true
    erro.value = null

    try {
      await auth.login(email.value, senha.value)
      router.push({ name: 'dashboard' })
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
    entrar,
  }
}