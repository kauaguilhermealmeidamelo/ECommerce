<template>
  <nav class="navbar">
    <router-link
      v-for="item in itens"
      :key="item.rota"
      :to="{ name: item.rota }"
      class="navbar__item"
      :class="{ 'navbar__item--ativo': rotaAtiva === item.rota }"
    >
      <span class="navbar__icone" v-html="item.icone"></span>
      <span>{{ item.label }}</span>
    </router-link>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const rotaAtiva = computed(() => route.name)

const itens = [
  { rota: 'dashboard', label: 'Início', icone: '🏠' },
  { rota: 'envios', label: 'Envios', icone: '📮' },
  { rota: 'produtos', label: 'Produtos', icone: '🏷️' },
  { rota: 'clientes', label: 'Clientes', icone: '👥' },
  { rota: 'entregas', label: 'Config.', icone: '⚙️' },
]
</script>

<style scoped>
.navbar {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 480px;
  background: #ffffff;
  border-top: 1px solid var(--line);
  display: flex;
  justify-content: space-around;
  padding: .7rem 0 .9rem;
  box-shadow: 0 -2px 10px rgba(20, 32, 77, 0.04);
}
.navbar__item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .25rem;
  color: var(--ink-soft);
  font-size: .68rem;
}
.navbar__icone { font-size: 1.1rem; }
.navbar__item--ativo { color: var(--navy); font-weight: 600; }
</style>
