<template>
  <div class="pagina">
    <div class="cabecalho">
      <div>
        <h1 class="font-display pagina__titulo">Produtos</h1>
        <p class="pagina__subtitulo">Peças cadastradas na sua loja.</p>
      </div>
      <div class="cabecalho__acoes">
        <button class="btn btn--secundario" @click="router.push({ name: 'categorias' })">Categorias</button>
        <button class="btn btn--primario" @click="router.push({ name: 'produto-novo' })">+ Novo</button>
      </div>
    </div>

    <div v-if="carregando">Carregando...</div>
    <div v-else-if="erro" class="pagina__erro">{{ erro }}</div>
    <div v-else-if="produtos.length === 0" class="vazio">Nenhum produto cadastrado ainda.</div>

    <ul v-else class="lista">
      <li
        v-for="produto in produtos"
        :key="produto.id"
        class="item"
        @click="router.push({ name: 'produto-editar', params: { id: produto.id } })"
      >
        <img v-if="capaDoProduto(produto)" :src="capaDoProduto(produto)" class="item__imagem" alt="" />
        <div v-else class="item__imagem item__imagem--vazia">🏷️</div>

        <div class="item__info">
          <strong>{{ produto.nome }}</strong>
          <span class="item__categoria">{{ produto.categoria?.nome }}</span>
        </div>

        <div class="item__valores">
          <strong>{{ formatarMoeda(produto.preco) }}</strong>
          <span class="item__estoque" :class="{ 'item__estoque--baixo': produto.estoque_total <= 2 }">
            {{ produto.estoque_total }} un.
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const produtos = ref([])
const carregando = ref(true)
const erro = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const capaDoProduto = (produto) => produto.imagens?.[0]?.url ?? produto.imagem_url ?? null

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
.cabecalho { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; gap: .75rem; }
.cabecalho__acoes { display: flex; gap: .5rem; flex-shrink: 0; }
.lista { list-style: none; padding: 0; margin: 0; }
.item { display: flex; align-items: center; gap: .75rem; padding: .75rem 0; border-bottom: 1px solid var(--line); cursor: pointer; }
.item:hover { background: #fafafa; }
.item__imagem { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; background: var(--icon-bg); }
.item__imagem--vazia { display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.item__info { flex: 1; display: flex; flex-direction: column; gap: .15rem; }
.item__categoria { font-size: .8rem; color: var(--ink-soft); }
.item__valores { text-align: right; display: flex; flex-direction: column; gap: .15rem; }
.item__estoque { font-size: .78rem; color: var(--ink-soft); }
.item__estoque--baixo { color: var(--danger); font-weight: 600; }
.pagina__erro { color: var(--danger); }
.vazio { color: var(--ink-soft); text-align: center; padding: 2rem 0; }
</style>