<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Categorias</h1>
    <p class="pagina__subtitulo">Organize categorias, subcategorias e sub-subcategorias da sua loja.</p>

    <section class="painel">
      <h2 class="painel__titulo">
        {{ categoriaPaiSelecionada ? `Nova subcategoria em "${categoriaPaiSelecionada.nome}"` : 'Nova categoria principal' }}
      </h2>

      <form class="form" @submit.prevent="criar">
        <label>
          Nome
          <input v-model="form.nome" required maxlength="255" placeholder="Ex: Vestidos" @input="gerarSlug" />
        </label>

        <label>
          Slug (gerado automaticamente, pode ajustar)
          <input v-model="form.slug" required maxlength="255" />
        </label>

        <label>
          Categoria pai
          <select v-model="form.categoria_pai_id">
            <option :value="null">Nenhuma — categoria principal</option>
            <option v-for="cat in categoriasFlat" :key="cat.id" :value="cat.id">
              {{ '—'.repeat(profundidade(cat)) }} {{ cat.nome }}
            </option>
          </select>
        </label>

        <p v-if="erro" class="pagina__erro">{{ erro }}</p>

        <div class="acoes">
          <button v-if="form.categoria_pai_id" type="button" class="btn btn--secundario" @click="limparPai">
            Cancelar subcategoria
          </button>
          <button type="submit" class="btn btn--primario" :disabled="salvando">
            {{ salvando ? 'Salvando...' : 'Criar categoria' }}
          </button>
        </div>
      </form>
    </section>

    <section class="painel" style="margin-top:1.25rem">
      <h2 class="painel__titulo">Estrutura atual</h2>

      <div v-if="carregando">Carregando...</div>
      <div v-else-if="arvore.length === 0" class="vazio">Nenhuma categoria cadastrada ainda.</div>

      <ul v-else class="lista-arvore">
        <CategoriaArvoreItem
          v-for="cat in arvore"
          :key="cat.id"
          :categoria="cat"
          @adicionar-filha="prepararSubcategoria"
          @atualizar="carregar"
        />
      </ul>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import CategoriaArvoreItem from '@/components/CategoriaArvoreItem.vue'

const arvore = ref([])
const categoriasFlat = ref([])
const carregando = ref(true)
const salvando = ref(false)
const erro = ref(null)
const categoriaPaiSelecionada = ref(null)

const form = ref({ nome: '', slug: '', categoria_pai_id: null })

function gerarSlug() {
  form.value.slug = form.value.nome
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // remove acentos
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
}

// Calcula quantos níveis de indentação mostrar no <select>, seguindo
// a cadeia de categoria_pai_id na lista flat.
function profundidade(categoria) {
  let nivel = 0
  let atual = categoria

  while (atual?.categoria_pai_id) {
    nivel++
    atual = categoriasFlat.value.find((c) => c.id === atual.categoria_pai_id)
    if (!atual) break
  }

  return nivel
}

function prepararSubcategoria(categoriaPai) {
  categoriaPaiSelecionada.value = categoriaPai
  form.value.categoria_pai_id = categoriaPai.id
  form.value.nome = ''
  form.value.slug = ''
  erro.value = null
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function limparPai() {
  categoriaPaiSelecionada.value = null
  form.value.categoria_pai_id = null
}

async function carregar() {
  carregando.value = true
  try {
    const [{ data: dataArvore }, { data: dataFlat }] = await Promise.all([
      api.get('/admin/categorias/arvore'),
      api.get('/admin/categorias'),
    ])
    arvore.value = dataArvore.data
    categoriasFlat.value = dataFlat.data
  } catch (e) {
    erro.value = 'Não foi possível carregar as categorias.'
  } finally {
    carregando.value = false
  }
}

async function criar() {
  salvando.value = true
  erro.value = null

  try {
    await api.post('/admin/categorias', form.value)
    form.value = { nome: '', slug: '', categoria_pai_id: null }
    categoriaPaiSelecionada.value = null
    await carregar()
  } catch (e) {
    const erros = e.response?.data?.errors
    erro.value = erros ? Object.values(erros)[0][0] : 'Não foi possível criar a categoria.'
  } finally {
    salvando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.form { display: flex; flex-direction: column; gap: 1rem; }
.form label { display: flex; flex-direction: column; gap: .4rem; font-size: .85rem; color: var(--ink-soft); }
select { background: #fff; }
.acoes { display: flex; gap: .75rem; margin-top: .25rem; }
.acoes .btn { flex: 1; }
.painel__titulo { font-size: .82rem; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-soft); margin: 0 0 1rem; font-weight: 700; }
.lista-arvore { list-style: none; padding: 0; margin: 0; }
.vazio { color: var(--ink-soft); text-align: center; padding: 1.5rem 0; }
.pagina__erro { color: var(--danger); font-size: .85rem; margin: 0; }
</style>