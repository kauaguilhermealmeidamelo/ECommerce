<template>
  <article class="produto-card">
    <img :src="produto.imagem_url" :alt="produto.nome" class="produto-card__imagem" />
    <div class="produto-card__corpo">
      <h3 class="produto-card__nome">{{ produto.nome }}</h3>
      <span class="produto-card__categoria">{{ produto.categoria?.nome }}</span>
      <strong class="produto-card__preco">{{ formatarMoeda(produto.preco) }}</strong>
      <button class="produto-card__botao" @click="$emit('adicionar', produto)">
        Adicionar ao carrinho
      </button>
    </div>
  </article>
</template>

<script setup lang="ts">
interface Produto {
  id: number | string
  nome: string
  preco: number
  imagem_url?: string
  categoria?: {
    nome: string
  }
  [key: string]: any
}

defineProps < {
  produto: Produto
} > ()

defineEmits < {
  (e: 'adicionar', produto: Produto): void
}> ()

const formatarMoeda = (v: number): string =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
</script>

<style scoped>
/* Toda cor/fonte vem do tema — nunca hardcode aqui. */
.produto-card {
  background: var(--cor-superficie);
  border: 1px solid var(--cor-linha);
  border-radius: var(--raio-borda);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.produto-card__imagem {
  width: 100%;
  aspect-ratio: 3/4;
  object-fit: cover;
}

.produto-card__corpo {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: .3rem;
}

.produto-card__nome {
  font-family: var(--fonte-display);
  margin: 0;
  font-size: 1.05rem;
  color: var(--cor-texto);
}

.produto-card__categoria {
  color: var(--cor-texto-suave);
  font-size: .8rem;
}

.produto-card__preco {
  font-size: 1.1rem;
  margin: .3rem 0;
  color: var(--cor-texto);
}

.produto-card__botao {
  background: var(--cor-primaria);
  color: #fff;
  border: none;
  border-radius: var(--raio-borda);
  padding: .65rem;
  font-weight: 600;
  cursor: pointer;
}

.produto-card__botao:hover {
  background: var(--cor-primaria-hover);
}
</style>