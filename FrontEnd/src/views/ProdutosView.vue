<template>
  <div class="pagina">
    <div class="pagina__cabecalho">
      <div>
        <h1 class="pagina__titulo">Produtos</h1>
        <p class="pagina__subtitulo">{{ filtrados.length }} encontrado{{ filtrados.length !== 1 ? 's' : '' }}</p>
      </div>
      <div class="pagina__acoes">
        <button class="btn btn--secundario" :class="{ 'btn--ativo': filtroAtivo }" @click="abrirFiltro">
          🔍 Filtros <span v-if="filtroAtivo" style="color: var(--blue-600)">●</span>
        </button>
        <router-link :to="{ name: 'categorias' }" class="btn btn--secundario">🗂️ Categorias</router-link>
        <router-link :to="{ name: 'produto-novo' }" class="btn btn--primario">+ Novo</router-link>
      </div>
    </div>

    <!-- Mini stats -->
    <div class="grade-mini-stats">
      <div class="card mini-stat">
        <div class="icon-box icon-box--verde">✅</div>
        <div><strong>{{ contagem.ativos }}</strong><span>Ativos</span></div>
      </div>
      <div class="card mini-stat">
        <div class="icon-box icon-box--ambar">⚠️</div>
        <div><strong>{{ contagem.estoqueBaixo }}</strong><span>Estoque Baixo</span></div>
      </div>
      <div class="card mini-stat">
        <div class="icon-box icon-box--vermelho">📦</div>
        <div><strong>{{ contagem.esgotados }}</strong><span>Esgotados</span></div>
      </div>
    </div>

    <div class="card">
      <div v-if="carregando" class="estado-carregando">Carregando...</div>
      <p v-else-if="erro" class="erro-mensagem" style="padding:1.5rem">{{ erro }}</p>
      <div v-else-if="filtrados.length === 0" class="estado-vazio">Nenhum produto encontrado.</div>

      <template v-else>
        <div class="tabela__scroll produtos-tabela--desktop">
          <table class="tabela">
            <thead>
              <tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              <tr v-for="produto in filtrados" :key="produto.id">
                <td>
                  <div class="produto-linha" @click="irParaEdicao(produto)">
                    <img v-if="capaDoProduto(produto)" :src="capaDoProduto(produto)" class="produto-linha__imagem" alt="" />
                    <div v-else class="produto-linha__imagem produto-linha__imagem--vazia">🏷️</div>
                    <span>{{ produto.nome }}</span>
                  </div>
                </td>
                <td>{{ produto.categoria?.nome ?? '—' }}</td>
                <td><strong>{{ formatarMoeda(produto.preco) }}</strong></td>
                <td>{{ produto.estoque_total }} un.</td>
                <td><span class="badge" :class="classeStatus(produto).badge">{{ classeStatus(produto).rotulo }}</span></td>
                <td>
                  <div class="acoes-linha">
                    <button class="btn btn--fantasma" @click="irParaEdicao(produto)" title="Editar">✏️</button>
                    <button class="btn btn--fantasma" @click="confirmarExclusao(produto)" title="Remover">🗑️</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="lista-mobile produtos-tabela--mobile">
          <div v-for="produto in filtrados" :key="produto.id" class="lista-mobile__item" @click="irParaEdicao(produto)">
            <img v-if="capaDoProduto(produto)" :src="capaDoProduto(produto)" class="produto-linha__imagem" alt="" />
            <div v-else class="produto-linha__imagem produto-linha__imagem--vazia">🏷️</div>
            <div style="flex:1; min-width:0">
              <strong>{{ produto.nome }}</strong>
              <p class="produtos__meta">{{ produto.categoria?.nome }}</p>
            </div>
            <div style="text-align:right">
              <strong>{{ formatarMoeda(produto.preco) }}</strong>
              <div><span class="badge" :class="classeStatus(produto).badge">{{ classeStatus(produto).rotulo }}</span></div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Filtro -->
    <Modal :aberto="filtroModalAberto" titulo="Filtrar Produtos" @fechar="filtroModalAberto = false">
      <div class="filtro-campo">
        <label>Buscar</label>
        <div class="busca"><span>🔍</span><input v-model="filtroPendente.busca" placeholder="Nome do produto..." /></div>
      </div>
      <div class="filtro-campo">
        <label>Categoria</label>
        <div class="filtro-chips">
          <button v-for="c in categoriasDisponiveis" :key="c" class="filtro-chip" :class="{ 'filtro-chip--ativo': filtroPendente.categoria === c }" @click="filtroPendente.categoria = c">{{ c }}</button>
        </div>
      </div>
      <div class="filtro-campo">
        <label>Status</label>
        <div class="filtro-chips">
          <button v-for="s in ['Todos', 'Ativo', 'Estoque Baixo', 'Esgotado']" :key="s" class="filtro-chip" :class="{ 'filtro-chip--ativo': filtroPendente.status === s }" @click="filtroPendente.status = s">{{ s }}</button>
        </div>
      </div>

      <template #rodape>
        <button class="btn btn--fantasma" @click="limparFiltro">Limpar</button>
        <button class="btn btn--primario" @click="aplicarFiltro">Aplicar</button>
      </template>
    </Modal>

    <!-- Confirmar exclusão -->
    <Modal :aberto="produtoParaExcluir !== null" titulo="Remover Produto" @fechar="produtoParaExcluir = null">
      <p style="font-size:.85rem; color: var(--ink-soft)">
        Tem certeza que deseja remover <strong>{{ produtoParaExcluir?.nome }}</strong>? Essa ação não pode ser desfeita.
      </p>
      <template #rodape>
        <button class="btn btn--fantasma" @click="produtoParaExcluir = null">Cancelar</button>
        <button class="btn btn--perigo" @click="excluirProduto">Remover</button>
      </template>
    </Modal>

    <Toast :mensagem="toastMsg" :tipo="toastTipo" @fechar="toastMsg = ''" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import Toast from '@/components/Toast.vue'

const router = useRouter()

const produtos = ref([])
const carregando = ref(true)
const erro = ref(null)

const toastMsg = ref('')
const toastTipo = ref('success')

const filtroModalAberto = ref(false)
const filtro = ref({ busca: '', categoria: 'Todos', status: 'Todos' })
const filtroPendente = ref({ busca: '', categoria: 'Todos', status: 'Todos' })
const produtoParaExcluir = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const capaDoProduto = (p) => p.imagens?.[0]?.url ?? p.imagem_url ?? null

function classeStatus(produto) {
  if (!produto.ativo || produto.estoque_total === 0) return { rotulo: 'Esgotado', badge: 'badge--danger' }
  if (produto.estoque_total <= 3) return { rotulo: 'Estoque Baixo', badge: 'badge--warning' }
  return { rotulo: 'Ativo', badge: 'badge--success' }
}

const categoriasDisponiveis = computed(() => ['Todos', ...new Set(produtos.value.map((p) => p.categoria?.nome).filter(Boolean))])

const contagem = computed(() => ({
  ativos: produtos.value.filter((p) => classeStatus(p).rotulo === 'Ativo').length,
  estoqueBaixo: produtos.value.filter((p) => classeStatus(p).rotulo === 'Estoque Baixo').length,
  esgotados: produtos.value.filter((p) => classeStatus(p).rotulo === 'Esgotado').length,
}))

const filtroAtivo = computed(() => filtro.value.busca || filtro.value.categoria !== 'Todos' || filtro.value.status !== 'Todos')

const filtrados = computed(() => produtos.value.filter((p) => {
  const buscaOk = p.nome.toLowerCase().includes(filtro.value.busca.toLowerCase())
  const categoriaOk = filtro.value.categoria === 'Todos' || p.categoria?.nome === filtro.value.categoria
  const statusOk = filtro.value.status === 'Todos' || classeStatus(p).rotulo === filtro.value.status
  return buscaOk && categoriaOk && statusOk
}))

function abrirFiltro() {
  filtroPendente.value = { ...filtro.value }
  filtroModalAberto.value = true
}
function aplicarFiltro() {
  filtro.value = { ...filtroPendente.value }
  filtroModalAberto.value = false
}
function limparFiltro() {
  filtro.value = { busca: '', categoria: 'Todos', status: 'Todos' }
  filtroPendente.value = { ...filtro.value }
  filtroModalAberto.value = false
}

function irParaEdicao(produto) {
  router.push({ name: 'produto-editar', params: { id: produto.id } })
}

function confirmarExclusao(produto) {
  produtoParaExcluir.value = produto
}

async function excluirProduto() {
  try {
    await api.delete(`/admin/produtos/${produtoParaExcluir.value.id}`)
    produtos.value = produtos.value.filter((p) => p.id !== produtoParaExcluir.value.id)
    toastTipo.value = 'success'
    toastMsg.value = 'Produto removido.'
  } catch (e) {
    toastTipo.value = 'error'
    toastMsg.value = 'Não foi possível remover o produto.'
  } finally {
    produtoParaExcluir.value = null
  }
}

async function carregar() {
  carregando.value = true
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
.mini-stat { padding: .9rem; display: flex; align-items: center; gap: .7rem; }
.mini-stat strong { display: block; font-size: 1.1rem; font-weight: 800; color: var(--ink); }
.mini-stat span { font-size: .7rem; color: var(--ink-soft); }

.produto-linha { display: flex; align-items: center; gap: .6rem; cursor: pointer; }
.produto-linha__imagem { width: 40px; height: 40px; border-radius: var(--radius-sm); object-fit: cover; background: var(--blue-50); flex-shrink: 0; }
.produto-linha__imagem--vazia { display: flex; align-items: center; justify-content: center; }
.acoes-linha { display: flex; gap: .2rem; }
.acoes-linha .btn { padding: .4rem .5rem; }

.produtos__meta { font-size: .72rem; color: var(--ink-faint); margin: .1rem 0 0; }

.filtro-campo { margin-bottom: 1.1rem; }
.filtro-campo label { display: block; font-size: .75rem; font-weight: 700; color: var(--ink); margin-bottom: .45rem; }
.filtro-chips { display: flex; flex-wrap: wrap; gap: .4rem; }

.produtos-tabela--mobile { display: flex; flex-direction: column; }
.produtos-tabela--desktop { display: none; }
@media (min-width: 640px) {
  .produtos-tabela--mobile { display: none; }
  .produtos-tabela--desktop { display: block; }
}
</style>