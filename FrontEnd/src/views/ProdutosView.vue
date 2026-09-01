<template>
  <div class="pagina">
    <div class="pagina__cabecalho">
      <div>
        <h1 class="pagina__titulo">Produtos</h1>
        <p class="pagina__subtitulo">
          {{ filtrados.length }} encontrado{{
            filtrados.length !== 1 ? "s" : ""
          }}
        </p>
      </div>
      <div class="pagina__acoes">
        <button
          class="btn btn--secundario"
          :class="{ 'btn--ativo': filtroAtivo }"
          @click="abrirFiltro"
        >
          <v-icon icon="mdi-magnify" size="small" class="mr-1"></v-icon> Filtros
          <span v-if="filtroAtivo" style="color: var(--blue-600)">●</span>
        </button>
        <router-link :to="{ name: 'categorias' }" class="btn btn--secundario">
          <v-icon icon="mdi-shape-outline" size="small" class="mr-1"></v-icon>
          Categorias
        </router-link>
        <button class="btn btn--primario" @click="abrirModalCriacao">
          <v-icon icon="mdi-plus" size="small" class="mr-1"></v-icon> Novo
        </button>
      </div>
    </div>

    <!-- Mini stats -->
    <div class="grade-mini-stats">
      <div class="card mini-stat">
        <div class="icon-box icon-box--verde">
          <v-icon icon="mdi-check-circle-outline"></v-icon>
        </div>
        <div>
          <strong>{{ contagem.ativos }}</strong
          ><span>Ativos</span>
        </div>
      </div>
      <div class="card mini-stat">
        <div class="icon-box icon-box--ambar">
          <v-icon icon="mdi-alert-outline"></v-icon>
        </div>
        <div>
          <strong>{{ contagem.estoqueBaixo }}</strong
          ><span>Estoque Baixo</span>
        </div>
      </div>
      <div class="card mini-stat">
        <div class="icon-box icon-box--vermelho">
          <v-icon icon="mdi-package-variant-remove"></v-icon>
        </div>
        <div>
          <strong>{{ contagem.esgotados }}</strong
          ><span>Esgotados</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div v-if="carregando" class="estado-carregando">Carregando...</div>
      <p v-else-if="erro" class="erro-mensagem" style="padding: 1.5rem">
        {{ erro }}
      </p>
      <div v-else-if="filtrados.length === 0" class="estado-vazio">
        Nenhum produto encontrado.
      </div>

      <template v-else>
        <div class="tabela__scroll produtos-tabela--desktop">
          <table class="tabela">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="produto in filtrados" :key="produto.id">
                <td>
                  <div class="produto-linha" @click="abrirModalEdicao(produto)">
                    <img
                      v-if="capaDoProduto(produto)"
                      :src="capaDoProduto(produto)"
                      class="produto-linha__imagem"
                      alt=""
                    />
                    <div
                      v-else
                      class="produto-linha__imagem produto-linha__imagem--vazia"
                    >
                      <v-icon icon="mdi-tag-outline" size="small"></v-icon>
                    </div>
                    <span>{{ produto.nome }}</span>
                  </div>
                </td>
                <td>{{ produto.categoria?.nome ?? "—" }}</td>
                <td>
                  <strong>{{ formatarMoeda(produto.preco) }}</strong>
                </td>
                <td>{{ produto.estoque_total }} un.</td>
                <td>
                  <span class="badge" :class="classeStatus(produto).badge">{{
                    classeStatus(produto).rotulo
                  }}</span>
                </td>
                <td>
                  <div class="acoes-linha">
                    <button
                      class="btn btn--fantasma"
                      @click="abrirModalEdicao(produto)"
                      title="Editar"
                    >
                      <v-icon icon="mdi-pencil-outline" size="small"></v-icon>
                    </button>
                    <button
                      class="btn btn--fantasma"
                      @click="confirmarExclusao(produto)"
                      title="Remover"
                    >
                      <v-icon icon="mdi-delete-outline" size="small"></v-icon>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="lista-mobile produtos-tabela--mobile">
          <div
            v-for="produto in filtrados"
            :key="produto.id"
            class="lista-mobile__item"
            @click="abrirModalEdicao(produto)"
          >
            <img
              v-if="capaDoProduto(produto)"
              :src="capaDoProduto(produto)"
              class="produto-linha__imagem"
              alt=""
            />
            <div
              v-else
              class="produto-linha__imagem produto-linha__imagem--vazia"
            >
              <v-icon icon="mdi-tag-outline" size="small"></v-icon>
            </div>
            <div style="flex: 1; min-width: 0">
              <strong>{{ produto.nome }}</strong>
              <p class="produtos__meta">{{ produto.categoria?.nome }}</p>
            </div>
            <div style="text-align: right">
              <strong>{{ formatarMoeda(produto.preco) }}</strong>
              <div>
                <span class="badge" :class="classeStatus(produto).badge">{{
                  classeStatus(produto).rotulo
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Modal de Cadastro / Edição de Produto -->
    <ProdutoModal
      :aberto="produtoModalAberto"
      :produto-id="produtoEmEdicaoId"
      @fechar="produtoModalAberto = false"
      @salvo="carregar"
    />

    <!-- Filtro -->
    <Modal
      :aberto="filtroModalAberto"
      titulo="Filtrar Produtos"
      @fechar="filtroModalAberto = false"
    >
      <div class="filtro-campo">
        <label>Buscar</label>
        <div class="busca">
          <v-icon icon="mdi-magnify" size="small" class="ml-2"></v-icon>
          <input
            v-model="filtroPendente.busca"
            placeholder="Nome do produto..."
          />
        </div>
      </div>
      <div class="filtro-campo">
        <label>Categoria</label>
        <div class="filtro-chips">
          <button
            v-for="c in categoriasDisponiveis"
            :key="c"
            class="filtro-chip"
            :class="{ 'filtro-chip--ativo': filtroPendente.categoria === c }"
            @click="filtroPendente.categoria = c"
          >
            {{ c }}
          </button>
        </div>
      </div>
      <div class="filtro-campo">
        <label>Status</label>
        <div class="filtro-chips">
          <button
            v-for="s in ['Todos', 'Ativo', 'Estoque Baixo', 'Esgotado']"
            :key="s"
            class="filtro-chip"
            :class="{ 'filtro-chip--ativo': filtroPendente.status === s }"
            @click="filtroPendente.status = s"
          >
            {{ s }}
          </button>
        </div>
      </div>

      <template #rodape>
        <button class="btn btn--fantasma" @click="limparFiltro">Limpar</button>
        <button class="btn btn--primario" @click="aplicarFiltro">
          Aplicar
        </button>
      </template>
    </Modal>

    <!-- Confirmar exclusão -->
    <Modal
      :aberto="produtoParaExcluir !== null"
      titulo="Remover Produto"
      @fechar="produtoParaExcluir = null"
    >
      <p style="font-size: 0.85rem; color: var(--ink-soft)">
        Tem certeza que deseja remover
        <strong>{{ produtoParaExcluir?.nome }}</strong
        >? Essa ação não pode ser desfeita.
      </p>
      <template #rodape>
        <button class="btn btn--fantasma" @click="produtoParaExcluir = null">
          Cancelar
        </button>
        <button class="btn btn--perigo" @click="excluirProduto">Remover</button>
      </template>
    </Modal>

    <Toast :mensagem="toastMsg" :tipo="toastTipo" @fechar="toastMsg = ''" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import Modal from "@/components/Modal.vue";
import Toast from "@/components/Toast.vue";
import ProdutoModal from "@/components/ProdutoModal.vue";

const produtos = ref([]);
const carregando = ref(true);
const erro = ref(null);

const toastMsg = ref("");
const toastTipo = ref("success");

const filtroModalAberto = ref(false);
const produtoModalAberto = ref(false);
const produtoEmEdicaoId = ref(null);

const filtro = ref({ busca: "", categoria: "Todos", status: "Todos" });
const filtroPendente = ref({ busca: "", categoria: "Todos", status: "Todos" });
const produtoParaExcluir = ref(null);

const formatarMoeda = (v) =>
  new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(
    v ?? 0,
  );
const capaDoProduto = (p) => p.imagens?.[0]?.url ?? p.imagem_url ?? null;

function classeStatus(produto) {
  if (!produto.ativo || produto.estoque_total === 0)
    return { rotulo: "Esgotado", badge: "badge--danger" };
  if (produto.estoque_total <= 3)
    return { rotulo: "Estoque Baixo", badge: "badge--warning" };
  return { rotulo: "Ativo", badge: "badge--success" };
}

const categoriasDisponiveis = computed(() => [
  "Todos",
  ...new Set(produtos.value.map((p) => p.categoria?.nome).filter(Boolean)),
]);

const contagem = computed(() => ({
  ativos: produtos.value.filter((p) => classeStatus(p).rotulo === "Ativo")
    .length,
  estoqueBaixo: produtos.value.filter(
    (p) => classeStatus(p).rotulo === "Estoque Baixo",
  ).length,
  esgotados: produtos.value.filter((p) => classeStatus(p).rotulo === "Esgotado")
    .length,
}));

const filtroAtivo = computed(
  () =>
    filtro.value.busca ||
    filtro.value.categoria !== "Todos" ||
    filtro.value.status !== "Todos",
);

const filtrados = computed(() =>
  produtos.value.filter((p) => {
    const buscaOk = p.nome
      .toLowerCase()
      .includes(filtro.value.busca.toLowerCase());
    const categoriaOk =
      filtro.value.categoria === "Todos" ||
      p.categoria?.nome === filtro.value.categoria;
    const statusOk =
      filtro.value.status === "Todos" ||
      classeStatus(p).rotulo === filtro.value.status;
    return buscaOk && categoriaOk && statusOk;
  }),
);

function abrirModalCriacao() {
  produtoEmEdicaoId.value = null;
  produtoModalAberto.value = true;
}

function abrirModalEdicao(produto) {
  produtoEmEdicaoId.value = produto.id;
  produtoModalAberto.value = true;
}

function abrirFiltro() {
  filtroPendente.value = { ...filtro.value };
  filtroModalAberto.value = true;
}
function aplicarFiltro() {
  filtro.value = { ...filtroPendente.value };
  filtroModalAberto.value = false;
}
function limparFiltro() {
  filtro.value = { busca: "", categoria: "Todos", status: "Todos" };
  filtroPendente.value = { ...filtro.value };
  filtroModalAberto.value = false;
}

function confirmarExclusao(produto) {
  produtoParaExcluir.value = produto;
}

async function excluirProduto() {
  try {
    await api.delete(`/admin/produtos/${produtoParaExcluir.value.id}`);
    produtos.value = produtos.value.filter(
      (p) => p.id !== produtoParaExcluir.value.id,
    );
    toastTipo.value = "success";
    toastMsg.value = "Produto removido.";
  } catch (e) {
    toastTipo.value = "error";
    toastMsg.value = "Não foi possível remover o produto.";
  } finally {
    produtoParaExcluir.value = null;
  }
}

async function carregar() {
  carregando.value = true;
  try {
    const { data } = await api.get("/admin/produtos");
    produtos.value = data.data;
  } catch (e) {
    erro.value = "Não foi possível carregar os produtos.";
  } finally {
    carregando.value = false;
  }
}

onMounted(carregar);
</script>

<style scoped>
/* Estilos mantidos iguais aos anteriores */
.mini-stat {
  padding: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.7rem;
}
.mini-stat strong {
  display: block;
  font-size: 1.1rem;
  font-weight: 800;
  color: var(--ink);
}
.mini-stat span {
  font-size: 0.7rem;
  color: var(--ink-soft);
}
.produto-linha {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
}
.produto-linha__imagem {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  background: var(--blue-50);
  flex-shrink: 0;
}
.produto-linha__imagem--vazia {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--ink-faint);
}
.acoes-linha {
  display: flex;
  gap: 0.2rem;
}
.acoes-linha .btn {
  padding: 0.4rem 0.5rem;
}
.produtos__meta {
  font-size: 0.72rem;
  color: var(--ink-faint);
  margin: 0.1rem 0 0;
}
.filtro-campo {
  margin-bottom: 1.1rem;
}
.filtro-campo label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--ink);
  margin-bottom: 0.45rem;
}
.filtro-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}
.produtos-tabela--mobile {
  display: flex;
  flex-direction: column;
}
.produtos-tabela--desktop {
  display: none;
}
@media (min-width: 640px) {
  .produtos-tabela--mobile {
    display: none;
  }
  .produtos-tabela--desktop {
    display: block;
  }
}
</style>
