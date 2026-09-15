import { ref, computed, onMounted } from 'vue'
import apiLoja from '@/services/apiLoja'
import { useCarrinhoStore } from '@/stores/carrinho.store'
import { CarrinhoData, ItemCarrinho } from './useCheckoutViewModel'

// ...interfaces iguais...

export function useCarrinhoViewModel() {
  const carrinhoStore = useCarrinhoStore()
  const carrinho = ref<CarrinhoData | null>(null)
  const carregando = ref(true)

  const itens = computed<ItemCarrinho[]>(() => carrinho.value?.itens ?? [])

  const subtotal = computed<number>(() =>
    itens.value.reduce((soma, i) => soma + (i.quantidade * Number(i.preco_unitario ?? i.produto?.preco ?? 0)), 0)
  )

  const formatarMoeda = (v: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

  async function carregar() {
    carregando.value = true
    try {
      const { data } = await apiLoja.get('/carrinho')
      carrinho.value = data.data
    } catch {
      carrinho.value = null
    } finally {
      carregando.value = false
    }
  }

  async function alterarQuantidade(item: ItemCarrinho, novaQuantidade: number) {
    if (novaQuantidade < 1) return
    try {
      const { data } = await apiLoja.patch(`/carrinho/itens/${item.id}`, { quantidade: novaQuantidade })
      carrinho.value = data.data
      carrinhoStore.carregarQuantidade()
    } catch {
      // mantém o valor atual se a troca falhar (ex: estoque insuficiente)
    }
  }

  async function remover(item: ItemCarrinho) {
    try {
      const { data } = await apiLoja.delete(`/carrinho/itens/${item.id}`)
      carrinho.value = data.data
      carrinhoStore.carregarQuantidade()
    } catch {
      // ignora se falhar
    }
  }

  onMounted(carregar)

  return {
    carregando,
    itens,
    subtotal,
    formatarMoeda,
    alterarQuantidade,
    remover,
  }
}