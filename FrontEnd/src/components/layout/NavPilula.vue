<template>
  <nav class="pilula">
    <router-link
      v-for="item in itens"
      :key="item.rota"
      :to="{ name: item.rota }"
      class="pilula__item"
      :class="{ 'pilula__item--ativo': rotaAtiva === item.rota }"
    >
      <span class="pilula__icone">{{ item.icone }}</span>
      <span class="pilula__label">{{ item.label }}</span>
      <span v-if="item.contador" class="pilula__contador">{{
        item.contador
      }}</span>
    </router-link>
  </nav>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

defineProps({
  itens: { type: Array, required: true },
});

const route = useRoute();
const rotaAtiva = computed(() => route.name);
</script>

<style scoped>
/* Idêntico em estrutura ao DesktopNav.vue do painel admin. */
.pilula {
  display: none;
  position: fixed;
  bottom: 1.5rem;
  left: 50%;
  transform: translateX(-50%);
  background: var(--cor-superficie);
  border-radius: 999px;
  border: 1px solid var(--cor-linha);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  padding: 0.35rem;
  align-items: center;
  gap: 0.2rem;
  z-index: 40;
}
.pilula__item {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.55rem 1.1rem;
  border-radius: 999px;
  color: var(--cor-texto-suave);
  text-decoration: none;
  transition:
    background 0.15s,
    color 0.15s;
}
.pilula__item:hover {
  background: var(--cor-fundo);
  color: var(--cor-texto);
}
.pilula__item--ativo {
  background: var(--cor-primaria);
  color: #fff;
}
.pilula__item--ativo:hover {
  background: var(--cor-primaria);
  color: #fff;
}
.pilula__icone {
  font-size: 1rem;
  line-height: 1;
}
.pilula__label {
  font-size: 0.68rem;
  font-weight: 700;
  white-space: nowrap;
}
.pilula__contador {
  position: absolute;
  top: -2px;
  right: -2px;
  background: var(--cor-primaria);
  color: #fff;
  font-size: 0.55rem;
  font-weight: 700;
  min-width: 14px;
  height: 14px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.2rem;
}
.pilula__item--ativo .pilula__contador {
  background: #fff;
  color: var(--cor-primaria);
}

@media (min-width: 768px) {
  .pilula {
    display: flex;
  }
}
</style>
