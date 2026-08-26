<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Painel</h1>
    <p class="pagina__subtitulo">Vendas, lucro e categorias da sua loja.</p>

    <div v-if="carregando">Carregando...</div>
    <div v-else-if="erro" class="pagina__erro">{{ erro }}</div>

    <template v-else-if="dados">
      <section class="cards">
        <StatCard icone="💰" :valor="formatarMoeda(dados.mes_atual.faturamento)" label="Faturamento (mês)"
          :variacao="calcularVariacao(dados.mes_atual.faturamento, dados.mes_anterior.faturamento)" />
        <StatCard icone="📈" :valor="formatarMoeda(dados.mes_atual.lucro)" label="Lucro (mês)"
          :variacao="calcularVariacao(dados.mes_atual.lucro, dados.mes_anterior.lucro)" />
        <StatCard icone="📦" :valor="dados.mes_atual.pedidos" label="Pedidos (mês)"
          :variacao="calcularVariacao(dados.mes_atual.pedidos, dados.mes_anterior.pedidos)" />
        <StatCard icone="🎫" :valor="formatarMoeda(dados.mes_atual.ticket_medio)" label="Ticket médio" />
        <StatCard v-if="visitas" icone="👀" :valor="visitas.hoje" label="Visitas hoje" />
        <StatCard v-if="visitas" icone="🧍" :valor="visitas.visitantes_unicos_hoje" label="Visitantes únicos" />
      </section>

      <section class="painel" style="margin-top:1.5rem">
        <h2 class="painel__titulo">Faturamento × lucro — últimos 6 meses</h2>
        <canvas ref="canvasLinha" height="140"></canvas>
      </section>

      <section class="painel" style="margin-top:1.25rem">
        <h2 class="painel__titulo">Categorias mais vendidas (90 dias)</h2>
        <canvas ref="canvasCategorias" height="150"></canvas>

        <div style="margin-top:1rem">
          <div v-for="cat in dados.categorias_mais_vendidas" :key="cat.categoria" class="categoria-item">
            <span class="categoria-nome">{{ cat.categoria }}</span>
            <span class="categoria-qtd">{{ cat.quantidade_vendida }} un · {{ cat.percentual }}%</span>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import Chart from 'chart.js/auto'
import api from '@/services/api'
import StatCard from '@/components/StatCard.vue'

const dados = ref(null)
const visitas = ref(null)
const carregando = ref(true)
const erro = ref(null)
const canvasLinha = ref(null)
const canvasCategorias = ref(null)

let chartLinha = null
let chartCategorias = null

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const calcularVariacao = (atual, anterior) => (anterior ? ((atual - anterior) / anterior) * 100 : null)

async function carregar() {
  carregando.value = true
  erro.value = null

  try {
    const [{ data }, { data: dataVisitas }] = await Promise.all([
      api.get('/admin/dashboard'),
      api.get('/admin/visitas/resumo'),
    ])
    dados.value = data.data
    visitas.value = dataVisitas.data
  } catch (e) {
    erro.value = 'Não foi possível carregar o dashboard.'
  } finally {
    // Importante: só desligamos o "Carregando..." aqui, ANTES de tentar
    // montar os gráficos. O bloco com os <canvas> só existe no DOM depois
    // que carregando vira false (v-else-if="dados") — se a gente montar
    // os gráficos antes disso, o Chart.js recebe um <canvas> que ainda
    // não foi renderizado e quebra com "can't acquire context from the
    // given item".
    carregando.value = false
  }

  if (dados.value) {
    await nextTick()
    montarGraficoLinha()
    montarGraficoCategorias()
  }
}

function montarGraficoLinha() {
  if (!canvasLinha.value) return

  // Evita acumular instâncias de Chart.js se o dashboard for recarregado
  // (ex: usuário navega pra outra tela e volta) — sem isso, o Chart.js
  // reclama de já existir um gráfico no mesmo canvas.
  chartLinha?.destroy()

  chartLinha = new Chart(canvasLinha.value, {
    type: 'line',
    data: {
      labels: dados.value.serie_mensal.map((m) => m.mes),
      datasets: [
        { label: 'Faturamento', data: dados.value.serie_mensal.map((m) => m.faturamento), borderColor: '#1c2a63', backgroundColor: 'rgba(28,42,99,0.08)', fill: true, tension: .35, borderWidth: 2, pointRadius: 3 },
        { label: 'Lucro', data: dados.value.serie_mensal.map((m) => m.lucro), borderColor: '#8b93b0', backgroundColor: 'transparent', tension: .35, borderWidth: 2, pointRadius: 3 },
      ],
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
      scales: { y: { display: false }, x: { grid: { display: false } } },
    },
  })
}

function montarGraficoCategorias() {
  if (!canvasCategorias.value) return

  chartCategorias?.destroy()

  chartCategorias = new Chart(canvasCategorias.value, {
    type: 'bar',
    data: {
      labels: dados.value.categorias_mais_vendidas.map((c) => c.categoria),
      datasets: [{ data: dados.value.categorias_mais_vendidas.map((c) => c.quantidade_vendida), backgroundColor: '#1c2a63', borderRadius: 6, maxBarThickness: 34 }],
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { display: false }, x: { grid: { display: false } } },
    },
  })
}

onMounted(carregar)
</script>

<style scoped>
.cards { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
.painel__titulo { font-size: .82rem; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-soft); margin: 0 0 1rem; font-weight: 700; }
.categoria-item { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid var(--line); font-size: .9rem; }
.categoria-item:last-child { border: none; }
.categoria-nome { font-weight: 600; }
.categoria-qtd { color: var(--ink-soft); }
.pagina__erro { color: var(--danger); }
</style>