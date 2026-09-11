<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Categorias</h1>
    <p class="pagina__subtitulo">Organize categorias, subcategorias e sub-subcategorias da sua loja.</p>

    <section class="painel">
      <h2 class="painel__titulo">
        {{ categoriaPaiSelecionada ? `Nova subcategoria em "${categoriaPaiSelecionada.nome}"` :
          'Nova categoria principal' }}
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
        <CategoriaArvoreItem v-for="cat in arvore" :key="cat.id" :categoria="cat"
          @adicionar-filha="prepararSubcategoria" @atualizar="carregar" />
      </ul>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useCategoriasViewModel } from '@/viewmodels/useCategoriasViewModel'
// @ts-expect-error Vue SFC declaration check
import CategoriaArvoreItem from '@/components/common/CategoriaArvoreItem.vue'

const {
  arvore,
  categoriasFlat,
  carregando,
  salvando,
  erro,
  categoriaPaiSelecionada,
  form,
  gerarSlug,
  profundidade,
  prepararSubcategoria,
  limparPai,
  carregar,
  criar,
} = useCategoriasViewModel()
</script>

<style scoped>
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

select {
  background: #fff;
}

.acoes {
  display: flex;
  gap: .75rem;
  margin-top: .25rem;
}

.acoes .btn {
  flex: 1;
}

.painel__titulo {
  font-size: .82rem;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--ink-soft);
  margin: 0 0 1rem;
  font-weight: 700;
}

.lista-arvore {
  list-style: none;
  padding: 0;
  margin: 0;
}

.vazio {
  color: var(--ink-soft);
  text-align: center;
  padding: 1.5rem 0;
}

.pagina__erro {
  color: var(--danger);
  font-size: .85rem;
  margin: 0;
}
</style>