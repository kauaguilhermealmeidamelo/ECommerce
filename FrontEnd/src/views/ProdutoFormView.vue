<template>
  <div :class="dialog ? 'produto-form-dialog' : 'pagina'">
    <div class="formulario-cabecalho">
      <div>
        <h1 class="pagina__titulo">{{ modoEdicao ? 'Editar produto' : 'Novo produto' }}</h1>
        <p class="pagina__subtitulo">{{ modoEdicao ? 'Atualize os dados do produto.' : 'Cadastre um novo produto na sua loja.' }}</p>
      </div>
      <span class="formulario-etapa">Informações do produto</span>
    </div>

    <div v-if="carregandoProduto">Carregando...</div>

    <form v-else class="form" @submit.prevent="salvar">
      <section class="formulario-secao">
        <div class="secao-cabecalho">
          <div>
            <h2>Dados básicos</h2>
            <p>Defina como o produto será apresentado no catálogo.</p>
          </div>
        </div>
        <div class="campos-grid campos-grid--dados">
          <label class="campo campo--full">
            Nome do produto
            <input v-model="produto.nome" required maxlength="255" placeholder="Ex.: Camiseta lisa de algodão" />
          </label>
          <label class="campo">
            Categoria
            <select v-model="produto.categoria_id" required>
              <option disabled value="">Selecione uma categoria</option>
              <option v-for="cat in categoriasFlat" :key="cat.id" :value="cat.id" :disabled="!cat.eh_folha">
                {{ '—'.repeat(cat.nivel) }} {{ cat.nome }}{{ !cat.eh_folha ? ' (grupo)' : '' }}
              </option>
            </select>
          </label>
          <label class="campo">
            Preço de venda
            <div class="input-prefix"><span>R$</span><input v-model.number="produto.preco" type="number" step="0.01" min="0" required placeholder="0,00" /></div>
          </label>
        </div>
      </section>

      <!-- Imagens -->
      <section class="formulario-secao imagens">
        <div class="secao-cabecalho">
          <div><h2>Fotos do produto</h2><p>A primeira foto será usada como capa do catálogo.</p></div>
          <span class="contador-midia">{{ totalImagens }}/8</span>
        </div>

        <div class="imagens__grade">
          <div v-for="img in imagensExistentesVisiveis" :key="'existente-' + img.id" class="imagens__item">
            <img :src="img.url" alt="" />
            <button type="button" class="imagens__remover" @click="removerImagemExistente(img.id)">×</button>
          </div>

          <div v-for="(img, i) in novasImagens" :key="'nova-' + i" class="imagens__item">
            <img :src="img.previewUrl" alt="" />
            <button type="button" class="imagens__remover" @click="removerImagemNova(i)">×</button>
          </div>

          <label v-if="totalImagens < 8" class="imagens__adicionar">
            <span>+ Foto</span>
            <input type="file" accept="image/*" multiple @change="adicionarImagens" hidden />
          </label>
        </div>
      </section>

      <!-- Variações Dinâmicas (Agnóstico de Nicho) -->
      <div class="variacoes-secao">
        <div class="variacoes-header">
          <div><h2>Variações e estoque</h2><p>Use variações quando o produto tiver opções como tamanho ou cor.</p></div>
          <button type="button" class="btn-adicionar-var" @click="adicionarVariacao"><v-icon icon="mdi-plus" size="small" /> Adicionar</button>
        </div>

        <div v-if="variacoes.length === 0" class="sem-variacoes">
          <div class="sem-variacoes__texto"><strong>Produto sem variações</strong><span>O estoque geral será considerado.</span></div>
          <label class="estoque-geral">
            Estoque Geral
            <input v-model.number="produto.estoque" type="number" min="0" placeholder="Quantidade" />
          </label>
        </div>

        <div v-else class="variacoes__lista">
          <div v-for="(v, index) in variacoes" :key="index" class="variacao-linha">
            <input v-model="v.nome" placeholder="Ex: 38 / 220V / 500g / P" required />
            <input v-model.number="v.preco" type="number" step="0.01" min="0" placeholder="Preço (Opcional)" />
            <input v-model.number="v.estoque" type="number" min="0" placeholder="Estoque" required />
            <button type="button" class="btn-remover-var" @click="removerVariacao(index)">Excluir</button>
          </div>
        </div>
      </div>

      <section class="formulario-secao envio-secao">
        <div class="secao-cabecalho"><div><h2>Dados de envio</h2><p>Informações usadas no cálculo de frete e embalagem.</p></div></div>
        <div class="campos-grid campos-grid--envio">
          <label class="campo">Peso (kg)<input v-model.number="produto.peso" type="number" min="0" step="0.001" placeholder="0,000" /></label>
          <label class="campo">Altura (cm)<input v-model.number="produto.altura" type="number" min="0" step="1" placeholder="0" /></label>
          <label class="campo">Largura (cm)<input v-model.number="produto.largura" type="number" min="0" step="1" placeholder="0" /></label>
          <label class="campo">Comprimento (cm)<input v-model.number="produto.comprimento" type="number" min="0" step="1" placeholder="0" /></label>
        </div>
      </section>

      <section class="formulario-secao">
        <label class="campo">Descrição<textarea v-model="produto.descricao" rows="4" placeholder="Conte os detalhes importantes para o cliente..."></textarea></label>
      </section>

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

<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import { useProdutoFormViewModel } from '@/viewmodels/useProdutoFormViewModel'

const props = defineProps({ id: { type: [String, Number], default: null } })
const dialog = defineModel<boolean>('dialog', { default: false })
const emit = defineEmits<{ salvo: []; cancelado: [] }>()

const {
  modoEdicao,
  produto,
  variacoes,
  categoriasFlat,
  carregandoCategorias,
  carregandoProduto,
  salvando,
  erro,
  imagensExistentesVisiveis,
  novasImagens,
  totalImagens,
  adicionarVariacao,
  removerVariacao,
  adicionarImagens,
  removerImagemExistente,
  removerImagemNova,
  limparPreviews,
  carregarCategorias,
  carregarProduto,
  salvar,
  cancelar,
} = useProdutoFormViewModel(props.id, {
  salvo: () => emit('salvo'),
  cancelado: () => emit('cancelado'),
})

onMounted(() => {
  carregarCategorias()
  carregarProduto()
})

onBeforeUnmount(() => {
  limparPreviews()
})
</script>

<style scoped>
/* Estilos visuais preservados */
.produto-form-dialog {
  padding: 1.75rem 2rem 2rem;
  max-width: 820px;
  margin: 0 auto;
}

.formulario-cabecalho { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: .5rem; }
.formulario-etapa { padding: .35rem .65rem; border-radius: 999px; background: var(--blue-50); color: var(--blue-600); font-size: .7rem; font-weight: 700; white-space: nowrap; }
.formulario-secao { padding: 1.15rem 0; border-bottom: 1px solid var(--line); }
.secao-cabecalho { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
.secao-cabecalho h2, .variacoes-header h2 { margin: 0; color: var(--ink); font-size: .9rem; font-weight: 750; }
.secao-cabecalho p, .variacoes-header p { margin: .25rem 0 0; color: var(--ink-soft); font-size: .75rem; }
.campos-grid { display: grid; gap: .85rem; }
.campos-grid--dados { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.campos-grid--envio { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.campo { display: flex; flex-direction: column; gap: .4rem; color: var(--ink); font-size: .75rem; font-weight: 700; }
.campo input, .campo select, .campo textarea { margin-top: 0; font-weight: 400; }
.campo--full { grid-column: 1 / -1; }
.input-prefix { display: flex; align-items: center; overflow: hidden; border: 1px solid var(--line-strong); border-radius: var(--radius-sm); background: #fafafa; }
.input-prefix span { padding-left: .75rem; color: var(--ink-soft); font-size: .8rem; }
.input-prefix input { border: 0; background: transparent; box-shadow: none; }
.contador-midia { color: var(--ink-soft); font-size: .75rem; font-weight: 700; }
.imagens__label, .imagens__ajuda { display: none; }
.imagens__grade { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.imagens__adicionar { border-color: var(--blue-600); background: var(--blue-50); color: var(--blue-600); font-size: .75rem; font-weight: 700; }
.variacoes-secao { margin-top: 1.15rem; border: 1px solid var(--line-strong); border-radius: var(--radius-md); padding: 1rem; background: #fff; }
.variacoes-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.btn-adicionar-var { display: inline-flex; align-items: center; gap: .3rem; color: var(--blue-600); border-color: var(--blue-600); background: var(--blue-50); font-weight: 700; }
.sem-variacoes { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: .9rem; padding: .75rem; border-radius: var(--radius-sm); background: #f8fafc; color: var(--ink-soft); font-size: .75rem; }
.sem-variacoes__texto { display: flex; flex-direction: column; gap: .2rem; }
.sem-variacoes__texto strong { color: var(--ink); }
.estoque-geral { min-width: 150px; color: var(--ink); font-weight: 700; }
.acoes { position: sticky; bottom: 0; justify-content: flex-end; padding-top: 1rem; background: #fff; }
.acoes .btn { flex: initial; min-width: 130px; justify-content: center; }

/* Keep one visual language after the legacy form rules below. */
.produto-form-dialog .form { gap: .65rem; }
.produto-form-dialog .formulario-secao { border-bottom: 0; padding: .75rem 0; }
.produto-form-dialog .secao-cabecalho { margin-bottom: .75rem; }
.produto-form-dialog .secao-cabecalho p,
.produto-form-dialog .variacoes-header p { max-width: 46ch; }
.produto-form-dialog .imagens__grade { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .5rem; }
.produto-form-dialog .variacoes-secao { margin-top: .25rem; padding: .9rem; background: #f8fafc; border-color: #e2e8f0; }
.produto-form-dialog .variacoes-header { align-items: center; }
.produto-form-dialog .btn-adicionar-var { border-radius: .6rem; padding: .45rem .7rem; }
.produto-form-dialog .campo input,
.produto-form-dialog .campo select,
.produto-form-dialog .campo textarea,
.produto-form-dialog .estoque-geral input,
.produto-form-dialog .variacao-linha input { border-radius: .6rem; border-color: #d7dee8; }
.produto-form-dialog .acoes { border-top: 1px solid var(--line); margin-top: .25rem; }

.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form label {
  display: flex;
  flex-direction: column;
  gap: .4rem;
  font-size: .85rem;
  color: var(--ink-soft);
}

textarea,
select,
input {
  font-family: inherit;
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: .6rem .75rem;
  font-size: .9rem;
  background: #fff;
}

.imagens__grade {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: .6rem;
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
}

.imagens__remover {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, .6);
  color: #fff;
  cursor: pointer;
}

.imagens__adicionar {
  aspect-ratio: 1;
  border: 1px dashed var(--line);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.variacoes-secao {
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 1rem;
}

.variacoes-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-adicionar-var {
  background: none;
  border: 1px solid var(--line);
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
  cursor: pointer;
  font-size: 0.8rem;
}

.variacoes__lista {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.8rem;
}

.variacao-linha {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 0.5rem;
  align-items: center;
}

.btn-remover-var {
  background: #ff4d4f;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 0.5rem;
  cursor: pointer;
}

.acoes {
  display: flex;
  gap: .75rem;
  margin-top: .5rem;
}

.acoes .btn {
  flex: 1;
}

.pagina__erro {
  color: var(--danger);
  font-size: .85rem;
}

@media (max-width: 640px) {
  .produto-form-dialog {
    padding: 1rem;
  }

  .formulario-cabecalho, .variacoes-header, .sem-variacoes { flex-direction: column; }
  .formulario-etapa { align-self: flex-start; }
  .campos-grid--dados, .campos-grid--envio { grid-template-columns: 1fr; }
  .imagens__grade { grid-template-columns: repeat(4, 1fr); }
  .estoque-geral { width: 100%; }
}
</style>