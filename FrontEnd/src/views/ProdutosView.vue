<script setup lang="ts">
import { useProdutoListViewModel } from '@/viewmodels/useProdutoListViewModel'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import ProdutoCard from '@/components/produto/ProdutoCard.vue'

const {
  produtosFiltrados,
  produtosComEstoqueBaixo,
  filtroTexto,
  carregando,
  erro,
  recarregar
} = useProdutoListViewModel()
</script>

<template>
  <section class="pagina-produtos">
    <BaseInput v-model="filtroTexto" placeholder="Buscar produto..." />

    <p v-if="produtosComEstoqueBaixo.length" class="alerta-estoque">
      ⚠️ {{ produtosComEstoqueBaixo.length }} produto(s) com estoque baixo.
    </p>

    <div v-if="carregando">Carregando produtos...</div>
    <p v-else-if="erro">
      {{ erro }}
      <BaseButton variant="secundario" @click="recarregar">Tentar de novo</BaseButton>
    </p>

    <div v-else class="grid-produtos">
      <ProdutoCard v-for="produto in produtosFiltrados" :key="produto.id" :produto="produto" />
    </div>
  </section>
</template>