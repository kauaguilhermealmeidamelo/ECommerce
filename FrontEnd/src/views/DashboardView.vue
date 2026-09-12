<!-- src/views/DashboardView.vue -->
<template>
  <v-container class="dashboard-container py-8 px-6" fluid>
    <!-- Cabeçalho -->
    <div class="dashboard-header mb-8">
      <div class="d-flex flex-column flex-sm-row justify-space-between align-sm-center gap-4">
        <div>
          <h1 class="text-h4 font-weight-bold text-grey-darken-4 tracking-tight">Dashboard</h1>
          <p class="text-body-2 text-grey-darken-1 mt-1">Visão geral e desempenho consolidado das operações da sua loja</p>
        </div>
        <div class="d-flex align-center gap-3">
          <v-chip variant="outlined" color="grey-darken-2" size="small" prepend-icon="mdi-calendar-range">
            Setembro de 2026
          </v-chip>
        </div>
      </div>
    </div>

    <!-- Cards de Estatísticas (KPIs) -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" lg="3">
        <StatCard 
          title="Faturamento (mês)" 
          :value="formatarMoeda(dados.faturamentoAtual)" 
          :comparison="`vs. mês anterior ${formatarMoeda(dados.faturamentoAnterior)}`"
          variant="indigo"
        >
          <template #icon>
            <v-icon icon="mdi-currency-usd" size="24" />
          </template>
          <template #badge>
            <v-chip color="success" size="x-small" class="font-weight-bold px-2" prepend-icon="mdi-menu-up">18.2%</v-chip>
          </template>
        </StatCard>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <StatCard 
          title="Pedidos (mês)" 
          :value="dados.pedidosAtual" 
          :comparison="`vs. mês anterior ${dados.pedidosAnterior}`"
          variant="purple"
        >
          <template #icon>
            <v-icon icon="mdi-shopping-outline" size="24" />
          </template>
          <template #badge>
            <v-chip color="success" size="x-small" class="font-weight-bold px-2" prepend-icon="mdi-menu-up">12.5%</v-chip>
          </template>
        </StatCard>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <StatCard 
          title="Ticket Médio" 
          :value="formatarMoeda(dados.ticketMedioAtual)" 
          :comparison="`vs. mês anterior ${formatarMoeda(dados.ticketMedioAnterior)}`"
          variant="amber"
        >
          <template #icon>
            <v-icon icon="mdi-chart-line" size="24" />
          </template>
        </StatCard>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <StatCard 
          title="Novos Clientes" 
          :value="dados.novosClientesAtual" 
          :comparison="`vs. mês anterior ${dados.novosClientesAnterior}`"
          variant="emerald"
        >
          <template #icon>
            <v-icon icon="mdi-account-group-outline" size="24" />
          </template>
          <template #badge>
            <v-chip color="success" size="x-small" class="font-weight-bold px-2" prepend-icon="mdi-menu-up">5.2%</v-chip>
          </template>
        </StatCard>
      </v-col>
    </v-row>

    <!-- Seção de Gráficos e Resumo do Mês -->
    <v-row class="mb-6">
      <!-- Gráfico de Comparação de Custos, Lucros e Vendas -->
      <v-col cols="12" lg="8">
        <v-card class="card-custom pa-6 h-100 d-flex flex-column justify-space-between" elevation="0">
          <div>
            <div class="d-flex justify-space-between align-center flex-wrap gap-2 mb-1">
              <h2 class="text-h6 font-weight-bold text-grey-darken-4">Comparativo de Vendas, Custos e Lucros</h2>
              <div class="d-flex align-center legend-container">
                <span class="legend-item"><span class="dot receita"></span> Vendas (Sales)</span>
                <span class="legend-item"><span class="dot gasto"></span> Custos (Cost)</span>
                <span class="legend-item"><span class="dot lucro"></span> Lucro (Profit)</span>
              </div>
            </div>
            <p class="text-caption text-grey-darken-1 mb-4">Análise comparativa de desempenho financeiro dos últimos 6 meses</p>
          </div>
          
          <!-- Tooltip Informativo do Mês Selecionado -->
          <div class="chart-tooltip-box mb-4 px-4 py-3">
            <div class="d-flex justify-space-between align-center flex-wrap gap-2">
              <div>
                <span class="text-caption text-grey">Mês em análise:</span>
                <strong class="text-primary ml-1 font-weight-bold">{{ mesSelecionado.mes }}</strong>
              </div>
              <div class="d-flex align-center tooltip-values gap-4">
                <span>Vendas: <strong class="text-blue-darken-2">{{ formatarMoeda(mesSelecionado.receita) }}</strong></span>
                <span>Custo: <strong class="text-error">{{ formatarMoeda(mesSelecionado.gasto) }}</strong></span>
                <span>Lucro: <strong class="text-success">{{ formatarMoeda(mesSelecionado.lucro) }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Gráfico de Colunas Agrupadas com Proporção Adequada -->
          <div class="bar-chart-container my-3">
            <div 
              v-for="(item, index) in historicoMensal" 
              :key="index"
              class="bar-group"
              @mouseenter="mesSelecionado = item"
              @click="mesSelecionado = item"
            >
              <div class="bars-wrapper">
                <!-- Coluna Vendas proporcional (Escala baseada em max 18.000) -->
                <div class="bar bar-receita" :style="{ height: `${(item.receita / 18000) * 100}%` }" title="Vendas"></div>
                <!-- Coluna Custos proporcional -->
                <div class="bar bar-gasto" :style="{ height: `${(item.gasto / 18000) * 100}%` }" title="Custos"></div>
                <!-- Coluna Lucro proporcional -->
                <div class="bar bar-lucro" :style="{ height: `${(item.lucro / 18000) * 100}%` }" title="Lucro"></div>
              </div>
              <span class="bar-label" :class="{ 'text-primary font-weight-bold': mesSelecionado.mes === item.mes }">
                {{ item.mes }}
              </span>
            </div>
          </div>
        </v-card>
      </v-col>

      <!-- Resumo do Mês -->
      <v-col cols="12" lg="4">
        <v-card class="card-custom pa-6 h-100 d-flex flex-column justify-space-between" elevation="0">
          <div>
            <div class="d-flex justify-space-between align-center mb-2">
              <h2 class="text-h6 font-weight-bold text-grey-darken-4">Resumo do Mês</h2>
            </div>
            <p class="text-caption text-uppercase text-grey font-weight-medium mb-4">setembro de 2026</p>
            
            <div class="summary-list">
              <div class="summary-item">
                <span class="summary-label">Vendas (Sales)</span>
                <span class="summary-value text-grey-darken-4">{{ formatarMoeda(dados.faturamentoAtual) }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Custos (Cost)</span>
                <span class="summary-value text-error">{{ formatarMoeda(dados.gastoAtual) }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Lucro (Profit)</span>
                <span class="summary-value text-success font-weight-bold">{{ formatarMoeda(dados.lucroEstimado) }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Pedidos</span>
                <span class="summary-value text-grey-darken-4">{{ dados.pedidosAtual }}</span>
              </div>
              <div class="summary-item border-none">
                <span class="summary-label">Novos clientes</span>
                <span class="summary-value text-grey-darken-4">{{ dados.novosClientesAtual }}</span>
              </div>
            </div>
          </div>

          <div class="best-month-box mt-6 pa-4 rounded-xl">
            <div class="text-caption font-weight-bold text-primary mb-1">Melhor mês do período</div>
            <div class="text-body-2 font-weight-bold text-grey-darken-4">abr/2026 — {{ formatarMoeda(16200.00) }}</div>
            <div class="text-caption text-grey-darken-1 mt-1">Faturamento atual representa 91.6% desse recorde.</div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Seção Inferior: Vendas por Categoria (Camisas, Calças, Moletons) -->
    <v-row>
      <v-col cols="12">
        <v-card class="card-custom pa-6" elevation="0">
          <div class="d-flex justify-space-between align-center mb-1">
            <h2 class="text-h6 font-weight-bold text-grey-darken-4">Vendas por Categoria</h2>
            <span class="text-caption text-grey">Últimos 90 dias</span>
          </div>
          <p class="text-caption text-grey-darken-1 mb-4">Faturamento segmentado por categoria cadastrada</p>
          
          <v-table class="categorias-table hover-table" theme="light">
            <thead>
              <tr>
                <th class="text-left font-weight-bold text-grey-darken-2">CATEGORIA</th>
                <th class="text-right font-weight-bold text-grey-darken-2">PEDIDOS</th>
                <th class="text-right font-weight-bold text-grey-darken-2">FATURAMENTO</th>
                <th class="text-center font-weight-bold text-grey-darken-2" style="width: 140px;">PARTICIPAÇÃO</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cat in categoriasMock" :key="cat.nome">
                <td class="font-weight-medium text-grey-darken-4 py-3">
                  <div class="d-flex align-center gap-2">
                    <v-avatar color="indigo-lighten-5" size="32" rounded="lg">
                      <v-icon icon="mdi-tag-outline" size="small" color="indigo-darken-2"></v-icon>
                    </v-avatar>
                    <span>{{ cat.nome }}</span>
                  </div>
                </td>
                <td class="text-right text-grey-darken-2">{{ cat.pedidos }}</td>
                <td class="text-right font-weight-bold text-grey-darken-4">{{ formatarMoeda(cat.faturamento) }}</td>
                <td class="text-center">
                  <v-progress-linear 
                    :model-value="cat.porcentagem" 
                    color="indigo" 
                    height="6" 
                    rounded
                    class="bg-grey-lighten-3"
                  ></v-progress-linear>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import StatCard from '@/components/dashboard/StatCard.vue'

const dados = ref({
  faturamentoAtual: 14850.40,
  faturamentoAnterior: 12500.00,
  pedidosAtual: 64,
  pedidosAnterior: 57,
  ticketMedioAtual: 232.03,
  ticketMedioAnterior: 219.30,
  novosClientesAtual: 18,
  novosClientesAnterior: 17,
  lucroEstimado: 5940.16,
  gastoAtual: 8910.24
})

const historicoMensal = ref([
  { mes: 'abr/2026', receita: 16200.00, lucro: 6500.00, gasto: 9700.00 },
  { mes: 'mai/2026', receita: 12500.00, lucro: 4800.00, gasto: 7700.00 },
  { mes: 'jun/2026', receita: 11800.00, lucro: 4500.00, gasto: 7300.00 },
  { mes: 'jul/2026', receita: 13400.00, lucro: 5200.00, gasto: 8200.00 },
  { mes: 'ago/2026', receita: 13900.00, lucro: 5600.00, gasto: 8300.00 },
  { mes: 'set/2026', receita: 14850.40, lucro: 5940.16, gasto: 8910.24 }
])

const mesSelecionado = ref(historicoMensal.value[5])

const categoriasMock = ref([
  { nome: 'Camisas', pedidos: 34, faturamento: 6800.00, porcentagem: 45.8 },
  { nome: 'Calças', pedidos: 22, faturamento: 5280.40, porcentagem: 35.5 },
  { nome: 'Moletons', pedidos: 12, faturamento: 2770.00, porcentagem: 18.7 }
])

const formatarMoeda = (valor) => {
  return Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
</script>

<style scoped>
.dashboard-container {
  max-width: 84rem;
  margin: 0 auto;
}

.card-custom {
  background-color: #ffffff !important;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
  border: 1px solid #f1f5f9;
}

.legend-container {
  gap: 1.25rem;
}

.legend-item {
  font-size: 0.75rem;
  font-weight: 600;
  color: #4b5563;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}
.dot.receita { background-color: #2563eb; }
.dot.gasto { background-color: #dc2626; }
.dot.lucro { background-color: #059669; }

.chart-tooltip-box {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  font-size: 0.85rem;
  color: #334155;
}

.tooltip-values {
  gap: 1.5rem !important;
}

/* Gráfico de colunas com altura ideal de mercado */
.bar-chart-container {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  height: 240px;
  padding: 1rem 1rem 0 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  flex: 1;
}

.bars-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 6px;
  height: 180px;
  width: 100%;
  justify-content: center;
}

.bar {
  width: 14px;
  border-radius: 6px 6px 0 0;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.bar:hover {
  opacity: 0.85;
  transform: scaleY(1.02);
}

.bar-receita { background-color: #2563eb; }
.bar-gasto { background-color: #dc2626; }
.bar-lucro { background-color: #059669; }

.bar-label {
  font-size: 0.8rem;
  font-weight: 500;
  color: #94a3b8;
  transition: color 0.2s ease;
}

.bar-group:hover .bar-label {
  color: #2563eb;
}

.best-month-box {
  background-color: #f0f7ff;
  border: 1px solid #dbeafe;
}

.summary-list {
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid #f8fafc;
}

.summary-item.border-none {
  border-bottom: none;
  padding-bottom: 0;
}

.summary-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.summary-value {
  font-weight: 600;
  font-size: 0.95rem;
}

.categorias-table {
  background-color: transparent !important;
}

.categorias-table :deep(th) {
  color: #475569 !important;
  font-size: 0.75rem !important;
  font-weight: 700 !important;
  letter-spacing: 0.05em;
  background-color: #f8fafc !important;
  border-bottom: 2px solid #e2e8f0 !important;
}

.categorias-table :deep(td) {
  border-bottom: 1px solid #f1f5f9 !important;
  background-color: #ffffff !important;
}

.hover-table :deep(tbody tr:hover) {
  background-color: #f8fafc !important;
}
</style>