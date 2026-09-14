import { ref, onMounted } from 'vue'
import { tema } from '@/theme/tema'
import apiLoja from '@/services/apiLoja'

export function useHomeViewModel() {
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

  function adicionarAoCarrinho(produto: any) {
    apiLoja.post('/carrinho/itens', { produto_id: produto.id, quantidade: 1 }).catch(() => {})
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