<!-- src/views/ProductsView.vue -->
<template>
  <v-container class="dashboard-container py-8 px-6" fluid>
    <!-- Cabeçalho da Página -->
    <div class="d-flex flex-column flex-sm-row justify-space-between align-sm-center gap-4 mb-8">
      <div>
        <h1 class="text-h4 font-weight-bold text-grey-darken-4 tracking-tight">Produtos</h1>
        <p class="text-body-2 text-grey-darken-1 mt-1">Gerencie seu catálogo de produtos, preços e estoque</p>
      </div>
      <div>
        <v-btn color="indigo" prepend-icon="mdi-plus" class="text-none font-weight-medium rounded-lg px-5" elevation="0"
          @click="dialogAdicionar = true">
          Adicionar Produto
        </v-btn>
      </div>
    </div>

    <!-- Barra de Filtros e Busca Moderna -->
    <v-card class="card-custom pa-4 mb-6" elevation="0">
      <v-row align="center" no-gutters>
        <v-col cols="12" md="8">
          <v-text-field v-model="busca" placeholder="Buscar por nome, código ou categoria..."
            prepend-inner-icon="mdi-magnify" variant="solo" flat density="comfortable" hide-details class="saas-input"
            autocomplete="off" />
        </v-col>
        <v-col cols="12" md="4" class="d-flex justify-md-end align-center mt-3 mt-md-0 gap-3">
          <v-btn variant="outlined" color="grey-darken-2" prepend-icon="mdi-filter-variant"
            class="text-none rounded-lg font-weight-medium px-5" style="border-color: #cbd5e1;">
            Filtrar
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Estado de Carregamento -->
    <v-card v-if="carregando" class="card-custom pa-12 text-center" elevation="0">
      <v-progress-circular indeterminate color="indigo" size="48" width="4" class="mb-4" />
      <p class="text-body-2 text-grey-darken-1 font-weight-medium">Carregando catálogo...</p>
    </v-card>

    <!-- Estado de Erro -->
    <v-card v-else-if="erro" class="card-custom pa-12 text-center" elevation="0">
      <div class="empty-state py-8">
        <v-avatar color="red-lighten-5" size="64" rounded="circle" class="mb-4">
          <v-icon icon="mdi-alert-circle-outline" size="32" color="error" />
        </v-avatar>
        <h3 class="text-h6 font-weight-bold text-grey-darken-4 mb-1">Falha na conexão</h3>
        <p class="text-body-2 text-grey mb-6">Ocorreu um erro interno ao tentar buscar os registros.</p>
        <v-btn color="indigo" variant="flat" class="text-none font-weight-medium rounded-lg px-6" elevation="0"
          @click="carregarProdutos">
          Tentar de novo
        </v-btn>
      </div>
    </v-card>

    <!-- Tabela de Produtos Clean -->
    <v-card v-else class="card-custom pa-0 overflow-hidden" elevation="0">
      <v-table class="saas-table" theme="light">
        <thead>
          <tr>
            <th class="text-left">PRODUTO</th>
            <th class="text-left">CATEGORIA</th>
            <th class="text-right">PREÇO</th>
            <th class="text-right">ESTOQUE</th>
            <th class="text-center" style="width: 100px;">AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="produto in produtosFiltrados" :key="produto.id">
            <td class="py-3">
              <div class="d-flex align-center gap-3">
                <v-avatar color="indigo-lighten-5" size="36" rounded="lg">
                  <v-icon icon="mdi-tag-outline" size="small" color="indigo-darken-2"></v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-bold text-grey-darken-4">{{ produto.nome || produto.name }}</div>
                  <div class="text-caption text-grey">SKU: {{ produto.sku || 'N/A' }}</div>
                </div>
              </div>
            </td>
            <td class="text-grey-darken-2 font-weight-medium">{{ produto.categoria || produto.category || 'Geral' }}
            </td>
            <td class="text-right font-weight-bold text-grey-darken-4">{{ formatarMoeda(produto.preco || produto.price)
              }}</td>
            <td class="text-right text-grey-darken-2">
              <v-chip :color="(produto.estoque ?? produto.stock) > 0 ? 'success' : 'error'" size="x-small"
                variant="flat" class="font-weight-bold">
                {{ produto.estoque ?? produto.stock ?? 0 }} un.
              </v-chip>
            </td>
            <td class="text-center">
              <v-btn icon="mdi-pencil-outline" size="small" variant="text" color="grey-darken-2" />
              <v-btn icon="mdi-delete-outline" size="small" variant="text" color="error"
                @click="deletarProduto(produto.id)" />
            </td>
          </tr>
          <tr v-if="produtosFiltrados.length === 0">
            <td colspan="5" class="text-center py-10 text-grey-darken-1 font-weight-medium">Nenhum produto encontrado.
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Modal Premium para Adicionar Produto -->
    <v-dialog v-model="dialogAdicionar" max-width="500px" transition="dialog-bottom-transition">
      <v-card class="modal-card pa-6" elevation="0">
        <div class="d-flex justify-space-between align-center mb-6">
          <h3 class="text-h5 font-weight-bold text-grey-darken-4 mb-0">Novo Produto</h3>
          <v-btn icon="mdi-close" variant="text" size="small" color="grey-darken-1"
            @click="dialogAdicionar = false"></v-btn>
        </div>

        <div class="form-group mb-4">
          <label class="form-label">Nome do Produto</label>
          <v-text-field v-model="novoProduto.nome" variant="solo" flat density="comfortable" class="saas-input"
            autocomplete="off" hide-details />
        </div>

        <div class="form-group mb-4">
          <label class="form-label">Categoria</label>
          <v-text-field v-model="novoProduto.categoria" variant="solo" flat density="comfortable" class="saas-input"
            autocomplete="off" hide-details />
        </div>

        <div class="form-group mb-4">
          <label class="form-label">Preço (R$)</label>
          <v-text-field v-model="novoProduto.preco" type="number" variant="solo" flat density="comfortable"
            class="saas-input" autocomplete="off" hide-details />
        </div>

        <div class="form-group mb-6">
          <label class="form-label">Quantidade em Estoque</label>
          <v-text-field v-model="novoProduto.estoque" type="number" variant="solo" flat density="comfortable"
            class="saas-input" autocomplete="off" hide-details />
        </div>

        <div class="d-flex justify-end gap-3 pt-2">
          <v-btn variant="text" color="grey-darken-2" class="text-none font-weight-medium"
            @click="dialogAdicionar = false">Cancelar</v-btn>
          <v-btn color="indigo" class="text-none font-weight-medium px-6" elevation="0" :loading="salvando"
            @click="salvarProduto">Salvar Cadastro</v-btn>
        </div>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const busca = ref('')
const produtos = ref([])
const carregando = ref(true)
const erro = ref(false)

const dialogAdicionar = ref(false)
const salvando = ref(false)
const novoProduto = ref({
  nome: '',
  categoria: '',
  preco: '',
  estoque: ''
})

// Constante com a URL base do seu Backend Laravel
const API_URL = 'http://localhost:8000/api/admin/produtos'

// Configuração padrão de Headers para todas as requisições
const getHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
  'Content-Type': 'application/json'
})

const carregarProdutos = async () => {
  carregando.value = true
  erro.value = false
  try {
    const resposta = await axios.get(API_URL, { headers: getHeaders() })
    produtos.value = Array.isArray(resposta.data) ? resposta.data : (resposta.data.produtos || [])
  } catch (err) {
    console.error('Erro ao buscar produtos:', err)
    erro.value = true
  } finally {
    carregando.value = false
  }
}

const salvarProduto = async () => {
  salvando.value = true
  try {
    // Apontando explicitamente para a URL do Laravel
    await axios.post(API_URL, novoProduto.value, { headers: getHeaders() })

    dialogAdicionar.value = false
    novoProduto.value = { nome: '', categoria: '', preco: '', estoque: '' }
    carregarProdutos() // Recarrega a tabela após salvar
  } catch (err) {
    console.error('Erro ao salvar produto:', err)

    // Tratamento de erro melhorado para exibir o que o Laravel recusou
    if (err.response && err.response.status === 422) {
      alert('Erro de validação: Verifique se todos os campos foram preenchidos corretamente.')
    } else {
      alert('Erro ao cadastrar produto. Verifique o console do backend.')
    }
  } finally {
    salvando.value = false
  }
}

const deletarProduto = async (id) => {
  if (!confirm('Deseja realmente excluir este produto?')) return
  try {
    await axios.delete(`${API_URL}/${id}`, { headers: getHeaders() })
    carregarProdutos()
  } catch (err) {
    console.error('Erro ao deletar produto:', err)
  }
}

const produtosFiltrados = computed(() => {
  if (!busca.value) return produtos.value
  const termo = busca.value.toLowerCase()
  return produtos.value.filter(p => {
    const nome = (p.nome || p.name || '').toLowerCase()
    const categoria = (p.categoria || p.category || '').toLowerCase()
    return nome.includes(termo) || categoria.includes(termo)
  })
})

const formatarMoeda = (valor) => {
  return Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

onMounted(() => {
  carregarProdutos()
})
</script>

<style scoped>
.dashboard-container {
  max-width: 84rem;
  margin: 0 auto;
}

/* Card Principal */
.card-custom {
  background-color: #ffffff !important;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
  border: 1px solid #f1f5f9;
}

/* Card do Modal (Com sombra de destaque premium) */
.modal-card {
  background-color: #ffffff !important;
  border-radius: 1.25rem !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
  border: 1px solid #e2e8f0;
}

/* Rótulos dos inputs do modal */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
  margin-left: 2px;
}

/* Campos de Input SaaS Premium (Substitui o outlined nativo feio) */
.saas-input :deep(.v-field) {
  border-radius: 0.75rem !important;
  background-color: #ffffff !important;
  border: 1px solid #cbd5e1 !important;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
  transition: all 0.2s ease;
}

.saas-input :deep(.v-field--focused) {
  border-color: #4f46e5 !important;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1) !important;
}

.saas-input :deep(.v-field::before),
.saas-input :deep(.v-field::after) {
  display: none !important;
  /* Remove as linhas nativas do Vuetify */
}

/* Tabela Elegante */
.saas-table {
  background-color: transparent !important;
}

.saas-table :deep(th) {
  color: #64748b !important;
  font-size: 0.75rem !important;
  font-weight: 700 !important;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background-color: #f8fafc !important;
  border-bottom: 1px solid #e2e8f0 !important;
}

.saas-table :deep(td) {
  border-bottom: 1px solid #f1f5f9 !important;
  background-color: #ffffff !important;
}

.saas-table :deep(tbody tr:hover td) {
  background-color: #f8fafc !important;
}

.empty-state {
  max-width: 400px;
  margin: 0 auto;
}
</style>