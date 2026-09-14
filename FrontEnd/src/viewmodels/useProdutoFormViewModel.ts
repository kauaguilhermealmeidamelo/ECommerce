import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

export function useProdutoFormViewModel(
    id?: string | number | null | any,
    callbacks?: { salvo?: () => void; cancelado?: () => void },
) {
    const router = useRouter()
    const modoEdicao = computed(() => !!id)

    const produto = ref({
        nome: '',
        categoria_id: '',
        preco: null as number | null,
        estoque: 0,
        peso: null as number | null,
        altura: null as number | null,
        largura: null as number | null,
        comprimento: null as number | null,
        descricao: '',
    })

    const variacoes = ref<Array<{ nome: string; preco?: number | null; estoque: number }>>([])

    const imagensExistentes = ref<Array<{ id: number; url: string }>>([])
    const idsImagensRemovidas = ref<number[]>([])
    const novasImagens = ref<Array<{ file: File; previewUrl: string }>>([])

    const imagensExistentesVisiveis = computed(() =>
        imagensExistentes.value.filter((img) => !idsImagensRemovidas.value.includes(img.id))
    )

    const totalImagens = computed(() => imagensExistentesVisiveis.value.length + novasImagens.value.length)

    const categoriasFlat = ref<any[]>([])
    const carregandoCategorias = ref(true)
    const carregandoProduto = ref(false)
    const salvando = ref(false)
    const erro = ref<string | null>(null)

    function adicionarVariacao() {
        variacoes.value.push({ nome: '', preco: null, estoque: 0 })
    }

    function removerVariacao(index: number) {
        variacoes.value.splice(index, 1)
    }

    function adicionarImagens(evento: Event) {
        const target = evento.target as HTMLInputElement
        const arquivos = Array.from(target.files || [])
        const espacoDisponivel = 8 - totalImagens.value

        arquivos.slice(0, espacoDisponivel).forEach((file) => {
            novasImagens.value.push({ file, previewUrl: URL.createObjectURL(file) })
        })
        target.value = ''
    }

    function removerImagemExistente(imgId: number) {
        idsImagensRemovidas.value.push(imgId)
    }

    function removerImagemNova(indice: number) {
        URL.revokeObjectURL(novasImagens.value[indice].previewUrl)
        novasImagens.value.splice(indice, 1)
    }

    function limparPreviews() {
        novasImagens.value.forEach((img) => URL.revokeObjectURL(img.previewUrl))
    }

    function achatarArvore(categorias: any[], nivel = 0): any[] {
        const resultado: any[] = []
        for (const cat of categorias) {
            const filhas = cat.filhas_recursivas ?? []
            resultado.push({ id: cat.id, nome: cat.nome, nivel, eh_folha: filhas.length === 0 })
            if (filhas.length) resultado.push(...achatarArvore(filhas, nivel + 1))
        }
        return resultado
    }

    async function carregarCategorias() {
        carregandoCategorias.value = true
        try {
            const { data } = await api.get('/admin/categorias/arvore')
            categoriasFlat.value = achatarArvore(data.data)
        } catch {
            categoriasFlat.value = []
        } finally {
            carregandoCategorias.value = false
        }
    }

    async function carregarProduto() {
        if (!modoEdicao.value || id === null || id === undefined || String(id) === '') return
        carregandoProduto.value = true
        try {
            const { data } = await api.get(`/admin/produtos/${id}`)
            produto.value = {
                nome: data.data.nome,
                categoria_id: data.data.categoria_id ?? data.data.categoriaId ?? '',
                preco: data.data.preco,
                estoque: data.data.estoque,
                peso: data.data.peso ?? null,
                altura: data.data.altura ?? null,
                largura: data.data.largura ?? null,
                comprimento: data.data.comprimento ?? null,
                descricao: data.data.descricao ?? '',
            }
            imagensExistentes.value = data.data.imagens ?? []
            variacoes.value = (data.data.variacoes ?? []).map((v: any) => ({
                nome: v.nome,
                preco: v.preco,
                estoque: v.estoque,
            }))
        } catch {
            erro.value = 'Não foi possível carregar este produto.'
        } finally {
            carregandoProduto.value = false
        }
    }

    async function salvar() {
        salvando.value = true
        erro.value = null

        const formData = new FormData()
        formData.append('nome', produto.value.nome)
        formData.append('categoria_id', String(produto.value.categoria_id))
        formData.append('preco', String(produto.value.preco))
        formData.append('descricao', produto.value.descricao || '')
        formData.append('estoque', String(produto.value.estoque ?? 0))
        if (produto.value.peso !== null) formData.append('peso', String(produto.value.peso))
        if (produto.value.altura !== null) formData.append('altura', String(produto.value.altura))
        if (produto.value.largura !== null) formData.append('largura', String(produto.value.largura))
        if (produto.value.comprimento !== null) formData.append('comprimento', String(produto.value.comprimento))

        variacoes.value.forEach((v, i) => {
            formData.append(`variacoes[${i}][nome]`, v.nome)
            if (v.preco !== null && v.preco !== undefined) {
                formData.append(`variacoes[${i}][preco]`, String(v.preco))
            }
            formData.append(`variacoes[${i}][estoque]`, String(v.estoque))
        })

        novasImagens.value.forEach((img) => formData.append('imagens[]', img.file))
        idsImagensRemovidas.value.forEach((imgId) => formData.append('imagens_removidas[]', String(imgId)))

        const url = modoEdicao.value ? `/admin/produtos/${id}` : '/admin/produtos'
        if (modoEdicao.value) formData.append('_method', 'PUT')

        try {
            await api.post(url, formData)
            limparPreviews()
            if (callbacks?.salvo) callbacks.salvo()
            else router.push({ name: 'produtos' })
        } catch (e: unknown) {
            const err = e as any
            const erros = err.response?.data?.errors
            const primeiraChave = erros ? Object.keys(erros)[0] : null
            erro.value = primeiraChave && erros[primeiraChave][0] ? String(erros[primeiraChave][0]) : 'Não foi possível salvar o produto.'
        } finally {
            salvando.value = false
        }
    }

    function cancelar() {
        limparPreviews()
        if (callbacks?.cancelado) callbacks.cancelado()
        else router.push({ name: 'produtos' })
    }

    return {
        modoEdicao,
        produto,
        variacoes,
        categoriasFlat,
        carregandoCategorias,
        carregandoProduto,
        salvando,
        erro,
        imagensExistentesVisiveis,
        novasImagens,
        totalImagens,
        adicionarVariacao,
        removerVariacao,
        adicionarImagens,
        removerImagemExistente,
        removerImagemNova,
        limparPreviews,
        carregarCategorias,
        carregarProduto,
        salvar,
        cancelar,
    }
}