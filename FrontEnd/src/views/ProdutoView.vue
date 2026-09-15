<template>
  <div v-if="carregando" class="estado">Carregando produto...</div>
  <p v-else-if="erro" class="estado estado--erro">{{ erro }}</p>
  <ProductDetail v-else-if="produto" :produto="produto" @adicionar-carrinho="onAdicionado" />
</template>

<script setup lang="ts">
import { useProductDetailViewModel } from '@/viewmodels/useProductDetailViewModel'
import ProductDetail from '@/components/produto/ProductDetail.vue'


const props = defineProps<{ id?: string | number }>()

const {
  produto,
  carregando,
  erro,
  onAdicionado
} = useProductDetailViewModel(props.id)
</script>

<style scoped>
.estado { text-align: center; padding: 3rem 1rem; color: var(--cor-texto-suave); font-size: .9rem; }
.estado--erro { color: #dc2626; }
</style>