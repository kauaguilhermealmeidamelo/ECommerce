<template>
  <Modal
    :aberto="aberto"
    :titulo="modoEdicao ? 'Editar produto' : 'Novo produto'"
    @fechar="fechar"
  >
    <div v-if="carregandoProduto" style="text-align: center; padding: 2rem">
      Carregando...
    </div>

    <form v-else class="form" @submit.prevent="salvar">
      <label>
        Nome
        <input
          v-model="produto.nome"
          required
          maxlength="255"
          placeholder="Ex: Vestido floral P"
        />
      </label>

      <label>
        Categoria
        <select v-model="produto.categoria_id" required>
          <option disabled value="">Selecione...</option>
          <option
            v-for="cat in categoriasFlat"
            :key="cat.id"
            :value="cat.id"
            :disabled="!cat.eh_folha"
          >
            {{ "—".repeat(cat.nivel) }} {{ cat.nome
            }}{{ !cat.eh_folha ? " (selecione uma subcategoria)" : "" }}
          </option>
        </select>
        <span
          v-if="!carregandoCategorias && categoriasFlat.length === 0"
          class="aviso-categorias"
        >
          Nenhuma categoria cadastrada ainda — crie uma categoria antes de
          cadastrar produtos.
        </span>
      </label>

      <label class="preco">
        Preço (R$)
        <input
          v-model.number="produto.preco"
          type="number"
          step="0.01"
          min="0"
          required
        />
      </label>

      <!-- Imagens -->
      <div class="imagens">
        <span class="imagens__label">Fotos do produto</span>
        <p class="imagens__ajuda">
          A primeira foto é a capa. As demais formam o carrossel na página do
          produto.
        </p>

        <div class="imagens__grade">
          <div
            v-for="img in imagensExistentesVisiveis"
            :key="'existente-' + img.id"
            class="imagens__item"
          >
            <img :src="img.url" alt="" />
            <button
              type="button"
              class="imagens__remover"
              @click="removerImagemExistente(img.id)"
            >
              ×
            </button>
          </div>

          <div
            v-for="(img, i) in novasImagens"
            :key="'nova-' + i"
            class="imagens__item"
          >
            <img :src="img.previewUrl" alt="" />
            <button
              type="button"
              class="imagens__remover"
              @click="removerImagemNova(i)"
            >
              ×
            </button>
          </div>

          <label v-if="totalImagens < 8" class="imagens__adicionar">
            <span>+ Foto</span>
            <input
              type="file"
              accept="image/*"
              multiple
              @change="adicionarImagens"
              hidden
            />
          </label>
        </div>
      </div>

      <label class="toggle">
        <input type="checkbox" v-model="temVariacao" />
        <span>Este produto tem variação de tamanho</span>
      </label>

      <div v-if="!temVariacao">
        <label>
          Estoque
          <input
            v-model.number="produto.estoque"
            type="number"
            min="0"
            placeholder="Quantidade dessa peça"
          />
        </label>
      </div>

      <div v-else class="variacoes">
        <span class="variacoes__label">Quantidade em estoque por tamanho</span>
        <p class="variacoes__ajuda">
          Deixe em branco ou 0 os tamanhos que não foram comprados — só os
          tamanhos com estoque acima de zero aparecem pro cliente na loja.
        </p>

        <div class="variacoes__grade">
          <label
            v-for="tamanho in tamanhosDisponiveis"
            :key="tamanho"
            class="variacoes__item"
          >
            <span>{{ tamanho }}</span>
            <input
              v-model.number="estoquePorTamanho[tamanho]"
              type="number"
              min="0"
              placeholder="0"
            />
          </label>
        </div>

        <p class="variacoes__total">
          Total em estoque: {{ totalEstoqueVariacoes }} peça(s)
        </p>
      </div>

      <label>
        Descrição
        <textarea
          v-model="produto.descricao"
          rows="3"
          placeholder="Opcional"
        ></textarea>
      </label>

      <p v-if="erro" class="pagina__erro">{{ erro }}</p>
    </form>

    <template #rodape>
      <button type="button" class="btn btn--fantasma" @click="fechar">
        Cancelar
      </button>
      <button
        type="button"
        class="btn btn--primario"
        :disabled="salvando || carregandoProduto"
        @click="salvar"
      >
        {{ salvando ? "Salvando..." : "Salvar produto" }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from "vue";
import api from "@/services/api";
import Modal from "@/components/Modal.vue";

const props = defineProps({
  aberto: { type: Boolean, default: false },
  produtoId: { type: [String, Number], default: null },
});

const emit = defineEmits(["fechar", "salvo"]);

const modoEdicao = computed(() => !!props.produtoId);
const tamanhosDisponiveis = ["PP", "P", "M", "G", "GG", "XG"];

const produto = ref({
  nome: "",
  categoria_id: "",
  preco: null,
  estoque: 0,
  descricao: "",
});

const temVariacao = ref(false);
const estoquePorTamanho = reactive(
  Object.fromEntries(tamanhosDisponiveis.map((t) => [t, 0])),
);
const totalEstoqueVariacoes = computed(() =>
  tamanhosDisponiveis.reduce(
    (soma, t) => soma + (Number(estoquePorTamanho[t]) || 0),
    0,
  ),
);

const imagensExistentes = ref([]);
const idsImagensRemovidas = ref([]);
const imagensExistentesVisiveis = computed(() =>
  imagensExistentes.value.filter(
    (img) => !idsImagensRemovidas.value.includes(img.id),
  ),
);

const novasImagens = ref([]);
const totalImagens = computed(
  () => imagensExistentesVisiveis.value.length + novasImagens.value.length,
);

const categoriasFlat = ref([]);
const carregandoCategorias = ref(false);
const carregandoProduto = ref(false);
const salvando = ref(false);
const erro = ref(null);

function resetarFormulario() {
  produto.value = {
    nome: "",
    categoria_id: "",
    preco: null,
    estoque: 0,
    descricao: "",
  };
  temVariacao.value = false;
  tamanhosDisponiveis.forEach((t) => {
    estoquePorTamanho[t] = 0;
  });
  imagensExistentes.value = [];
  idsImagensRemovidas.value = [];
  novasImagens.value.forEach((img) => URL.revokeObjectURL(img.previewUrl));
  novasImagens.value = [];
  erro.value = null;
}

async function carregarCategorias() {
  carregandoCategorias.value = true;
  try {
    const { data } = await api.get("/admin/categorias/arvore");
    categoriasFlat.value = achatarArvore(data.data);
  } catch (e) {
    categoriasFlat.value = [];
  } finally {
    carregandoCategorias.value = false;
  }
}

function achatarArvore(categorias, nivel = 0) {
  const resultado = [];
  for (const cat of categorias) {
    const filhas = cat.filhas_recursivas ?? [];
    resultado.push({
      id: cat.id,
      nome: cat.nome,
      nivel,
      eh_folha: filhas.length === 0,
    });
    if (filhas.length) resultado.push(...achatarArvore(filhas, nivel + 1));
  }
  return resultado;
}

async function carregarProduto() {
  if (!props.produtoId) {
    resetarFormulario();
    return;
  }

  carregandoProduto.value = true;
  resetarFormulario();
  try {
    const { data } = await api.get(`/admin/produtos/${props.produtoId}`);
    produto.value = {
      nome: data.data.nome,
      categoria_id: data.data.categoria_id,
      preco: data.data.preco,
      estoque: data.data.estoque,
      descricao: data.data.descricao ?? "",
    };
    imagensExistentes.value = data.data.imagens ?? [];
    const variacoes = data.data.variacoes ?? [];
    if (variacoes.length > 0) {
      temVariacao.value = true;
      variacoes.forEach((v) => {
        estoquePorTamanho[v.tamanho] = v.estoque;
      });
    }
  } catch (e) {
    erro.value = "Não foi possível carregar este produto.";
  } finally {
    carregandoProduto.value = false;
  }
}

watch(
  () => props.aberto,
  (novoValor) => {
    if (novoValor) {
      carregarCategorias();
      carregarProduto();
    }
  },
);

function adicionarImagens(evento) {
  const arquivos = Array.from(evento.target.files || []);
  const espacoDisponivel = 8 - totalImagens.value;
  arquivos.slice(0, espacoDisponivel).forEach((file) => {
    novasImagens.value.push({ file, previewUrl: URL.createObjectURL(file) });
  });
  evento.target.value = "";
}

function removerImagemExistente(id) {
  idsImagensRemovidas.value.push(id);
}

function removerImagemNova(indice) {
  URL.revokeObjectURL(novasImagens.value[indice].previewUrl);
  novasImagens.value.splice(indice, 1);
}

onBeforeUnmount(() => {
  novasImagens.value.forEach((img) => URL.revokeObjectURL(img.previewUrl));
});

async function salvar() {
  salvando.value = true;
  erro.value = null;

  const formData = new FormData();
  formData.append("nome", produto.value.nome);
  formData.append("categoria_id", produto.value.categoria_id);
  formData.append("preco", produto.value.preco);
  formData.append("descricao", produto.value.descricao || "");
  formData.append("usa_variacao", temVariacao.value ? "1" : "0");

  if (temVariacao.value) {
    tamanhosDisponiveis
      .filter((t) => Number(estoquePorTamanho[t]) > 0)
      .forEach((t, i) => {
        formData.append(`variacoes[${i}][tamanho]`, t);
        formData.append(`variacoes[${i}][estoque]`, estoquePorTamanho[t]);
      });
  } else {
    formData.append("estoque", produto.value.estoque ?? 0);
  }

  novasImagens.value.forEach((img) => formData.append("imagens[]", img.file));
  idsImagensRemovidas.value.forEach((id) =>
    formData.append("imagens_removidas[]", id),
  );

  const url = modoEdicao.value
    ? `/admin/produtos/${props.produtoId}`
    : "/admin/produtos";
  if (modoEdicao.value) formData.append("_method", "PUT");

  try {
    await api.post(url, formData);
    emit("salvo");
    fechar();
  } catch (e) {
    const erros = e.response?.data?.errors;
    erro.value = erros
      ? Object.values(erros)[0][0]
      : "Não foi possível salvar o produto. Confira os campos.";
  } finally {
    salvando.value = false;
  }
}

function fechar() {
  emit("fechar");
}
</script>

<style scoped>
.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-height: 65vh;
  overflow-y: auto;
  padding-right: 0.3rem;
}
.form label {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  font-size: 0.85rem;
  color: var(--ink-soft);
}
textarea {
  font-family: inherit;
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: 0.6rem 0.75rem;
  font-size: 0.9rem;
  resize: vertical;
}
select {
  background: #fff;
}
.aviso-categorias {
  color: var(--danger);
  font-size: 0.78rem;
}
.imagens__label {
  font-weight: 600;
  font-size: 0.85rem;
  display: block;
}
.imagens__ajuda {
  font-size: 0.78rem;
  color: var(--ink-soft);
  margin: 0.1rem 0 0.6rem;
}
.imagens__grade {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.6rem;
}
.imagens__item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  background: var(--icon-bg);
}
.imagens__item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.imagens__remover {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  font-size: 0.9rem;
  line-height: 1;
  cursor: pointer;
}
.imagens__adicionar {
  aspect-ratio: 1;
  border: 1px dashed var(--line);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.78rem;
  color: var(--ink-soft);
  cursor: pointer;
}
.toggle {
  flex-direction: row !important;
  align-items: center;
  gap: 0.5rem !important;
}
.toggle input {
  width: auto;
  accent-color: var(--navy);
}
.variacoes {
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 0.9rem;
}
.variacoes__label {
  font-weight: 600;
  font-size: 0.85rem;
  display: block;
  margin-bottom: 0.2rem;
}
.variacoes__ajuda {
  font-size: 0.78rem;
  color: var(--ink-soft);
  margin: 0 0 0.8rem;
}
.variacoes__grade {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.6rem;
}
.variacoes__item {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  font-size: 0.8rem;
  color: var(--ink-soft);
}
.variacoes__item span {
  font-weight: 700;
  color: var(--ink);
}
.variacoes__total {
  margin: 0.8rem 0 0;
  font-size: 0.82rem;
  font-weight: 600;
  text-align: right;
}
.pagina__erro {
  color: var(--danger);
  font-size: 0.85rem;
  margin: 0;
}
</style>
