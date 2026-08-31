<template>
  <div class="pagina">
    <div v-if="carregando" class="estado-carregando">Carregando painel...</div>
    <p v-else-if="erro" class="erro-mensagem">{{ erro }}</p>

    <template v-else-if="dados">
      <!-- Métricas -->
      <section class="grade-metricas">
        <StatCard icone="mdi-cash-multiple" cor="azul" :valor="formatarMoeda(dados.mes_atual.faturamento)"
          label="Faturamento (mês)"
          :variacao="calcularVariacao(dados.mes_atual.faturamento, dados.mes_anterior.faturamento)"
          :sub="`vs. mês anterior ${formatarMoeda(dados.mes_anterior.faturamento)}`" />

        <StatCard icone="mdi-shopping" cor="roxo" :valor="dados.mes_atual.pedidos" label="Pedidos (mês)"
          :variacao="calcularVariacao(dados.mes_atual.pedidos, dados.mes_anterior.pedidos)"
          :sub="`vs. mês anterior ${dados.mes_anterior.pedidos}`" />

        <StatCard icone="mdi-credit-card-outline" cor="laranja" :valor="formatarMoeda(dados.mes_atual.ticket_medio)"
          label="Ticket Médio"
          :variacao="calcularVariacao(dados.mes_atual.ticket_medio, dados.mes_anterior.ticket_medio)"
          :sub="`vs. mês anterior ${formatarMoeda(dados.mes_anterior.ticket_medio)}`" />

        <StatCard icone="mdi-account-group" cor="verde" :valor="dados.mes_atual.novos_clientes" label="Novos Clientes"
          :variacao="calcularVariacao(dados.mes_atual.novos_clientes, dados.mes_anterior.novos_clientes)"
          :sub="`vs. mês anterior ${dados.mes_anterior.novos_clientes}`" />

      </section>

      <!-- Receita + resumo do mês -->
      <section class="linha-dupla">
        <div class="card grafico-receita">
          <div class="card__cabecalho">
            
            <div>
              <h3 class="card__titulo">Receita × Lucro</h3>
              <p class="card__subtitulo">Últimos 6 meses</p>
            </div>
          </div>
          <div class="grafico-receita__corpo">
            <canvas ref="canvasReceita" height="150"></canvas>
          </div>
        </div>

        <div class="card resumo-mes">
          <div class="card__cabecalho">
            <div>
              <h3 class="card__titulo">Resumo do Mês</h3>
              <p class="card__subtitulo">{{ mesAtualFormatado }}</p>
            </div>
          </div>
          <div class="resumo-mes__corpo">
            <div v-for="linha in linhasResumo" :key="linha.label" class="resumo-mes__linha">
              <div class="resumo-mes__linha-topo">
                <span>{{ linha.label }}</span>
                <strong>{{ linha.valor }}</strong>
              </div>
              <div class="barra">
                <div class="barra__preenchimento" :class="linha.cor" :style="{ width: linha.pct + '%' }"></div>
              </div>
            </div>

            <div class="resumo-mes__destaque">
              <p>Melhor mês do período</p>
              <strong>{{ melhorMes?.mes }} — {{ formatarMoeda(melhorMes?.faturamento ?? 0) }}</strong>
              <span>Faturamento atual representa {{ pctDoMelhorMes }}% desse recorde.</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Vendas por categoria (NOVO) -->
      <section class="card">
        <div class="card__cabecalho">
          <div>
            <h3 class="card__titulo">Vendas por Categoria</h3>
            <p class="card__subtitulo">Faturamento dos últimos 90 dias, por categoria cadastrada</p>
          </div>
        </div>

        <div v-if="dados.categorias_mais_vendidas.length === 0" class="estado-vazio">
          Nenhuma venda registrada nas categorias cadastradas ainda.
        </div>

        <div v-else class="categorias__corpo">
          <div class="categorias__grafico">
            <canvas ref="canvasCategorias" height="220"></canvas>
          </div>
          <ul class="categorias__lista">
            <li v-for="(cat, i) in dados.categorias_mais_vendidas" :key="cat.categoria" class="categorias__item">
              <span class="categorias__ponto"
                :style="{ background: coresCategorias[i % coresCategorias.length] }"></span>
              <span class="categorias__nome">{{ cat.categoria }}</span>
              <span class="categorias__valor">{{ formatarMoeda(cat.faturamento) }}</span>
              <span class="categorias__pct">{{ cat.percentual_faturamento }}%</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Últimos pedidos -->
      <section class="card">
        <div class="card__cabecalho">
          <div>
            <h3 class="card__titulo">Últimos Pedidos</h3>
            <p class="card__subtitulo">{{ pedidos.length }} pedido{{ pedidos.length !== 1 ? 's' : '' }} recentes</p>
          </div>
          <router-link :to="{ name: 'pedidos' }" class="btn btn--fantasma">Ver todos →</router-link>
        </div>

        <div v-if="carregandoPedidos" class="estado-carregando">Carregando pedidos...</div>
        <div v-else-if="pedidos.length === 0" class="estado-vazio">Nenhum pedido recebido ainda.</div>

        <template v-else>
          <div class="tabela__scroll pedidos-tabela--desktop">
            <table class="tabela">
              <thead>
                <tr>
                  <th>Pedido</th>
                  <th>Data</th>
                  <th>Itens</th>
                  <th>Valor</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pedido in pedidos" :key="pedido.id">
                  <td><strong>#{{ pedido.id }}</strong></td>
                  <td>{{ formatarData(pedido.created_at) }}</td>
                  <td>{{ pedido.itens?.length ?? 0 }} item(ns)</td>
                  <td><strong>{{ formatarMoeda(pedido.total) }}</strong></td>
                  <td>
                    <StatusPedidoBadge :status="pedido.status" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="lista-mobile pedidos-tabela--mobile">
            <div v-for="pedido in pedidos" :key="pedido.id" class="lista-mobile__item">
              <div style="flex:1">
                <strong>#{{ pedido.id }}</strong>
                <p class="pedidos-tabela__meta">{{ formatarData(pedido.created_at) }} · {{ pedido.itens?.length ?? 0 }}
                  item(ns)</p>
              </div>
              <div style="text-align:right">
                <strong>{{ formatarMoeda(pedido.total) }}</strong>
                <div>
                  <StatusPedidoBadge :status="pedido.status" />
                </div>
              </div>
            </div>
          </div>
        </template>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import Chart from 'chart.js/auto'
import api from '@/services/api'
import StatCard from '@/components/StatCard.vue'
import StatusPedidoBadge from '@/components/StatusPedidoBadge.vue'

const dados = ref(null)
const carregando = ref(true)
const erro = ref(null)

const pedidos = ref([])
const carregandoPedidos = ref(true)

const canvasReceita = ref(null)
const canvasCategorias = ref(null)
let chartReceita = null
let chartCategorias = null

const coresCategorias = ['#2563eb', '#7c3aed', '#0ea5e9', '#16a34a', '#ea580c', '#d97706', '#dc2626', '#0891b2']

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const formatarData = (d) => new Date(d).toLocaleDateString('pt-BR')
const calcularVariacao = (atual, anterior) => (anterior ? ((atual - anterior) / anterior) * 100 : null)

const mesAtualFormatado = computed(() => new Date().toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }))

const melhorMes = computed(() => {
  if (!dados.value?.serie_mensal?.length) return null
  return [...dados.value.serie_mensal].sort((a, b) => b.faturamento - a.faturamento)[0]
})

const pctDoMelhorMes = computed(() => {
  if (!melhorMes.value || !dados.value) return 0
  if (!melhorMes.value.faturamento) return 0
  return Math.min(100, Math.round((dados.value.mes_atual.faturamento / melhorMes.value.faturamento) * 100))
})

const linhasResumo = computed(() => {
  if (!dados.value) return []
  const serie = dados.value.serie_mensal
  const maxFaturamento = Math.max(...serie.map((m) => m.faturamento), 1)
  const maxPedidos = Math.max(...serie.map((m) => m.pedidos), 1)
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
  } catch (e) {
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
  } catch (e) {
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
      labels: dados.value.serie_mensal.map((m) => m.mes),
      datasets: [
        {
          label: 'Receita', data: dados.value.serie_mensal.map((m) => m.faturamento),
          borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)', fill: true, tension: .35, borderWidth: 2.5, pointRadius: 3,
        },
        {
          label: 'Lucro', data: dados.value.serie_mensal.map((m) => m.lucro),
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
      labels: categorias.map((c) => c.categoria),
      datasets: [{
        data: categorias.map((c) => c.faturamento),
        backgroundColor: categorias.map((_, i) => coresCategorias[i % coresCategorias.length]),
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
            label: (ctx) => `${ctx.label}: ${formatarMoeda(ctx.raw)}`,
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
</script>

<style scoped>
.linha-dupla {
  display: grid;
  grid-template-columns: 1fr;
  gap: .9rem;
}

@media (min-width: 1000px) {
  .linha-dupla {
    grid-template-columns: 1.6fr 1fr;
  }
}

.grafico-receita {
  padding: 1.1rem;
}

.grafico-receita__corpo {
  margin-top: .5rem;
}

.resumo-mes {
  padding: 1.1rem;
  display: flex;
  flex-direction: column;
}

.resumo-mes__corpo {
  display: flex;
  flex-direction: column;
  gap: .9rem;
  margin-top: .4rem;
}

.resumo-mes__linha-topo {
  display: flex;
  justify-content: space-between;
  font-size: .78rem;
  margin-bottom: .35rem;
}

.resumo-mes__linha-topo span {
  color: var(--ink-soft);
  font-weight: 600;
}

.resumo-mes__linha-topo strong {
  color: var(--ink);
}

.resumo-mes__barra--azul {
  background: var(--blue-600);
}

.resumo-mes__barra--verde {
  background: var(--success);
}

.resumo-mes__barra--roxo {
  background: var(--info);
}

.resumo-mes__barra--laranja {
  background: #ea580c;
}

.resumo-mes__destaque {
  margin-top: .3rem;
  padding-top: .9rem;
  border-top: 1px solid var(--line);
  background: var(--blue-50);
  border-radius: var(--radius-md);
  padding: .9rem;
}

.resumo-mes__destaque p {
  font-size: .72rem;
  font-weight: 700;
  color: var(--blue-700);
  margin: 0 0 .2rem;
}

.resumo-mes__destaque strong {
  font-size: 1rem;
  color: var(--blue-700);
  display: block;
}

.resumo-mes__destaque span {
  font-size: .68rem;
  color: var(--blue-600);
  opacity: .8;
}

.categorias__corpo {
  padding: 1.1rem;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  align-items: center;
}

.categorias__grafico {
  width: 100%;
  max-width: 220px;
}

.categorias__lista {
  list-style: none;
  margin: 0;
  padding: 0;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: .55rem;
}

.categorias__item {
  display: flex;
  align-items: center;
  gap: .55rem;
  font-size: .8rem;
}

.categorias__ponto {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  flex-shrink: 0;
}

.categorias__nome {
  flex: 1;
  font-weight: 600;
  color: var(--ink);
}

.categorias__valor {
  font-weight: 700;
  color: var(--ink);
}

.categorias__pct {
  color: var(--ink-faint);
  width: 3rem;
  text-align: right;
}

@media (min-width: 700px) {
  .categorias__corpo {
    flex-direction: row;
    align-items: center;
  }

  .categorias__grafico {
    max-width: 240px;
  }
}

.pedidos-tabela--mobile {
  display: block;
}

.pedidos-tabela--desktop {
  display: none;
}

.pedidos-tabela__meta {
  font-size: .72rem;
  color: var(--ink-faint);
  margin: .15rem 0 0;
}

@media (min-width: 640px) {
  .pedidos-tabela--mobile {
    display: none;
  }

  .pedidos-tabela--desktop {
    display: block;
  }
}
</style>