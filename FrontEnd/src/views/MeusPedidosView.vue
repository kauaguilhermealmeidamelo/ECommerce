<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Meus pedidos</h1>
    <p class="pagina__subtitulo">Acompanhe suas compras e rastreie a entrega.</p>

    <div v-if="carregando" class="estado">Carregando seus pedidos...</div>
    <p v-else-if="erro" class="estado estado--erro">{{ erro }}</p>

    <div v-else-if="pedidos.length === 0" class="estado-vazio">
      <p>Você ainda não fez nenhum pedido.</p>
      <router-link :to="{ name: 'catalogo' }" class="btn btn--primario">Ver catálogo</router-link>
    </div>

    <ul v-else class="lista-pedidos">
      <li v-for="pedido in pedidos" :key="pedido.id" class="pedido-card">
        <router-link :to="{ name: 'pedido-detalhe', params: { id: pedido.id } }" class="pedido-card__link">
          <div class="pedido-card__topo">
            <strong>Pedido #{{ pedido.id }}</strong>
            <span class="badge" :class="classeStatus(pedido.status)">{{ rotuloStatus(pedido.status) }}</span>
          </div>
          <p class="pedido-card__meta">
            {{ formatarData(pedido.created_at) }} · {{ pedido.itens?.length ?? 0 }} item(ns)
          </p>
          <div class="pedido-card__base">
            <strong>{{ formatarMoeda(pedido.total) }}</strong>
            <span class="pedido-card__ver">Ver detalhes e rastreio →</span>
          </div>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiLoja from '@/services/apiLoja'

const pedidos = ref([])
const carregando = ref(true)
const erro = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const formatarData = (d) => new Date(d).toLocaleDateString('pt-BR')

const rotulos = {
  pendente: 'Aguardando pagamento',
  pago: 'Pago',
  enviado: 'Enviado',
  concluido: 'Concluído',
  cancelado: 'Cancelado',
}
const classes = {
  pendente: 'badge--warning',
  pago: 'badge--success',
  enviado: 'badge--info',
  concluido: 'badge--success',
  cancelado: 'badge--danger',
}
const rotuloStatus = (s) => rotulos[s] ?? s
const classeStatus = (s) => classes[s] ?? 'badge--neutral'

async function carregar() {
  carregando.value = true
  try {
    const { data } = await apiLoja.get('/minha-conta/pedidos')
    pedidos.value = data.data
  } catch (e) {
    erro.value = 'Não foi possível carregar seus pedidos agora.'
  } finally {
    carregando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.pagina { max-width: 720px; margin: 0 auto; padding: 1.5rem 1.25rem 6rem; }
.pagina__titulo { font-size: 1.3rem; margin: 0 0 .2rem; color: var(--cor-texto); }
.pagina__subtitulo { color: var(--cor-texto-suave); font-size: .88rem; margin: 0 0 1.5rem; }

.estado, .estado--erro { text-align: center; padding: 2.5rem 1rem; color: var(--cor-texto-suave); font-size: .88rem; }
.estado--erro { color: #dc2626; }

.estado-vazio { text-align: center; padding: 3rem 1rem; display: flex; flex-direction: column; align-items: center; gap: 1rem; color: var(--cor-texto-suave); }

.lista-pedidos { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .9rem; }
.pedido-card { background: var(--cor-superficie); border: 1px solid var(--cor-linha); border-radius: var(--raio-borda); overflow: hidden; }
.pedido-card__link { display: block; padding: 1rem 1.1rem; text-decoration: none; color: inherit; }
.pedido-card__topo { display: flex; justify-content: space-between; align-items: center; margin-bottom: .35rem; }
.pedido-card__topo strong { color: var(--cor-texto); }
.pedido-card__meta { font-size: .78rem; color: var(--cor-texto-suave); margin: 0 0 .75rem; }
.pedido-card__base { display: flex; justify-content: space-between; align-items: center; }
.pedido-card__base strong { color: var(--cor-texto); font-size: 1.05rem; }
.pedido-card__ver { font-size: .78rem; color: var(--cor-primaria); font-weight: 600; }

.badge { font-size: .68rem; font-weight: 700; padding: .28rem .6rem; border-radius: 999px; }
.badge--success { background: #dcfce7; color: #16a34a; }
.badge--warning { background: #fef3c7; color: #d97706; }
.badge--info { background: #eff6ff; color: #2563eb; }
.badge--danger { background: #fee2e2; color: #dc2626; }
.badge--neutral { background: var(--cor-fundo); color: var(--cor-texto-suave); }

.btn { border: none; border-radius: var(--raio-borda); padding: .7rem 1.2rem; font-weight: 700; font-size: .88rem; text-decoration: none; }
.btn--primario { background: var(--cor-primaria); color: #fff; }
</style>
