import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiLoja from '@/services/apiLoja'

export interface ItemCarrinho {
  id: number | string
  produto_id: number | string
  quantidade: number
  produto?: {
    nome: string
    preco: number
    [key: string]: any
  }
  [key: string]: any
}

export interface CarrinhoData {
  itens: ItemCarrinho[]
  subtotal: number
  [key: string]: any
}

export function useCheckoutViewModel() {
  const router = useRouter()

  const carrinho = ref<CarrinhoData | null>(null)
  const carregando = ref(true)
  const salvando = ref(false)
  const erro = ref<string | null>(null)

  // Dados do formulário de checkout
  const tipoEntrega = ref<'retirada' | 'local' | 'transportadora'>('retirada')
  const endereco = ref({
    cep: '',
    logradouro: '',
    numero: '',
    bairro: '',
    cidade: '',
    uf: '',
    complemento: '',
  })
  
  const freteCalculado = ref<number>(0)
  const opcoesFrete = ref<any[]>([])

  async function carregarCarrinho() {
    carregando.value = true
    try {
      const { data } = await apiLoja.get('/carrinho')
      carrinho.value = data.data
      if (!carrinho.value?.itens || carrinho.value.itens.length === 0) {
        router.push({ name: 'carrinho' })
      }
    } catch {
      erro.value = 'Não foi possível carregar os dados do carrinho.'
    } finally {
      carregando.value = false
    }
  }

  async function calcularFrete() {
    if (!endereco.value.cep || endereco.value.cep.length < 8) return
    try {
      const { data } = await apiLoja.post('/checkout/frete', {
        cep: endereco.value.cep,
        tipo: tipoEntrega.value,
      })
      opcoesFrete.value = data.data?.opcoes ?? []
      freteCalculado.value = data.data?.valor ?? 0
    } catch {
      erro.value = 'Erro ao calcular o frete para o CEP informado.'
    }
  }

  async function finalizarCheckout() {
    salvando.value = true
    erro.value = null
    try {
      const { data } = await apiLoja.post('/checkout/finalizar', {
        tipo_entrega: tipoEntrega.value,
        endereco: endereco.value,
        frete: freteCalculado.value,
      })
      
      const checkoutUrl = data.data?.checkout_url
      if (checkoutUrl) {
        window.location.href = checkoutUrl
      } else {
        router.push({ name: 'pedido-sucesso' })
      }
    } catch {
      erro.value = 'Não foi possível processar o pagamento no momento.'
    } finally {
      salvando.value = false
    }
  }

  onMounted(carregarCarrinho)

  return {
    carrinho,
    carregando,
    salvando,
    erro,
    tipoEntrega,
    endereco,
    freteCalculado,
    opcoesFrete,
    calcularFrete,
    finalizarCheckout,
  }
}