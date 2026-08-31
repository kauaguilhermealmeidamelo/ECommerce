<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Carrinho</h1>

    <div v-if="carregando" class="estado">Carregando carrinho...</div>

    <div v-else-if="itens.length === 0" class="estado-vazio">
      <p>Seu carrinho está vazio.</p>
      <router-link :to="{ name: 'catalogo' }" class="btn btn--primario">Ver catálogo</router-link>
    </div>

    <template v-else>
      <ul class="itens">
        <li v-for="item in itens" :key="item.id" class="item">
          <img :src="item.produto?.imagem_url" :alt="item.produto?.nome" class="item__imagem" />
          <div class="item__info">
            <strong>{{ item.produto?.nome }}</strong>
            <span v-if="item.tamanho" class="item__tamanho">Tamanho: {{ item.tamanho }}</span>
            <span class="item__preco">{{ formatarMoeda(item.preco_unitario) }}</span>
          </div>
          <div class="item__acoes">
            <button class="item__remover" @click="remover(item)" aria-label="Remover">✕</button>
            <div class="quantidade">
              <button @click="alterarQuantidade(item, item.quantidade - 1)" :disabled="item.quantidade <= 1">−</button>
              <span>{{ item.quantidade }}</span>
              <button @click="alterarQuantidade(item, item.quantidade + 1)">+</button>
            </div>
          </div>
        </li>
      </ul>

      <div class="resumo">
        <div class="resumo__linha">
          <span>Subtotal</span>
          <strong>{{ formatarMoeda(subtotal) }}</strong>
        </div>
        <p class="resumo__aviso">Frete calculado na próxima etapa.</p>
        <router-link :to="{ name: 'checkout' }" class="btn btn--primario btn--bloco">Finalizar compra</router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiLoja from '@/services/apiLoja'

const carrinho = ref(null)
const carregando = ref(true)

const itens = computed(() => carrinho.value?.itens ?? [])
const subtotal = computed(() => itens.value.reduce((soma, i) => soma + i.quantidade * i.preco_unitario, 0))

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

async function carregar() {
  carregando.value = true
  try {
    const { data } = await apiLoja.get('/carrinho')
    carrinho.value = data.data
  } catch (e) {
    carrinho.value = null
  } finally {
    carregando.value = false
  }
}

async function alterarQuantidade(item, novaQuantidade) {
  if (novaQuantidade < 1) return
  try {
    const { data } = await apiLoja.patch(`/carrinho/itens/${item.id}`, { quantidade: novaQuantidade })
    carrinho.value = data.data
  } catch (e) {
    // mantém o valor atual se a troca falhar (ex: sem estoque suficiente)
  }
}

async function remover(item) {
  try {
    const { data } = await apiLoja.delete(`/carrinho/itens/${item.id}`)
    carrinho.value = data.data
  } catch (e) {
    // ignora — o item continua na lista se a remoção falhar
  }
}

onMounted(carregar)
</script>

<style scoped>
.pagina { max-width: 720px; margin: 0 auto; padding: 1.5rem 1.25rem 6rem; }
.pagina__titulo { font-size: 1.4rem; margin: 0 0 1.25rem; color: var(--cor-texto); }

.estado { text-align: center; padding: 2.5rem 1rem; color: var(--cor-texto-suave); font-size: .88rem; }
.estado-vazio { text-align: center; padding: 3rem 1rem; display: flex; flex-direction: column; align-items: center; gap: 1rem; color: var(--cor-texto-suave); }

.itens { list-style: none; margin: 0 0 1.5rem; padding: 0; display: flex; flex-direction: column; gap: .8rem; }
.item {
  display: flex; align-items: center; gap: .8rem;
  background: var(--cor-superficie); border: 1px solid var(--cor-linha); border-radius: var(--raio-borda); padding: .8rem;
}
.item__imagem { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; background: var(--cor-fundo); flex-shrink: 0; }
.item__info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .15rem; }
.item__info strong { font-size: .88rem; color: var(--cor-texto); }
.item__tamanho { font-size: .75rem; color: var(--cor-texto-suave); }
.item__preco { font-size: .85rem; font-weight: 700; color: var(--cor-primaria); }

.item__acoes { display: flex; flex-direction: column; align-items: flex-end; gap: .4rem; }
.item__remover { border: none; background: none; color: var(--cor-texto-suave); cursor: pointer; font-size: .8rem; }
.item__remover:hover { color: #dc2626; }
.quantidade { display: flex; align-items: center; gap: .5rem; border: 1px solid var(--cor-linha); border-radius: 999px; padding: .2rem .5rem; }
.quantidade button { border: none; background: none; width: 20px; height: 20px; cursor: pointer; font-size: .95rem; color: var(--cor-texto); }
.quantidade button:disabled { opacity: .35; cursor: not-allowed; }
.quantidade span { font-size: .8rem; font-weight: 700; width: 16px; text-align: center; }

.resumo { background: var(--cor-superficie); border: 1px solid var(--cor-linha); border-radius: var(--raio-borda); padding: 1.1rem; }
.resumo__linha { display: flex; justify-content: space-between; font-size: 1rem; margin-bottom: .3rem; color: var(--cor-texto); }
.resumo__aviso { font-size: .78rem; color: var(--cor-texto-suave); margin: 0 0 1rem; }

.btn { border: none; border-radius: var(--raio-borda); padding: .8rem 1.2rem; font-weight: 700; font-size: .9rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
.btn--primario { background: var(--cor-primaria); color: #fff; }
.btn--bloco { width: 100%; }
</style>
