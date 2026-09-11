import { ref, onMounted } from 'vue'
import apiLoja from '@/services/apiLoja'

export function useMeusPedidosViewModel() {
  const pedidos = ref<any[]>([])
  const carregando = ref(true)
  const erro = ref<string | null>(null)

  const formatarMoeda = (v: number) => 
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

  const formatarData = (d: string) => 
    new Date(d).toLocaleDateString('pt-BR')

  const rotulos: Record<string, string> = {
    pendente: 'Aguardando pagamento',
    pago: 'Pago',
    enviado: 'Enviado',
    concluido: 'Concluído',
    cancelado: 'Cancelado',
  }

  const classes: Record<string, string> = {
    pendente: 'badge--warning',
    pago: 'badge--success',
    enviado: 'badge--info',
    concluido: 'badge--success',
    cancelado: 'badge--danger',
  }

  const rotuloStatus = (s: string) => rotulos[s] ?? s
  const classeStatus = (s: string) => classes[s] ?? 'badge--neutral'

  async function carregar() {
    carregando.value = true
    erro.value = null
    try {
      const { data } = await apiLoja.get('/minha-conta/pedidos')
      pedidos.value = data.data
    } catch {
      erro.value = 'Não foi possível carregar seus pedidos agora.'
    } finally {
      carregando.value = false
    }
  }

  onMounted(carregar)

  return {
    pedidos,
    carregando,
    erro,
    formatarMoeda,
    formatarData,
    rotuloStatus,
    classeStatus,
  }
}