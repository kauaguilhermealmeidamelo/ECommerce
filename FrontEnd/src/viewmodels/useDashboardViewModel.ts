import { ref, computed, nextTick, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import api from '@/services/api'

export function useDashboardViewModel() {
    const dados = ref<any>(null)
    const carregando = ref(true)
    const erro = ref<string | null>(null)

    const pedidos = ref<any[]>([])
    const carregandoPedidos = ref(true)

    const canvasReceita = ref<HTMLCanvasElement | null>(null)
    const canvasCategorias = ref<HTMLCanvasElement | null>(null)
    let chartReceita: Chart | null = null
    let chartCategorias: Chart | null = null

    const coresCategorias = ['#2563eb', '#7c3aed', '#0ea5e9', '#16a34a', '#ea580c', '#d97706', '#dc2626', '#0891b2']

    const formatarMoeda = (v: number) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
    const formatarData = (d: string) => new Date(d).toLocaleDateString('pt-BR')
    const calcularVariacao = (atual: string | number | null, anterior: string | number | null): number | null => {
        const valAtual = Number(atual) || 0
        const valAnterior = Number(anterior) || 0

        if (!valAnterior) return null
        return ((valAtual - valAnterior) / valAnterior) * 100
    }
    const mesAtualFormatado = computed(() => new Date().toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }))

    const melhorMes = computed(() => {
        if (!dados.value?.serie_mensal?.length) return null
        return [...dados.value.serie_mensal].sort((a: any, b: any) => b.faturamento - a.faturamento)[0]
    })

    const pctDoMelhorMes = computed(() => {
        if (!melhorMes.value || !dados.value) return 0
        if (!melhorMes.value.faturamento) return 0
        return Math.min(100, Math.round((dados.value.mes_atual.faturamento / melhorMes.value.faturamento) * 100))
    })

    const linhasResumo = computed(() => {
        if (!dados.value) return []
        const serie = dados.value.serie_mensal
        const maxFaturamento = Math.max(...serie.map((m: any) => m.faturamento), 1)
        const maxPedidos = Math.max(...serie.map((m: any) => m.pedidos), 1)
        const m = dados.value.mes_atual

        return [
            { label: 'Faturamento', valor: formatarMoeda(m.faturamento), pct: Math.round((m.faturamento / maxFaturamento) * 100), cor: 'resumo-mes__barra--azul' },
            { label: 'Lucro estimado', valor: formatarMoeda(m.lucro), pct: m.faturamento ? Math.round((m.lucro / m.faturamento) * 100) : 0, cor: 'resumo-mes__barra--verde' },
            { label: 'Pedidos', valor: String(m.pedidos), pct: Math.round((m.pedidos / maxPedidos) * 100), cor: 'resumo-mes__barra--roxo' },
            { label: 'Novos clientes', valor: String(m.novos_clientes), pct: Math.min(100, m.novos_clientes * 5), cor: 'resumo-mes__barra--laranja' },
        ]
    })

    async function carregarDashboard() {
        carregando.value = true
        erro.value = null
        try {
            const { data } = await api.get('/admin/dashboard')
            dados.value = data.data
        } catch {
            erro.value = 'Não foi possível carregar o dashboard.'
        } finally {
            carregando.value = false
        }

        if (dados.value) {
            await nextTick()
            montarGraficoReceita()
            montarGraficoCategorias()
        }
    }

    async function carregarUltimosPedidos() {
        carregandoPedidos.value = true
        try {
            const { data } = await api.get('/admin/pedidos')
            pedidos.value = (data.data ?? []).slice(0, 6)
        } catch {
            pedidos.value = []
        } finally {
            carregandoPedidos.value = false
        }
    }

    function montarGraficoReceita() {
        if (!canvasReceita.value) return
        chartReceita?.destroy()

        chartReceita = new Chart(canvasReceita.value, {
            type: 'line',
            data: {
                labels: dados.value.serie_mensal.map((m: any) => m.mes),
                datasets: [
                    {
                        label: 'Receita', data: dados.value.serie_mensal.map((m: any) => m.faturamento),
                        borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)', fill: true, tension: .35, borderWidth: 2.5, pointRadius: 3,
                    },
                    {
                        label: 'Lucro', data: dados.value.serie_mensal.map((m: any) => m.lucro),
                        borderColor: '#8b5cf6', backgroundColor: 'transparent', tension: .35, borderWidth: 2, pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true, font: { size: 11 } } } },
                scales: {
                    y: { display: false },
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af' } },
                },
            },
        })
    }

    function montarGraficoCategorias() {
        if (!canvasCategorias.value) return
        chartCategorias?.destroy()

        const categorias = dados.value.categorias_mais_vendidas
        if (!categorias.length) return

        chartCategorias = new Chart(canvasCategorias.value, {
            type: 'doughnut',
            data: {
                labels: categorias.map((c: any) => c.categoria),
                datasets: [{
                    data: categorias.map((c: any) => c.faturamento),
                    backgroundColor: categorias.map((_: any, i: number) => coresCategorias[i % coresCategorias.length]),
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx: any) => `${ctx.label}: ${formatarMoeda(ctx.raw)}`,
                        },
                    },
                },
            },
        })
    }

    onMounted(() => {
        carregarDashboard()
        carregarUltimosPedidos()
    })

    return {
        dados,
        carregando,
        erro,
        pedidos,
        carregandoPedidos,
        canvasReceita,
        canvasCategorias,
        coresCategorias,
        mesAtualFormatado,
        melhorMes,
        pctDoMelhorMes,
        linhasResumo,
        formatarMoeda,
        formatarData,
        calcularVariacao,
    }
}