import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

export interface Cliente {
  id: number | string
  nome: string
  email: string
  telefone?: string
  total_pedidos: number
  total_gasto: number
  desde: string
  [key: string]: any
}

export function useClientesViewModel() {
  const clientes = ref<Cliente[]>([])
  const carregando = ref(true)
  const erro = ref<string | null>(null)

  const busca = ref('')
  const perfilFiltro = ref('Todos')
  const filtroAberto = ref(false)
  const buscaPendente = ref('')
  const perfilPendente = ref('Todos')

  const formatarMoeda = (v: number) => 
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

  const formatarData = (d: string) => 
    new Date(d).toLocaleDateString('pt-BR')

  const inicial = (nome: string) => 
    (nome ?? '?').trim().charAt(0).toUpperCase()

  function perfil(cliente: Cliente) {
    const diasDesdeCadastro = (Date.now() - new Date(cliente.desde).getTime()) / 86400000
    if (diasDesdeCadastro <= 30) return { rotulo: 'Novo', badge: 'badge--success' }
    if (cliente.total_gasto >= 2000) return { rotulo: 'VIP', badge: 'badge--info' }
    return { rotulo: 'Regular', badge: 'badge--azul' }
  }

  const contagem = computed(() => ({
    vip: clientes.value.filter((c) => perfil(c).rotulo === 'VIP').length,
    regular: clientes.value.filter((c) => perfil(c).rotulo === 'Regular').length,
    novo: clientes.value.filter((c) => perfil(c).rotulo === 'Novo').length,
  }))

  const filtrados = computed(() => 
    clientes.value.filter((c) => {
      const q = busca.value.toLowerCase()
      const buscaOk = c.nome.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
      const perfilOk = perfilFiltro.value === 'Todos' || perfil(c).rotulo === perfilFiltro.value
      return buscaOk && perfilOk
    })
  )

  function aplicarFiltro() {
    busca.value = buscaPendente.value
    perfilFiltro.value = perfilPendente.value
    filtroAberto.value = false
  }

  function limparFiltro() {
    busca.value = ''
    perfilFiltro.value = 'Todos'
    buscaPendente.value = ''
    perfilPendente.value = 'Todos'
    filtroAberto.value = false
  }

  async function carregar() {
    carregando.value = true
    erro.value = null
    try {
      const { data } = await api.get('/admin/clientes')
      clientes.value = data.data
    } catch {
      erro.value = 'Não foi possível carregar os clientes.'
    } finally {
      carregando.value = false
    }
  }

  onMounted(carregar)

  return {
    clientes,
    carregando,
    erro,
    busca,
    perfilFiltro,
    filtroAberto,
    buscaPendente,
    perfilPendente,
    contagem,
    filtrados,
    formatarMoeda,
    formatarData,
    inicial,
    perfil,
    aplicarFiltro,
    limparFiltro,
    carregar,
  }
}