<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Clientes</h1>
    <p class="pagina__subtitulo">Quem já comprou na sua loja.</p>

    <div v-if="carregando">Carregando...</div>
    <div v-else-if="erro" class="pagina__erro">{{ erro }}</div>
    <div v-else-if="clientes.length === 0" class="vazio">Nenhum cliente cadastrado ainda.</div>

    <ul v-else class="lista">
      <li v-for="cliente in clientes" :key="cliente.id" class="item">
        <div class="item__avatar">{{ inicial(cliente.nome) }}</div>
        <div class="item__info">
          <strong>{{ cliente.nome }}</strong>
          <span class="item__contato">{{ cliente.email }}</span>
          <span v-if="cliente.telefone" class="item__contato">{{ cliente.telefone }}</span>
        </div>
        <div class="item__valores">
          <strong>{{ formatarMoeda(cliente.total_gasto) }}</strong>
          <span class="item__pedidos">{{ cliente.total_pedidos }} pedido(s)</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const clientes = ref([])
const carregando = ref(true)
const erro = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const inicial = (nome) => (nome ?? '?').trim().charAt(0).toUpperCase()

async function carregar() {
  try {
    const { data } = await api.get('/admin/clientes')
    clientes.value = data.data
  } catch (e) {
    erro.value = 'Não foi possível carregar os clientes.'
  } finally {
    carregando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.lista { list-style: none; padding: 0; margin: 0; }
.item { display: flex; align-items: center; gap: .75rem; padding: .75rem 0; border-bottom: 1px solid var(--line); }
.item__avatar {
  width: 40px; height: 40px; border-radius: 50%;
  background: var(--icon-bg); color: var(--navy);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; flex-shrink: 0;
}
.item__info { flex: 1; display: flex; flex-direction: column; gap: .1rem; min-width: 0; }
.item__contato { font-size: .78rem; color: var(--ink-soft); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.item__valores { text-align: right; display: flex; flex-direction: column; gap: .1rem; }
.item__pedidos { font-size: .75rem; color: var(--ink-soft); }
.vazio { color: var(--ink-soft); text-align: center; padding: 2rem 0; }
.pagina__erro { color: var(--danger); }
</style>
