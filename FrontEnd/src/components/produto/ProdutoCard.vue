<script setup lang="ts">
import type { Produto } from '@/types/produto.types'
import BaseCard from '@/components/common/BaseCard.vue'

defineProps<{ produto: Produto }>()
</script>

<template>
  <BaseCard>
    <div class="produto-card">
      <strong>{{ produto.nome }}</strong>
      <span>R$ {{ produto.preco.toFixed(2) }}</span>
      <small>Estoque: {{ produto.estoque }}</small>
    </div>
  </BaseCard>
</template>