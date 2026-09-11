import { ref, onMounted } from 'vue'
import api from '@/services/api'

export interface Categoria {
    id: number | string
    nome: string
    slug: string
    categoria_pai_id?: number | string | null
    filhos?: Categoria[]
    [key: string]: any
}

export function useCategoriasViewModel() {
    const arvore = ref<Categoria[]>([])
    const categoriasFlat = ref<Categoria[]>([])
    const carregando = ref(true)
    const salvando = ref(false)
    const erro = ref<string | null>(null)
    const categoriaPaiSelecionada = ref<Categoria | null>(null)

    const form = ref({
        nome: '',
        slug: '',
        categoria_pai_id: null as number | string | null,
    })

    function gerarSlug() {
        form.value.slug = form.value.nome
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // remove acentos
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
    }

    // Calcula quantos níveis de indentação mostrar no <select>
    function profundidade(categoria: Categoria): number {
        let nivel = 0
        let atual: Categoria | undefined = categoria

        while (atual?.categoria_pai_id) {
            nivel++
            atual = categoriasFlat.value.find((c) => c.id === atual?.categoria_pai_id)
            if (!atual) break
        }

        return nivel
    }

    function prepararSubcategoria(categoriaPai: Categoria) {
        categoriaPaiSelecionada.value = categoriaPai
        form.value.categoria_pai_id = categoriaPai.id
        form.value.nome = ''
        form.value.slug = ''
        erro.value = null
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    function limparPai() {
        categoriaPaiSelecionada.value = null
        form.value.categoria_pai_id = null
    }

    async function carregar() {
        carregando.value = true
        erro.value = null
        try {
            const [{ data: dataArvore }, { data: dataFlat }] = await Promise.all([
                api.get('/admin/categorias/arvore'),
                api.get('/admin/categorias'),
            ])
            arvore.value = dataArvore.data
            categoriasFlat.value = dataFlat.data
        } catch {
            erro.value = 'Não foi possível carregar as categorias.'
        } finally {
            carregando.value = false
        }
    }

    async function criar() {
        salvando.value = true
        erro.value = null

        try {
            await api.post('/admin/categorias', form.value)
            form.value = { nome: '', slug: '', categoria_pai_id: null }
            categoriaPaiSelecionada.value = null
            await carregar()
        } catch (err: unknown) {
            const e = err as any
            const erros = e.response?.data?.errors as Record<string, string[]> | undefined
            erro.value = erros ? Object.values(erros)[0]?.[0] ?? 'Não foi possível criar a categoria.' : 'Não foi possível criar a categoria.'
        } finally {
            salvando.value = false
        }
    }

    onMounted(carregar)

    return {
        arvore,
        categoriasFlat,
        carregando,
        salvando,
        erro,
        categoriaPaiSelecionada,
        form,
        gerarSlug,
        profundidade,
        prepararSubcategoria,
        limparPai,
        carregar,
        criar,
    }
}