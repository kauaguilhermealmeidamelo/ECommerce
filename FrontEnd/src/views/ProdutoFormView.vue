<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">{{ modoEdicao ? 'Editar produto' : 'Novo produto' }}</h1>
    <p class="pagina__subtitulo">{{ modoEdicao ? 'Atualize os dados do produto.' : 'Cadastre um novo produto na sua loja.' }}</p>

    <div v-if="carregandoProduto">Carregando...</div>

    <form v-else class="form" @submit.prevent="salvar">
      <label>
        Nome
        <input v-model="produto.nome" required maxlength="255" placeholder="Ex: Furadeira 220V ou Camiseta Lisa" />
      </label>

      <label>
        Categoria
        <select v-model="produto.categoria_id" required>
          <option disabled value="">Selecione...</option>
          <option v-for="cat in categoriasFlat" :key="cat.id" :value="cat.id" :disabled="!cat.eh_folha">
            {{ '—'.repeat(cat.nivel) }} {{ cat.nome }}{{ !cat.eh_folha ? ' (selecione uma subcategoria)' : '' }}
          </option>
        </select>
      </label>

      <label class="preco">
        Preço (R$)
        <input v-model.number="produto.preco" type="number" step="0.01" min="0" required />
      </label>

      <!-- Imagens -->
      <div class="imagens">
        <span class="imagens__label">Fotos do produto</span>
        <p class="imagens__ajuda">A primeira foto é a capa. As demais formam o carrossel.</p>

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
      </div>

      <!-- Variações Dinâmicas (Agnóstico de Nicho) -->
      <div class="variacoes-secao">
        <div class="variacoes-header">
          <span class="variacoes__label">Variações e Estoque Dinâmico</span>
          <button type="button" class="btn-adicionar-var" @click="adicionarVariacao">+ Adicionar Variação</button>
        </div>
        <p class="variacoes__ajuda">
          Crie livremente as opções do seu produto (ex: tamanho, voltagem, peso, sabor) e defina o estoque individual.
        </p>

        <div v-if="variacoes.length === 0" class="sem-variacoes">
          Produto sem variações específicas. O estoque geral será considerado.
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

<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import { useProdutoFormViewModel } from '@/viewmodels/useProdutoFormViewModel'

const props = defineProps({ id: { type: [String, Number], default: null } })

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
} = useProdutoFormViewModel(props.id)

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
</style>