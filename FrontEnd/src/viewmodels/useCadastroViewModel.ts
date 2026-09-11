import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useClienteAuthStore } from '@/stores/clienteAuth'

export function useCadastroViewModel() {
  const router = useRouter()
  const auth = useClienteAuthStore()

  const form = ref({
    name: '',
    email: '',
    telefone: '',
    senha: '',
    senha_confirmation: '',
  })
  
  const erro = ref<string | null>(null)
  const carregando = ref(false)

  async function cadastrar() {
    erro.value = null

    if (form.value.senha !== form.value.senha_confirmation) {
      erro.value = 'As senhas não coincidem.'
      return
    }

    carregando.value = true
    try {
      await auth.registrar(form.value)
      router.push({ name: 'home' })
    } catch (err: unknown) {
      const e = err as any
      const erros = e.response?.data?.errors as Record<string, string[]> | undefined
      erro.value = erros ? Object.values(erros)[0]?.[0] ?? 'Não foi possível criar sua conta. Confira os dados.' : 'Não foi possível criar sua conta. Confira os dados.'
    } finally {
      carregando.value = false
    }
  }

  return {
    form,
    erro,
    carregando,
    cadastrar,
  }
}