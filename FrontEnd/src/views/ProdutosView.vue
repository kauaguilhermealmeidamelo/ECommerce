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
          @click="abrirCadastroProduto">
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
            rounded="lg" class="filtro-btn text-none font-weight-medium px-5">
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
                  <div class="font-weight-bold text-grey-darken-4">{{ produto.nome }}</div>
                  <div class="text-caption text-grey">SKU: {{ produto.sku || 'N/A' }}</div>
                </div>
              </div>
            </td>
            <td class="text-grey-darken-2 font-weight-medium">{{ produto.categoria || 'Geral' }}
            </td>
            <td class="text-right font-weight-bold text-grey-darken-4">{{ formatarMoeda(produto.preco)
              }}</td>
            <td class="text-right text-grey-darken-2">
              <v-chip :color="produto.estoque > 0 ? 'success' : 'error'" size="x-small"
                variant="flat" class="font-weight-bold">
                {{ produto.estoque ?? 0 }} un.
              </v-chip>
            </td>
            <td class="text-center">
              <v-btn icon="mdi-pencil-outline" size="small" variant="text" color="grey-darken-2"
                aria-label="Editar produto" @click="editarProduto(produto.id)" />
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

    <v-dialog v-model="dialogCadastro" width="640px" max-width="calc(100vw - 32px)" max-height="90vh" scrollable>
      <v-card class="produto-dialog-card">
        <v-card-title class="d-flex align-center justify-space-between px-6 py-4">
          <span class="text-h6 font-weight-bold">Novo produto</span>
          <v-btn icon="mdi-close" variant="text" aria-label="Fechar" @click="dialogCadastro = false" />
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-0">
          <ProdutoFormView v-model:dialog="dialogCadastro" @salvo="produtoSalvo" @cancelado="dialogCadastro = false" />
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProdutoListViewModel } from '@/viewmodels/useProdutoListViewModel'
import ProdutoFormView from '@/views/ProdutoFormView.vue'

const dialogCadastro = ref(false)
const router = useRouter()

const {
  filtroTexto: busca,
  carregando,
  erro,
  produtosFiltrados,
  recarregar: carregarProdutos,
  salvarProduto,
  deletarProduto,
} = useProdutoListViewModel()

function abrirCadastroProduto() { dialogCadastro.value = true }
function editarProduto(id: number) { router.push({ name: 'produto-editar', params: { id } }) }
async function produtoSalvo() {
  dialogCadastro.value = false
  await carregarProdutos()
}

const formatarMoeda = (valor: number | string | null | undefined) => {
  return Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
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

.produto-dialog-card {
  overflow: hidden;
  max-height: 90vh;
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
  border-radius: 0.7rem !important;
  background-color: #ffffff !important;
  border: 1px solid #cbd5e1 !important;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
  transition: all 0.2s ease;
}

.filtro-btn {
  min-height: 42px;
  border: 1px solid #cbd5e1 !important;
  border-radius: .7rem !important;
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