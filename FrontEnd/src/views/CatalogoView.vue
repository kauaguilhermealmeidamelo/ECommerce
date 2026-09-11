<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Catálogo</h1>

    <!-- Busca por texto -->
    <div v-if="busca" class="pagina__busca-ativa">
      Resultados para "<strong>{{ busca }}</strong>"
      <button @click="limparBusca">Limpar</button>
    </div>

    <!-- Chips de categoria -->
    <div v-else class="chips">
      <button class="chip" :class="{ 'chip--ativo': !categoriaSelecionada }" @click="categoriaSelecionada = null">
        Todos
      </button>
      <button v-for="cat in categoriasFolha" :key="cat.id" class="chip"
        :class="{ 'chip--ativo': categoriaSelecionada === cat.id }" @click="categoriaSelecionada = Number(cat.id)">
        {{ cat.nome }}
      </button>
    </div>

    <div v-if="carregandoCategorias" class="estado">Carregando catálogo...</div>

    <!-- Busca por texto: grid único -->
    <section v-else-if="busca">
      <p class="secao__contador">{{ produtosBusca.length }} produto(s) encontrado(s)</p>
      <div v-if="produtosBusca.length === 0" class="estado-vazio">Nenhum produto encontrado pra "{{ busca }}".</div>
      <div v-else class="grade-produtos">
        <ProductCard v-for="produto in produtosBusca" :key="produto.id" :produto="produto"
          @adicionar="adicionarAoCarrinho" />
      </div>
    </section>

    <!-- Uma categoria selecionada: grid único daquela categoria -->
    <section v-else-if="categoriaSelecionada">
      <p class="secao__contador">{{ produtosCategoriaUnica.length }} produto(s)</p>
      <div v-if="produtosCategoriaUnica.length === 0" class="estado-vazio">Nenhum produto nessa categoria ainda.</div>
      <div v-else class="grade-produtos">
        <ProductCard v-for="produto in produtosCategoriaUnica" :key="produto.id" :produto="produto"
          @adicionar="adicionarAoCarrinho" />
      </div>
    </section>

    <!-- Nenhum filtro: uma seção por categoria -->
    <template v-else>
      <section v-for="secao in secoesPorCategoria" :key="secao.categoria.id" class="secao-categoria">
        <div class="secao-categoria__cabecalho">
          <h2>{{ secao.categoria.nome }}</h2>
          <button v-if="secao.total > secao.produtos.length" class="secao-categoria__ver-tudo"
            @click="categoriaSelecionada = Number(secao.categoria.id)">
            Ver tudo →
          </button>
        </div>

        <div v-if="secao.carregando" class="estado">Carregando...</div>
        <div v-else-if="secao.produtos.length === 0" class="estado-vazio estado-vazio--compacto">Nenhum produto nessa
          categoria ainda.</div>
        <div v-else class="grade-produtos">
          <ProductCard v-for="produto in secao.produtos" :key="produto.id" :produto="produto"
            @adicionar="adicionarAoCarrinho" />
        </div>
      </section>

      <p v-if="categoriasFolha.length === 0" class="estado-vazio">Nenhuma categoria cadastrada ainda.</p>
    </template>
  </div>
</template>

<script setup lang="ts">
import { useCatalogoViewModel } from '@/viewmodels/useCatalogoViewModel'
import ProductCard from '@/components/produto/ProductCard.vue'

const {
  categoriasFolha,
  carregandoCategorias,
  categoriaSelecionada,
  busca,
  secoesPorCategoria,
  produtosCategoriaUnica,
  produtosBusca,
  limparBusca,
  adicionarAoCarrinho,
} = useCatalogoViewModel()
</script>

<style scoped>
.pagina {
  max-width: 1100px;
  margin: 0 auto;
  padding: 1.25rem 1.25rem 6rem;
}

.pagina__titulo {
  font-size: 1.4rem;
  margin: 0 0 1rem;
  color: var(--cor-texto);
}

.pagina__busca-ativa {
  display: flex;
  align-items: center;
  gap: .75rem;
  font-size: .88rem;
  color: var(--cor-texto-suave);
  margin-bottom: 1.25rem;
}

.pagina__busca-ativa strong {
  color: var(--cor-texto);
}

.pagina__busca-ativa button {
  border: none;
  background: none;
  color: var(--cor-primaria);
  font-weight: 600;
  cursor: pointer;
  font-size: .82rem;
}

.chips {
  display: flex;
  gap: .5rem;
  overflow-x: auto;
  padding-bottom: .5rem;
  margin-bottom: 1.5rem;
}

.chip {
  flex-shrink: 0;
  border: 1px solid var(--cor-linha);
  background: var(--cor-superficie);
  color: var(--cor-texto-suave);
  font-size: .82rem;
  font-weight: 600;
  padding: .55rem 1rem;
  border-radius: 999px;
  cursor: pointer;
  white-space: nowrap;
  transition: background .15s, color .15s, border-color .15s;
}

.chip:hover {
  border-color: var(--cor-primaria);
  color: var(--cor-primaria);
}

.chip--ativo {
  background: var(--cor-primaria);
  border-color: var(--cor-primaria);
  color: #fff;
}

.estado {
  text-align: center;
  padding: 2.5rem 1rem;
  color: var(--cor-texto-suave);
  font-size: .88rem;
}

.estado-vazio {
  text-align: center;
  padding: 2.5rem 1rem;
  color: var(--cor-texto-suave);
  font-size: .85rem;
}

.estado-vazio--compacto {
  padding: 1.5rem 1rem;
}

.secao__contador {
  font-size: .8rem;
  color: var(--cor-texto-suave);
  margin: 0 0 1rem;
}

.secao-categoria {
  margin-bottom: 2.5rem;
}

.secao-categoria__cabecalho {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.secao-categoria__cabecalho h2 {
  font-family: var(--fonte-display);
  font-size: 1.1rem;
  color: var(--cor-texto);
  margin: 0;
}

.secao-categoria__ver-tudo {
  border: none;
  background: none;
  color: var(--cor-primaria);
  font-weight: 600;
  font-size: .8rem;
  cursor: pointer;
}

.grade-produtos {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: .9rem;
}

@media (min-width: 640px) {
  .grade-produtos {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 900px) {
  .grade-produtos {
    grid-template-columns: repeat(4, 1fr);
  }
}
</style>