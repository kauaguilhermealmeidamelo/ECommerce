import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'

export function usePedidosViewModel() {
    const aba = ref<'todos' | 'envio'>('todos')

    const pedidos = ref<any[]>([])
    const carregandoPedidos = ref(true)
    const erroPedidos = ref<string | null>(null)

    const pedidosEnvio = ref<any[]>([])
    const carregandoEnvio = ref(true)
    const formEnvio = reactive<Record<number, { transportadora: string; codigo_rastreio: string }>>({})
    const enviandoId = ref<number | null>(null)

    const toastMsg = ref('')
    const toastTipo = ref<'success' | 'info' | 'error'>('success')

    const filtroModalAberto = ref(false)
    const filtro = ref({ busca: '', status: 'Todos' })
    const filtroPendente = ref({ busca: '', status: 'Todos' })
    const pedidoDetalhe = ref<any | null>(null)

    const statusFiltraveis = [
        { valor: 'Todos', rotulo: 'Todos' },
        { valor: 'pendente', rotulo: 'Pendente' },
        { valor: 'em_analise', rotulo: 'Em Análise' },
        { valor: 'pago', rotulo: 'Pago' },
        { valor: 'recusado', rotulo: 'Recusado' },
        { valor: 'enviado', rotulo: 'Enviado' },
        { valor: 'concluido', rotulo: 'Concluído' },
        { valor: 'estornado', rotulo: 'Estornado' },
        { valor: 'cancelado', rotulo: 'Cancelado' },
    ]

    const rotulosPagamento: Record<string, string> = {
        pix: 'Pix',
        credit_card: 'Cartão de Crédito',
        debit_card: 'Cartão de Débito',
        boleto: 'Boleto',
        saldo_mp: 'Saldo Mercado Pago',
    }

    const formatarMoeda = (v: number) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
    const formatarData = (d: string) => new Date(d).toLocaleDateString('pt-BR')
    const rotuloEntrega = (m: string) => ({ retirada: 'Retirada na loja', local: 'Entrega local', transportadora: 'Transportadora' }[m] ?? '—')

    const rotuloPagamento = (pedido: any) => {
        if (!pedido?.metodo_pagamento) return pedido?.status === 'pendente' ? 'Aguardando' : '—'
        return rotulosPagamento[pedido.metodo_pagamento] ?? pedido.metodo_pagamento
    }

    const filtroAtivo = computed(() => filtro.value.busca || filtro.value.status !== 'Todos')

    const pedidosFiltrados = computed(() => pedidos.value.filter((p) => {
        const buscaOk = String(p.id).includes(filtro.value.busca)
        const statusOk = filtro.value.status === 'Todos' || p.status === filtro.value.status
        return buscaOk && statusOk
    }))

    function abrirFiltro() {
        filtroPendente.value = { ...filtro.value }
        filtroModalAberto.value = true
    }

    function aplicarFiltro() {
        filtro.value = { ...filtroPendente.value }
        filtroModalAberto.value = false
    }

    function limparFiltro() {
        filtro.value = { busca: '', status: 'Todos' }
        filtroPendente.value = { ...filtro.value }
        filtroModalAberto.value = false
    }

    function verDetalhe(pedido: any) {
        pedidoDetalhe.value = pedido
    }

    function copiarEndereco(pedido: any) {
        const d = pedido.destinatario
        if (!d) return
        const texto = `${d.nome}\n${d.endereco}, ${d.numero} ${d.complemento ?? ''}\n${d.bairro} - ${d.cidade}/${d.uf}\nCEP: ${d.cep}`
        navigator.clipboard.writeText(texto)
        toastTipo.value = 'info'
        toastMsg.value = 'Endereço copiado.'
    }

    async function marcarEnviado(pedido: any) {
        enviandoId.value = pedido.id
        try {
            await api.patch(`/admin/envios/${pedido.id}/marcar-enviado`, formEnvio[pedido.id])
            pedidosEnvio.value = pedidosEnvio.value.filter((p) => p.id !== pedido.id)
            toastTipo.value = 'success'
            toastMsg.value = 'Envio confirmado!'
        } catch {
            toastTipo.value = 'error'
            toastMsg.value = 'Não foi possível confirmar o envio.'
        } finally {
            enviandoId.value = null
        }
    }

    async function carregarPedidos() {
        carregandoPedidos.value = true
        try {
            const { data } = await api.get('/admin/pedidos')
            pedidos.value = data.data
        } catch {
            erroPedidos.value = 'Não foi possível carregar os pedidos.'
        } finally {
            carregandoPedidos.value = false
        }
    }

    async function carregarEnvios() {
        carregandoEnvio.value = true
        try {
            const { data } = await api.get('/admin/envios/pendentes')
            pedidosEnvio.value = data.data
            pedidosEnvio.value.forEach((p) => { formEnvio[p.id] = { transportadora: '', codigo_rastreio: '' } })
        } catch {
            pedidosEnvio.value = []
        } finally {
            carregandoEnvio.value = false
        }
    }

    onMounted(() => {
        carregarPedidos()
        carregarEnvios()
    })

    return {
        aba,
        pedidos,
        carregandoPedidos,
        erroPedidos,
        pedidosEnvio,
        carregandoEnvio,
        formEnvio,
        enviandoId,
        toastMsg,
        toastTipo,
        filtroModalAberto,
        filtroPendente,
        pedidoDetalhe,
        statusFiltraveis,
        filtroAtivo,
        pedidosFiltrados,
        formatarMoeda,
        formatarData,
        rotuloEntrega,
        rotuloPagamento,
        abrirFiltro,
        aplicarFiltro,
        limparFiltro,
        verDetalhe,
        copiarEndereco,
        marcarEnviado,
    }
}