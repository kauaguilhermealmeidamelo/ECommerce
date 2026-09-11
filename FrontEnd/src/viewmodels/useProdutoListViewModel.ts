// src/viewmodels/useProdutoListViewModel.ts
import { ref, computed, onMounted } from 'vue'
import { produtoService } from '@/services/produto.service'
import type { Produto } from '@/types/produto.types'

export function useProdutoListViewModel() {
    const produtos = ref<Produto[]>([])
    const carregando = ref(false)
    const erro = ref<string | null>(null)
    const filtroTexto = ref('')

    const produtosFiltrados = computed<Produto[]>(() =>
        produtos.value.filter((p: Produto) => p.nome.toLowerCase().includes(filtroTexto.value.toLowerCase()))
    )

    const produtosComEstoqueBaixo = computed(() =>
        produtos.value.filter((p: Produto) => p.estoque > 0 && p.estoque <= 3)
    )

    async function carregar(): Promise<void> {
        carregando.value = true
        erro.value = null
        try {
            produtos.value = await produtoService.listar()
        } catch {
            erro.value = 'Não foi possível carregar os produtos.'
        } finally {
            carregando.value = false
        }
    }

    onMounted(carregar)

    return {
        carregando,
        erro,
        filtroTexto,
        produtosFiltrados,
        produtosComEstoqueBaixo,
        recarregar: carregar,
    }
}