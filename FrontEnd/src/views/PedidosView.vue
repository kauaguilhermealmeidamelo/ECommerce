<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Pedidos</h1>
    <p class="pagina__subtitulo">Últimos pedidos recebidos.</p>

    <div v-if="carregando">Carregando...</div>
    <div v-else-if="erro" class="pagina__erro">{{ erro }}</div>

    <ul v-else class="lista">
      <li v-for="pedido in pedidos" :key="pedido.id" class="item">
        <div class="item__info">
          <strong>Pedido #{{ pedido.id }}</strong>
          <span class="item__data">{{ formatarData(pedido.created_at) }}</span>
        </div>

        <div class="item__valores">
          <strong>{{ formatarMoeda(pedido.total) }}</strong>
          <span class="badge" :class="`badge--${pedido.status}`">{{ pedido.status }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const pedidos = ref([])
const carregando = ref(true)
const erro = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const formatarData = (d) => new Date(d).toLocaleDateString('pt-BR')

async function carregar() {
  try {
    const { data } = await api.get('/admin/pedidos')
    pedidos.value = data.data
  } catch (e) {
    erro.value = 'Não foi possível carregar os pedidos.'
  } finally {
    carregando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.lista { list-style: none; padding: 0; margin: 0; }
.item { display: flex; justify-content: space-between; align-items: center; padding: .8rem 0; border-bottom: 1px solid var(--line); }
.item__info { display: flex; flex-direction: column; gap: .15rem; }
.item__data { font-size: .8rem; color: var(--ink-soft); }
.item__valores { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; }
.badge { font-size: .72rem; font-weight: 600; padding: .2rem .55rem; border-radius: 999px; text-transform: capitalize; background: var(--icon-bg); color: var(--navy); }
.pagina__erro { color: var(--danger); }
</style>
