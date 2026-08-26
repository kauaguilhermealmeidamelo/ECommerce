<template>
  <div class="pagina">
    <div class="cabecalho">
      <div>
        <h1 class="font-display pagina__titulo">Produtos</h1>
        <p class="pagina__subtitulo">Peças cadastradas na sua loja.</p>
      </div>
      <button class="btn btn--primario">+ Novo</button>
    </div>

    <div v-if="carregando">Carregando...</div>
    <div v-else-if="erro" class="pagina__erro">{{ erro }}</div>

    <ul v-else class="lista">
      <li v-for="produto in produtos" :key="produto.id" class="item">
        <img v-if="produto.imagem_url" :src="produto.imagem_url" class="item__imagem" alt="" />
        <div v-else class="item__imagem item__imagem--vazia">🏷️</div>

        <div class="item__info">
          <strong>{{ produto.nome }}</strong>
          <span class="item__categoria">{{ produto.categoria?.nome }}</span>
        </div>

        <div class="item__valores">
          <strong>{{ formatarMoeda(produto.preco) }}</strong>
          <span class="item__estoque" :class="{ 'item__estoque--baixo': produto.estoque <= 2 }">
            {{ produto.estoque }} un.
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const produtos = ref([])
const carregando = ref(true)
const erro = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

async function carregar() {
  try {
    const { data } = await api.get('/admin/produtos')
    produtos.value = data.data
  } catch (e) {
    erro.value = 'Não foi possível carregar os produtos.'
  } finally {
    carregando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.cabecalho { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
.lista { list-style: none; padding: 0; margin: 0; }
.item { display: flex; align-items: center; gap: .75rem; padding: .75rem 0; border-bottom: 1px solid var(--line); }
.item__imagem { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; background: var(--icon-bg); }
.item__imagem--vazia { display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.item__info { flex: 1; display: flex; flex-direction: column; gap: .15rem; }
.item__categoria { font-size: .8rem; color: var(--ink-soft); }
.item__valores { text-align: right; display: flex; flex-direction: column; gap: .15rem; }
.item__estoque { font-size: .78rem; color: var(--ink-soft); }
.item__estoque--baixo { color: var(--danger); font-weight: 600; }
.pagina__erro { color: var(--danger); }
</style>
