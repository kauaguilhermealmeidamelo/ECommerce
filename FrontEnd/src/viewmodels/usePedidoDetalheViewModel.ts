import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import apiLoja from '@/services/apiLoja'

export function usePedidoDetalheViewModel() {
  const route = useRoute()
  const pedido = ref<any>(null)
  const carregando = ref(true)
  const erro = ref<string | null>(null)

  const formatarMoeda = (v: number) => 
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

  const formatarData = (d: string) => 
    new Date(d).toLocaleDateString('pt-BR')

  const etapasRastreio = computed(() => {
    if (!pedido.value) return []

    const status = pedido.value.status
    const ordem = ['pendente', 'pago', 'enviado', 'concluido']
    const indiceAtual = ordem.indexOf(status)

    return [
      { chave: 'pendente', rotulo: 'Pedido realizado' },
      { chave: 'pago', rotulo: 'Pagamento confirmado' },
      { chave: 'enviado', rotulo: 'Enviado', detalhe: pedido.value.enviado_em ? formatarData(pedido.value.enviado_em) : null },
      { chave: 'concluido', rotulo: 'Entregue' },
    ].map((etapa, i) => ({
      ...etapa,
      concluida: status !== 'cancelado' && i <= indiceAtual,
      atual: status !== 'cancelado' && i === indiceAtual,
    }))
  })

  async function carregar() {
    carregando.value = true
    erro.value = null
    try {
      const { data } = await apiLoja.get(`/minha-conta/pedidos/${route.params.id}`)
      pedido.value = data.data
    } catch (e: any) {
      erro.value = e.response?.status === 403
        ? 'Você não tem acesso a este pedido.'
        : 'Não foi possível carregar este pedido.'
    } finally {
      carregando.value = false
    }
  }

  return {
    pedido,
    carregando,
    erro,
    etapasRastreio,
    formatarMoeda,
    formatarData,
    carregar,
  }
}