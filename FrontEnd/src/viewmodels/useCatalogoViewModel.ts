import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiLoja from '@/services/apiLoja'
import { useCarrinhoStore } from '@/stores/carrinho.store'

export interface CategoriaFolha {
  id: number | string
  nome: string
  filhas_recursivas?: CategoriaFolha[]
  [key: string]: any
}

export interface SecaoCategoria {
  categoria: CategoriaFolha
  produtos: any[]
  total: number
  carregando: boolean
}

export function useCatalogoViewModel() {
  const route = useRoute()
  const router = useRouter()
  const carrinho = useCarrinhoStore()

  const categoriasFolha = ref<CategoriaFolha[]>([])
  const carregandoCategorias = ref(true)

  // Aceita ?categoria=ID na URL além da seleção via chips
  const categoriaSelecionada = ref<number | null>(
    route.query.categoria ? Number(route.query.categoria) : null
  )
  const busca = ref<string>((route.query.busca as string) ?? '')

  const secoesPorCategoria = ref<SecaoCategoria[]>([])
  const produtosCategoriaUnica = ref<any[]>([])
  const produtosBusca = ref<any[]>([])

  function achatarFolhas(categorias: CategoriaFolha[]): CategoriaFolha[] {
    const folhas: CategoriaFolha[] = []
    for (const cat of categorias) {
      const filhas = cat.filhas_recursivas ?? []
      if (filhas.length === 0) {
        folhas.push(cat)
      } else {
        folhas.push(...achatarFolhas(filhas))
      }
    }
    return folhas
  }

  async function carregarCategorias() {
    carregandoCategorias.value = true
    try {
      const { data } = await apiLoja.get('/categorias/arvore')
      categoriasFolha.value = achatarFolhas(data.data)

      secoesPorCategoria.value = categoriasFolha.value.map((categoria) => ({
        categoria,
        produtos: [],
        total: 0,
        carregando: true,
      }))

      await Promise.all(
        secoesPorCategoria.value.map(async (secao) => {
          try {
            const { data: dataProdutos } = await apiLoja.get('/produtos', {
              params: { categoria_id: secao.categoria.id, por_pagina: 8 },
            })
            secao.produtos = dataProdutos.data
            secao.total = dataProdutos.data.length
          } catch {
            secao.produtos = []
          } finally {
            secao.carregando = false
          }
        })
      )
    } catch {
      categoriasFolha.value = []
    } finally {
      carregandoCategorias.value = false
    }
  }

  async function carregarCategoriaUnica(categoriaId: number) {
    try {
      const { data } = await apiLoja.get('/produtos', {
        params: { categoria_id: categoriaId, por_pagina: 60 },
      })
      produtosCategoriaUnica.value = data.data
    } catch {
      produtosCategoriaUnica.value = []
    }
  }

  async function carregarBusca(termo: string) {
    try {
      const { data } = await apiLoja.get('/produtos', { params: { por_pagina: 100 } })
      const termoBusca = termo.toLowerCase()
      produtosBusca.value = data.data.filter((p: any) =>
        p.nome.toLowerCase().includes(termoBusca)
      )
    } catch {
      produtosBusca.value = []
    }
  }

  function limparBusca() {
    busca.value = ''
  }

  // Produtos com variação (tamanho) não têm como escolher o tamanho
  // direto no card — leva pra página do produto. Sem variação, adiciona
  // direto via store (que já cuida de toast de sucesso/erro).
  async function adicionarAoCarrinho(produto: any) {
    if (produto.variacoes?.length) {
      router.push({ name: 'produto', params: { id: produto.id } })
      return
    }

    await carrinho.adicionarItem(produto.id, 1)
  }

  watch(categoriaSelecionada, (id) => {
    if (id) carregarCategoriaUnica(id)
  })

  watch(busca, (termo) => {
    if (termo) carregarBusca(termo)
  })

  onMounted(() => {
    carregarCategorias()
    if (busca.value) carregarBusca(busca.value)
    if (categoriaSelecionada.value) carregarCategoriaUnica(categoriaSelecionada.value)
  })

  return {
    categoriasFolha,
    carregandoCategorias,
    categoriaSelecionada,
    busca,
    secoesPorCategoria,
    produtosCategoriaUnica,
    produtosBusca,
    limparBusca,
    adicionarAoCarrinho,
  }
}