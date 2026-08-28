<template>
  <Teleport to="body">
    <Transition name="toast">
      <div v-if="mensagem" class="toast" :class="`toast--${tipo}`">
        <span class="toast__ponto"></span>
        <span>{{ mensagem }}</span>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { watch } from 'vue'

const props = defineProps({
  mensagem: { type: String, default: '' },
  tipo: { type: String, default: 'success' }, // success | info | error
})
const emit = defineEmits(['fechar'])

watch(() => props.mensagem, (novo) => {
  if (!novo) return
  setTimeout(() => emit('fechar'), 3000)
})
</script>

<style scoped>
.toast {
  position: fixed; bottom: 6.5rem; left: 50%; transform: translateX(-50%);
  background: var(--ink); color: #fff; padding: .75rem 1.1rem;
  border-radius: 999px; font-size: .82rem; font-weight: 600;
  display: flex; align-items: center; gap: .5rem;
  box-shadow: var(--shadow-md); z-index: 2000; white-space: nowrap;
}
.toast__ponto { width: 7px; height: 7px; border-radius: 50%; background: var(--success); flex-shrink: 0; }
.toast--info .toast__ponto { background: var(--blue-600); }
.toast--error .toast__ponto { background: var(--danger); }

@media (min-width: 768px) { .toast { bottom: 2rem; } }

.toast-enter-active, .toast-leave-active { transition: opacity .2s, transform .2s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translate(-50%, 10px); }
</style>