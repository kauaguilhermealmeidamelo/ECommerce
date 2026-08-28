<template>
  <div class="stat card">
    <div class="stat__topo">
      <div class="icon-box" :class="`icon-box--${cor}`" v-html="icone"></div>
      <span v-if="variacao !== null" class="badge" :class="variacao >= 0 ? 'badge--success' : 'badge--danger'">
        {{ variacao >= 0 ? '▲' : '▼' }} {{ Math.abs(variacao).toFixed(1) }}%
      </span>
    </div>
    <strong class="stat__valor">{{ valor }}</strong>
    <span class="stat__label">{{ label }}</span>
    <span v-if="sub" class="stat__sub">{{ sub }}</span>
  </div>
</template>

<script setup>
defineProps({
  icone: { type: String, default: '' },
  cor: { type: String, default: 'azul' }, // azul | verde | roxo | laranja | vermelho | ambar
  valor: { type: [String, Number], required: true },
  label: { type: String, required: true },
  variacao: { type: Number, default: null },
  sub: { type: String, default: '' },
})
</script>

<style scoped>
.stat { padding: 1.1rem; display: flex; flex-direction: column; gap: .1rem; }
.stat__topo { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .7rem; }
.stat__valor { font-size: 1.4rem; font-weight: 800; color: var(--ink); line-height: 1.1; }
.stat__label { font-size: .78rem; font-weight: 600; color: var(--ink-soft); margin-top: .2rem; }
.stat__sub { font-size: .7rem; color: var(--ink-faint); margin-top: .15rem; }
</style>