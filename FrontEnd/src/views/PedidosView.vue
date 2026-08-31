<template>
  <div class="pagina">
    <div class="pagina__cabecalho">
      <div>
        <h1 class="pagina__titulo">Pedidos</h1>
        <p class="pagina__subtitulo">Acompanhe suas vendas</p>
      </div>
      <div class="pagina__acoes">
        <button v-if="aba === 'todos'" class="btn btn--secundario" :class="{ 'btn--ativo': filtroAtivo }"
          @click="abrirFiltro">
          🔍 Filtros <span v-if="filtroAtivo" style="color: var(--blue-600)">●</span>
        </button>
      </div>
    </div>

    <div class="abas">
      <button class="abas__item" :class="{ 'abas__item--ativa': aba === 'todos' }" @click="aba = 'todos'">Todos os
        Pedidos</button>
      <button class="abas__item" :class="{ 'abas__item--ativa': aba === 'envio' }" @click="aba = 'envio'">
        Aguardando Envio
        <span v-if="pedidosEnvio.length" class="abas__contador">{{ pedidosEnvio.length }}</span>
      </button>
    </div>

    <!-- Todos os pedidos -->
    <div v-if="aba === 'todos'" class="card">
      <div v-if="carregandoPedidos" class="estado-carregando">Carregando...</div>
      <p v-else-if="erroPedidos" class="erro-mensagem" style="padding:1.5rem">{{ erroPedidos }}</p>
      <div v-else-if="pedidosFiltrados.length === 0" class="estado-vazio">Nenhum pedido encontrado.</div>

      <template v-else>
        <div class="tabela__scroll pedidos-tabela--desktop">
          <table class="tabela">
            <thead>
              <tr>
                <th>Pedido</th>
                <th>Data</th>
                <th>Itens</th>
                <th>Valor</th>
                <th>Pagamento</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pedido in pedidosFiltrados" :key="pedido.id">
                <td><strong>#{{ pedido.id }}</strong></td>
                <td>{{ formatarData(pedido.created_at) }}</td>
                <td>{{ pedido.itens?.length ?? 0 }} item(ns)</td>
                <td><strong>{{ formatarMoeda(pedido.total) }}</strong></td>
                <td>{{ rotuloPagamento(pedido) }}</td>
                <td>
                  <StatusPedidoBadge :status="pedido.status" />
                </td>
                <td><button class="btn btn--fantasma" @click="verDetalhe(pedido)">👁️</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="lista-mobile pedidos-tabela--mobile">
          <div v-for="pedido in pedidosFiltrados" :key="pedido.id" class="lista-mobile__item"
            @click="verDetalhe(pedido)">
            <div style="flex:1">
              <strong>#{{ pedido.id }}</strong>
              <p class="pedidos__meta">{{ formatarData(pedido.created_at) }} · {{ pedido.itens?.length ?? 0 }} item(ns)
                · {{ rotuloPagamento(pedido) }}</p>
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
    </div>

    <!-- Aguardando envio -->
    <div v-else class="lista-mobile" style="display:flex; flex-direction:column; gap:.9rem">
      <div v-if="carregandoEnvio" class="estado-carregando">Carregando...</div>
      <div v-else-if="pedidosEnvio.length === 0" class="card estado-vazio">Nenhum pedido aguardando envio no momento.
      </div>

      <div v-for="pedido in pedidosEnvio" :key="pedido.id" class="card envio-card">
        <div class="envio-card__topo">
          <strong>Pedido #{{ pedido.id }}</strong>
          <span class="badge badge--azul">{{ pedido.metodo_entrega === 'local' ? 'Entrega local' : 'Transportadora'
            }}</span>
        </div>
        <p class="envio-card__itens">{{pedido.itens.map((i) => `${i.quantidade}x ${i.produto}`).join(', ')}}</p>
        <div class="envio-card__endereco">
          <strong>{{ pedido.destinatario.nome }}</strong>
          <span>{{ pedido.destinatario.endereco }}, {{ pedido.destinatario.numero }}</span>
          <span v-if="pedido.destinatario.complemento">{{ pedido.destinatario.complemento }}</span>
          <span>{{ pedido.destinatario.bairro }} — {{ pedido.destinatario.cidade }}/{{ pedido.destinatario.uf }}</span>
          <span><strong>CEP {{ pedido.destinatario.cep }}</strong></span>
        </div>
        <div class="envio-card__acoes">
          <button class="btn btn--secundario" @click="copiarEndereco(pedido)">📋 Copiar endereço</button>
        </div>
        <details class="envio-card__form">
          <summary>Marcar como enviado</summary>
          <div class="envio-card__campos">
            <input v-model="formEnvio[pedido.id].transportadora" placeholder="Transportadora (Correios, Jadlog...)" />
            <input v-model="formEnvio[pedido.id].codigo_rastreio" placeholder="Código de rastreio" />
            <button class="btn btn--primario btn--bloco" :disabled="enviandoId === pedido.id"
              @click="marcarEnviado(pedido)">
              {{ enviandoId === pedido.id ? 'Salvando...' : 'Confirmar envio' }}
            </button>
          </div>
        </details>
      </div>
    </div>

    <!-- Filtro -->
    <Modal :aberto="filtroModalAberto" titulo="Filtrar Pedidos" @fechar="filtroModalAberto = false">
      <div class="filtro-campo">
        <label>Buscar</label>
        <div class="busca"><span>🔍</span><input v-model="filtroPendente.busca" placeholder="Número do pedido..." />
        </div>
      </div>
      <div class="filtro-campo">
        <label>Status</label>
        <div class="filtro-chips">
          <button v-for="s in statusFiltraveis" :key="s.valor" class="filtro-chip"
            :class="{ 'filtro-chip--ativo': filtroPendente.status === s.valor }"
            @click="filtroPendente.status = s.valor">{{
              s.rotulo }}</button>
        </div>
      </div>
      <template #rodape>
        <button class="btn btn--fantasma" @click="limparFiltro">Limpar</button>
        <button class="btn btn--primario" @click="aplicarFiltro">Aplicar</button>
      </template>
    </Modal>

    <!-- Detalhe do pedido -->
    <Modal :aberto="pedidoDetalhe !== null" :titulo="`Pedido #${pedidoDetalhe?.id ?? ''}`"
      @fechar="pedidoDetalhe = null">
      <template v-if="pedidoDetalhe">
        <div class="detalhe-linha"><span>Data</span><strong>{{ formatarData(pedidoDetalhe.created_at) }}</strong></div>
        <div class="detalhe-linha"><span>Itens</span><strong>{{ pedidoDetalhe.itens?.length ?? 0 }}</strong></div>
        <div class="detalhe-linha"><span>Total</span><strong>{{ formatarMoeda(pedidoDetalhe.total) }}</strong></div>
        <div class="detalhe-linha"><span>Entrega</span><strong>{{ rotuloEntrega(pedidoDetalhe.metodo_entrega)
            }}</strong></div>
        <div class="detalhe-linha"><span>Pagamento</span><strong>{{ rotuloPagamento(pedidoDetalhe) }}</strong></div>
        <div v-if="pedidoDetalhe.pago_em" class="detalhe-linha"><span>Pago em</span><strong>{{
          formatarData(pedidoDetalhe.pago_em) }}</strong></div>
        <div v-if="pedidoDetalhe.motivo_recusa" class="detalhe-linha"><span>Motivo da recusa</span><strong>{{
          pedidoDetalhe.motivo_recusa }}</strong></div>
        <div class="detalhe-linha"><span>Status</span>
          <StatusPedidoBadge :status="pedidoDetalhe.status" />
        </div>
      </template>
    </Modal>

    <Toast :mensagem="toastMsg" :tipo="toastTipo" @fechar="toastMsg = ''" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import Toast from '@/components/Toast.vue'
import StatusPedidoBadge from '@/components/StatusPedidoBadge.vue'

const aba = ref('todos')

const pedidos = ref([])
const carregandoPedidos = ref(true)
const erroPedidos = ref(null)

const pedidosEnvio = ref([])
const carregandoEnvio = ref(true)
const formEnvio = reactive({})
const enviandoId = ref(null)

const toastMsg = ref('')
const toastTipo = ref('success')

const filtroModalAberto = ref(false)
const filtro = ref({ busca: '', status: 'Todos' })
const filtroPendente = ref({ busca: '', status: 'Todos' })
const pedidoDetalhe = ref(null)

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

const rotulosPagamento = {
  pix: 'Pix',
  credit_card: 'Cartão de Crédito',
  debit_card: 'Cartão de Débito',
  boleto: 'Boleto',
  saldo_mp: 'Saldo Mercado Pago',
}

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const formatarData = (d) => new Date(d).toLocaleDateString('pt-BR')
const rotuloEntrega = (m) => ({ retirada: 'Retirada na loja', local: 'Entrega local', transportadora: 'Transportadora' }[m] ?? '—')
const rotuloPagamento = (pedido) => {
  if (!pedido?.metodo_pagamento) return pedido?.status === 'pendente' ? 'Aguardando' : '—'
  return rotulosPagamento[pedido.metodo_pagamento] ?? pedido.metodo_pagamento
}

const filtroAtivo = computed(() => filtro.value.busca || filtro.value.status !== 'Todos')

const pedidosFiltrados = computed(() => pedidos.value.filter((p) => {
  const buscaOk = String(p.id).includes(filtro.value.busca)
  const statusOk = filtro.value.status === 'Todos' || p.status === filtro.value.status
  return buscaOk && statusOk
}))

function abrirFiltro() { filtroPendente.value = { ...filtro.value }; filtroModalAberto.value = true }
function aplicarFiltro() { filtro.value = { ...filtroPendente.value }; filtroModalAberto.value = false }
function limparFiltro() { filtro.value = { busca: '', status: 'Todos' }; filtroPendente.value = { ...filtro.value }; filtroModalAberto.value = false }

function verDetalhe(pedido) { pedidoDetalhe.value = pedido }

function copiarEndereco(pedido) {
  const d = pedido.destinatario
  const texto = `${d.nome}\n${d.endereco}, ${d.numero} ${d.complemento ?? ''}\n${d.bairro} - ${d.cidade}/${d.uf}\nCEP: ${d.cep}`
  navigator.clipboard.writeText(texto)
  toastTipo.value = 'info'
  toastMsg.value = 'Endereço copiado.'
}

async function marcarEnviado(pedido) {
  enviandoId.value = pedido.id
  try {
    await api.patch(`/admin/envios/${pedido.id}/marcar-enviado`, formEnvio[pedido.id])
    pedidosEnvio.value = pedidosEnvio.value.filter((p) => p.id !== pedido.id)
    toastTipo.value = 'success'
    toastMsg.value = 'Envio confirmado!'
  } catch (e) {
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
  } catch (e) {
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
  } catch (e) {
    pedidosEnvio.value = []
  } finally {
    carregandoEnvio.value = false
  }
}

onMounted(() => {
  carregarPedidos()
  carregarEnvios()
})
</script>

<style scoped>
.abas {
  display: flex;
  gap: .4rem;
  background: #fff;
  padding: .3rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--line);
  width: fit-content;
}

.abas__item {
  padding: .5rem 1rem;
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  font-size: .8rem;
  font-weight: 700;
  color: var(--ink-soft);
  display: flex;
  align-items: center;
  gap: .4rem;
}

.abas__item--ativa {
  background: var(--blue-600);
  color: #fff;
}

.abas__contador {
  background: rgba(255, 255, 255, .25);
  font-size: .65rem;
  padding: .1rem .4rem;
  border-radius: 999px;
}

.abas__item:not(.abas__item--ativa) .abas__contador {
  background: var(--blue-50);
  color: var(--blue-600);
}

.pedidos-tabela--mobile {
  display: flex;
  flex-direction: column;
}

.pedidos-tabela--desktop {
  display: none;
}

@media (min-width: 640px) {
  .pedidos-tabela--mobile {
    display: none;
  }

  .pedidos-tabela--desktop {
    display: block;
  }
}

.pedidos__meta {
  font-size: .72rem;
  color: var(--ink-faint);
  margin: .1rem 0 0;
}

.filtro-campo {
  margin-bottom: 1.1rem;
}

.filtro-campo label {
  display: block;
  font-size: .75rem;
  font-weight: 700;
  color: var(--ink);
  margin-bottom: .45rem;
}

.filtro-chips {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
}

.envio-card {
  padding: 1rem;
}

.envio-card__topo {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: .5rem;
}

.envio-card__itens {
  font-size: .78rem;
  color: var(--ink-soft);
  margin: 0 0 .8rem;
}

.envio-card__endereco {
  display: flex;
  flex-direction: column;
  gap: .1rem;
  background: #fafbfc;
  border-radius: var(--radius-sm);
  padding: .75rem;
  font-size: .82rem;
  margin-bottom: .8rem;
}

.envio-card__acoes {
  margin-bottom: .5rem;
}

.envio-card__form summary {
  cursor: pointer;
  color: var(--blue-600);
  font-weight: 700;
  font-size: .8rem;
}

.envio-card__campos {
  display: flex;
  flex-direction: column;
  gap: .6rem;
  margin-top: .8rem;
}

.detalhe-linha {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: .5rem 0;
  border-bottom: 1px solid var(--line);
  font-size: .85rem;
}

.detalhe-linha span {
  color: var(--ink-soft);
}

.detalhe-linha:last-child {
  border-bottom: none;
}
</style>
