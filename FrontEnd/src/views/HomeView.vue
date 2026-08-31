<template>
  <div class="home">
    <section class="hero">
      <div class="hero__conteudo">
        <span class="hero__selo">Novidades toda semana</span>
        <h1 class="font-display hero__titulo">{{ tema.nome }}</h1>
        <p class="hero__subtitulo">Peças selecionadas com cuidado, prontas pra fazer parte do seu estilo.</p>
        <router-link :to="{ name: 'catalogo' }" class="btn btn--primario">Ver catálogo</router-link>
      </div>
    </section>

    <section class="secao">
      <h2 class="secao__titulo">Compre por categoria</h2>

      <div v-if="carregando" class="estado">Carregando categorias...</div>
      <div v-else-if="categorias.length === 0" class="estado">Nenhuma categoria cadastrada ainda.</div>
      <div v-else class="categorias-grade">
        <router-link
          v-for="cat in categorias"
          :key="cat.id"
          :to="{ name: 'catalogo', query: { categoria: cat.id } }"
          class="categoria-card"
        >
          {{ cat.nome }}
        </router-link>
      </div>
    </section>

    <section class="secao secao--achadinhos">
      <div class="secao__cabecalho">
        <h2 class="secao__titulo">Achadinhos recentes</h2>
        <router-link :to="{ name: 'catalogo' }" class="secao__ver-tudo">Ver tudo →</router-link>
      </div>

      <div v-if="carregandoAchadinhos" class="estado">Carregando...</div>
      <div v-else-if="achadinhos.length === 0" class="estado">Nenhum produto novo por aqui ainda.</div>
      <div v-else class="grade-produtos">
        <ProductCard v-for="produto in achadinhos" :key="produto.id" :produto="produto" @adicionar="adicionarAoCarrinho" />
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { tema } from '@/theme/tema'
import apiLoja from '@/services/apiLoja'
import ProductCard from '@/components/ProductCard.vue'

const categorias = ref([])
const carregando = ref(true)
const achadinhos = ref([])
const carregandoAchadinhos = ref(true)

function achatarFolhas(lista) {
  const folhas = []
  for (const cat of lista) {
    const filhas = cat.filhas_recursivas ?? []
    if (filhas.length === 0) folhas.push(cat)
    else folhas.push(...achatarFolhas(filhas))
  }
  return folhas
}

async function carregarCategorias() {
  carregando.value = true
  try {
    const { data } = await apiLoja.get('/categorias/arvore')
    categorias.value = achatarFolhas(data.data)
  } catch (e) {
    categorias.value = []
  } finally {
    carregando.value = false
  }
}

async function carregarAchadinhos() {
  carregandoAchadinhos.value = true
  try {
    const { data } = await apiLoja.get('/produtos/achadinhos')
    achadinhos.value = data.data.slice(0, 8)
  } catch (e) {
    achadinhos.value = []
  } finally {
    carregandoAchadinhos.value = false
  }
}

function adicionarAoCarrinho(produto) {
  apiLoja.post('/carrinho/itens', { produto_id: produto.id, quantidade: 1 }).catch(() => {})
}

onMounted(() => {
  carregarCategorias()
  carregarAchadinhos()
})
</script>

<style scoped>
.hero {
  background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-hover));
  border-radius: 0 0 28px 28px;
  padding: 3.5rem 1.5rem;
  text-align: center;
  color: #fff;
}
.hero__selo {
  display: inline-block; font-size: .75rem; font-weight: 700; letter-spacing: .04em;
  background: rgba(255,255,255,.18); padding: .4rem .9rem; border-radius: 999px; margin-bottom: 1rem;
}
.hero__titulo { font-size: 2.2rem; margin: 0 0 .6rem; }
.hero__subtitulo { font-size: .95rem; opacity: .9; max-width: 420px; margin: 0 auto 1.5rem; }

.btn { display: inline-flex; align-items: center; justify-content: center; border: none; border-radius: var(--raio-borda); padding: .85rem 1.5rem; font-weight: 700; font-size: .92rem; text-decoration: none; cursor: pointer; }
.btn--primario { background: #fff; color: var(--cor-primaria); }

.secao { max-width: 1100px; margin: 0 auto; padding: 2.5rem 1.25rem 0; }
.secao--achadinhos { padding-bottom: 3rem; }
.secao__titulo { font-family: var(--fonte-display); font-size: 1.2rem; color: var(--cor-texto); margin: 0 0 1.25rem; }
.secao__cabecalho { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.secao__cabecalho .secao__titulo { margin: 0; }
.secao__ver-tudo { font-size: .82rem; color: var(--cor-primaria); font-weight: 600; text-decoration: none; }

.estado { text-align: center; padding: 2rem 1rem; color: var(--cor-texto-suave); font-size: .85rem; }

.categorias-grade { display: grid; grid-template-columns: repeat(2, 1fr); gap: .8rem; }
@media (min-width: 640px) { .categorias-grade { grid-template-columns: repeat(4, 1fr); } }
.categoria-card {
  background: var(--cor-superficie); border: 1px solid var(--cor-linha); border-radius: var(--raio-borda);
  padding: 1.1rem; text-align: center; font-weight: 700; color: var(--cor-texto); text-decoration: none;
  transition: border-color .15s, color .15s;
}
.categoria-card:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }

.grade-produtos { display: grid; grid-template-columns: repeat(2, 1fr); gap: .9rem; }
@media (min-width: 640px) { .grade-produtos { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 900px) { .grade-produtos { grid-template-columns: repeat(4, 1fr); } }
</style>
