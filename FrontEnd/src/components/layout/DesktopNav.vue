<template>
  <nav class="pilula">
    <router-link v-for="item in itens" :key="item.rota" :to="{ name: item.rota }" class="pilula__item"
      :class="{ 'pilula__item--ativo': rotaAtiva === item.rota }">
      <!-- Ícone renderizado pelo Vuetify -->
      <v-icon class="pilula__icone" :icon="item.icone"></v-icon>

      <span class="pilula__label">{{ item.label }}</span>
      <span v-if="item.contador" class="pilula__contador">{{ item.contador }}</span>
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
.pilula {
  display: none;
  position: fixed;
  bottom: 1.5rem;
  left: 50%;
  transform: translateX(-50%);
  background: #fff;
  border-radius: 999px;
  border: 1px solid var(--line);
  box-shadow: var(--shadow-md);
  padding: .35rem;
  align-items: center;
  gap: .2rem;
  z-index: 40;
}

.pilula__item {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .25rem;
  padding: .55rem 1.1rem;
  border-radius: 999px;
  color: var(--ink-soft);
  transition: background .15s, color .15s;
  text-decoration: none;
  /* Previne sublinhado no link */
}

.pilula__item:hover {
  background: #fafafa;
  color: var(--ink);
}

.pilula__item--ativo {
  background: var(--blue-600);
  color: #fff;
  box-shadow: 0 4px 12px rgba(37, 99, 235, .3);
}

.pilula__item--ativo:hover {
  background: var(--blue-600);
  color: #fff;
}

.pilula__icone {
  font-size: 1rem;
  line-height: 1;
}

.pilula__label {
  font-size: .68rem;
  font-weight: 700;
  white-space: nowrap;
}

.pilula__contador {
  position: absolute;
  top: -2px;
  right: -2px;
  background: var(--blue-600);
  color: #fff;
  font-size: .55rem;
  font-weight: 700;
  min-width: 14px;
  height: 14px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 .2rem;
}

.pilula__item--ativo .pilula__contador {
  background: #fff;
  color: var(--blue-600);
}

@media (min-width: 768px) {
  .pilula {
    display: flex;
  }
}
</style>