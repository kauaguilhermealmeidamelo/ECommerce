<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">{{ modoEdicao ? 'Editar produto' : 'Novo produto' }}</h1>
    <p class="pagina__subtitulo">{{ modoEdicao ? 'Atualize os dados da peça.' : 'Cadastre uma nova peça na sua loja.' }}</p>

    <div v-if="carregandoProduto">Carregando...</div>

    <form v-else class="form" @submit.prevent="salvar">
      <label>
        Nome
        <input v-model="produto.nome" required maxlength="255" placeholder="Ex: Vestido floral P" />
      </label>

      <label>
        Categoria
        <select v-model="produto.categoria_id" required>
          <option disabled value="">Selecione...</option>
          <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nome }}</option>
        </select>
        <span v-if="!carregandoCategorias && categorias.length === 0" class="aviso-categorias">
          Nenhuma categoria cadastrada ainda — crie uma categoria antes de cadastrar produtos.
        </span>
      </label>

      <div class="campos__linha">
        <label>
          Preço (R$)
          <input v-model.number="produto.preco" type="number" step="0.01" min="0" required />
        </label>
        <label>
          Estoque
          <input v-model.number="produto.estoque" type="number" min="0" />
        </label>
      </div>

      <label>
        Descrição
        <textarea v-model="produto.descricao" rows="3" placeholder="Opcional"></textarea>
      </label>

      <p v-if="erro" class="pagina__erro">{{ erro }}</p>

      <div class="acoes">
        <button type="button" class="btn btn--secundario" @click="cancelar">Cancelar</button>
        <button type="submit" class="btn btn--primario" :disabled="salvando">
          {{ salvando ? 'Salvando...' : 'Salvar produto' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

// Quando a rota é /produtos/:id/editar, o router passa "id" como prop
// (ver router/index.js). Sem id, é modo de criação.
const props = defineProps({ id: { type: [String, Number], default: null } })

const router = useRouter()
const modoEdicao = computed(() => !!props.id)

const produto = ref({
  nome: '',
  categoria_id: '',
  preco: null,
  estoque: 0,
  descricao: '',
})

const categorias = ref([])
const carregandoCategorias = ref(true)
const carregandoProduto = ref(false)
const salvando = ref(false)
const erro = ref(null)

async function carregarCategorias() {
  carregandoCategorias.value = true
  try {
    const { data } = await api.get('/admin/categorias')
    categorias.value = data.data
  } catch (e) {
    categorias.value = []
  } finally {
    carregandoCategorias.value = false
  }
}

async function carregarProduto() {
  if (!modoEdicao.value) return

  carregandoProduto.value = true
  try {
    const { data } = await api.get(`/admin/produtos/${props.id}`)
    produto.value = {
      nome: data.data.nome,
      categoria_id: data.data.categoria_id,
      preco: data.data.preco,
      estoque: data.data.estoque,
      descricao: data.data.descricao ?? '',
    }
  } catch (e) {
    erro.value = 'Não foi possível carregar este produto.'
  } finally {
    carregandoProduto.value = false
  }
}

async function salvar() {
  salvando.value = true
  erro.value = null

  try {
    if (modoEdicao.value) {
      await api.put(`/admin/produtos/${props.id}`, produto.value)
    } else {
      await api.post('/admin/produtos', produto.value)
    }
    router.push({ name: 'produtos' })
  } catch (e) {
    // Erros de validação do Laravel (422) trazem a 1ª mensagem em errors
    const erros = e.response?.data?.errors
    erro.value = erros ? Object.values(erros)[0][0] : 'Não foi possível salvar o produto. Confira os campos.'
  } finally {
    salvando.value = false
  }
}

function cancelar() {
  router.push({ name: 'produtos' })
}

onMounted(() => {
  carregarCategorias()
  carregarProduto()
})
</script>

<style scoped>
.form { display: flex; flex-direction: column; gap: 1rem; }
.form label { display: flex; flex-direction: column; gap: .4rem; font-size: .85rem; color: var(--ink-soft); }
textarea {
  font-family: inherit;
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: .6rem .75rem;
  font-size: .9rem;
  resize: vertical;
}
select {
  background: #fff;
}
.campos__linha { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
.aviso-categorias { color: var(--danger); font-size: .78rem; }
.acoes { display: flex; gap: .75rem; margin-top: .5rem; }
.acoes .btn { flex: 1; }
.pagina__erro { color: var(--danger); font-size: .85rem; margin: 0; }
</style>