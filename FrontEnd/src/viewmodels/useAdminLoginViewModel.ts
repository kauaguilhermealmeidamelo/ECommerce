import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export function useAdminLoginViewModel() {
  const router = useRouter()
  // Usamos a store do Admin, não a de clientes!
  const auth = useAuthStore()

  const form = ref({
    email: '',
    senha: ''
  })
  
  const erro = ref<string | null>(null)
  const carregando = ref(false)

  async function login() {
    erro.value = null
    carregando.value = true

    try {
      await auth.login(form.value.email, form.value.senha)
      // Após sucesso, joga direto pro painel admin
      router.push({ name: 'dashboard' }) 
    } catch (err: unknown) {
      const e = err as any
      const mensagem = e.response?.data?.message || e.response?.data?.error
      erro.value = mensagem ?? 'Credenciais inválidas. Verifique seu e-mail e senha.'
    } finally {
      carregando.value = false
    }
  }

  return {
    form,
    erro,
    carregando,
    login
  }
}