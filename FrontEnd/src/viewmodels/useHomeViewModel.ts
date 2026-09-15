import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { tema } from '@/theme/tema'
import apiLoja from '@/services/apiLoja'
import { useCarrinhoStore } from '@/stores/carrinho.store'

export function useHomeViewModel() {
  const router = useRouter()
  const carrinho = useCarrinhoStore()

  const categorias = ref<any[]>([])
  const carregando = ref(true)
  const maisVendidos = ref<any[]>([])
  const carregandoMaisVendidos = ref(true)

  function achatarFolhas(lista: any[]): any[] {
    const folhas: any[] = []
    for (const cat of lista) {
      const filhas = cat.filhas_recursivas ?? []
      if (filhas.length === 0) folhas.push(cat)
      else folhas.push(...achatarFolhas(filhas))
    }
    return folhas
  }

  async function carregarCategorias() {
    carregando.value = true
    try {
      const { data } = await apiLoja.get('/categorias/arvore')
      categorias.value = achatarFolhas(data.data)
    } catch {
      categorias.value = []
    } finally {
      carregando.value = false
    }
  }

  async function carregarMaisVendidos() {
    carregandoMaisVendidos.value = true
    try {
      const { data } = await apiLoja.get('/produtos/mais-vendidos')
      maisVendidos.value = data.data
    } catch {
      maisVendidos.value = []
    } finally {
      carregandoMaisVendidos.value = false
    }
  }

  async function adicionarAoCarrinho(produto: any) {
    // Produto com variação (tamanho etc.) precisa que o cliente escolha
    // a opção antes de adicionar — sem isso a API sempre recusa (422).
    // Em vez de falhar calado, leva pra página do produto.
    if (produto.variacoes?.length) {
      router.push({ name: 'produto', params: { id: produto.id } })
      return
    }

    await carrinho.adicionarItem(produto.id, 1)
  }

  onMounted(() => {
    carregarCategorias()
    carregarMaisVendidos()
  })

  return {
    tema,
    categorias,
    carregando,
    maisVendidos,
    carregandoMaisVendidos,
    adicionarAoCarrinho,
  }
}