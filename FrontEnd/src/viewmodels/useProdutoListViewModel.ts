// src/viewmodels/useProdutoListViewModel.ts
import { ref, computed, onMounted } from 'vue'
import { produtoService } from '@/services/produto.service'
import type { NovoProdutoPayload, Produto } from '@/types/produto.types'

export function useProdutoListViewModel() {
    const produtos = ref<Produto[]>([])
    const carregando = ref(false)
    const erro = ref<string | null>(null)
    const filtroTexto = ref('')
    const dialogAdicionar = ref(false)
    const salvando = ref(false)
    const novoProduto = ref<NovoProdutoPayload>({ nome: '', categoria: '', preco: 0, estoque: 0 })

    const produtosFiltrados = computed<Produto[]>(() =>
        produtos.value.filter((p: Produto) => {
            const termo = filtroTexto.value.toLowerCase()
            return p.nome.toLowerCase().includes(termo) || (p.categoria ?? '').toLowerCase().includes(termo)
        })
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

    async function salvarProduto(): Promise<void> {
        salvando.value = true
        erro.value = null
        try {
            await produtoService.criar(novoProduto.value)
            dialogAdicionar.value = false
            novoProduto.value = { nome: '', categoria: '', preco: 0, estoque: 0 }
            await carregar()
        } catch {
            erro.value = 'Não foi possível cadastrar o produto.'
        } finally {
            salvando.value = false
        }
    }

    async function deletarProduto(id: number): Promise<void> {
        if (!window.confirm('Deseja realmente excluir este produto?')) return
        try {
            await produtoService.remover(id)
            await carregar()
        } catch {
            erro.value = 'Não foi possível excluir o produto.'
        }
    }

    onMounted(carregar)

    return {
        carregando,
        erro,
        filtroTexto,
        dialogAdicionar,
        salvando,
        novoProduto,
        produtosFiltrados,
        produtosComEstoqueBaixo,
        recarregar: carregar,
        salvarProduto,
        deletarProduto,
    }
}