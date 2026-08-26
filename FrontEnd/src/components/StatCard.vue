<template>
  <div class="card">
    <div class="card__icone" v-html="icone"></div>
    <div>
      <strong class="card__valor">{{ valor }}</strong>
      <span class="card__label">{{ label }}</span>
      <span v-if="variacao !== null" class="card__variacao" :class="variacao >= 0 ? 'up' : 'down'">
        {{ variacao >= 0 ? '▲' : '▼' }} {{ Math.abs(variacao).toFixed(1) }}%
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icone: { type: String, default: '' },
  valor: { type: [String, Number], required: true },
  label: { type: String, required: true },
  variacao: { type: Number, default: null },
})
</script>

<style scoped>
.card {
  border: 1px solid var(--line);
  border-radius: 14px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: .8rem;
}
.card__icone {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: var(--icon-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.2rem;
}
.card__valor {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  font-size: 1.55rem;
  display: block;
  line-height: 1;
}
.card__label { color: var(--ink-soft); font-size: .85rem; margin-top: .3rem; display: block; }
.card__variacao { font-size: .78rem; font-weight: 600; margin-top: .25rem; display: inline-block; }
.up { color: var(--success); }
.down { color: var(--danger); }
</style>
