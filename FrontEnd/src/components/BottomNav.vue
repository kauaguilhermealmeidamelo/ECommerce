<template>
  <nav class="navbar navbar--mobile">
    <router-link
      v-for="item in itens"
      :key="item.rota"
      :to="{ name: item.rota }"
      class="navbar__item"
      :class="{ 'navbar__item--ativo': rotaAtiva === item.rota }"
    >
      <span class="navbar__icone">{{ item.icone }}</span>
      <span class="navbar__label">{{ item.label }}</span>
      <span v-if="item.contador" class="navbar__contador">{{ item.contador }}</span>
    </router-link>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
  itens: { type: Array, required: true },
})

const route = useRoute()
const rotaAtiva = computed(() => route.name)
</script>

<style scoped>
.navbar--mobile {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #fff;
  border-top: 1px solid var(--line);
  display: flex;
  z-index: 40;
}
.navbar__item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .2rem;
  padding: .55rem 0 .8rem;
  position: relative;
  color: var(--ink-faint);
}
.navbar__item--ativo { color: var(--blue-600); }
.navbar__item--ativo::before {
  content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%);
  width: 22px; height: 2px; background: var(--blue-600); border-radius: 999px;
}
.navbar__icone { font-size: 1.1rem; line-height: 1; }
.navbar__label { font-size: .62rem; font-weight: 700; }
.navbar__contador {
  position: absolute; top: 4px; right: 22%;
  background: var(--blue-600); color: #fff; font-size: .55rem; font-weight: 700;
  min-width: 14px; height: 14px; border-radius: 999px;
  display: flex; align-items: center; justify-content: center; padding: 0 .2rem;
}

@media (min-width: 768px) {
  .navbar--mobile { display: none; }
}
</style>