<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="aberto" class="modal__fundo" @click.self="$emit('fechar')">
        <div class="modal__caixa" :class="`modal__caixa--${tamanho}`">
          <div class="modal__cabecalho">
            <h3>{{ titulo }}</h3>
            <button class="modal__fechar" @click="$emit('fechar')" aria-label="Fechar">✕</button>
          </div>
          <div class="modal__corpo">
            <slot />
          </div>
          <div v-if="$slots.rodape" class="modal__rodape">
            <slot name="rodape" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  aberto: { type: Boolean, default: false },
  titulo: { type: String, default: '' },
  tamanho: { type: String, default: 'sm' }, // sm | md
})
defineEmits(['fechar'])
</script>

<style scoped>
.modal__fundo {
  position: fixed; inset: 0; background: rgba(17, 24, 39, .45);
  display: flex; align-items: flex-end; justify-content: center;
  z-index: 1000; padding: 0;
}
.modal__caixa {
  background: #fff; width: 100%; max-height: 88vh; overflow-y: auto;
  border-radius: 20px 20px 0 0;
  box-shadow: var(--shadow-md);
  display: flex; flex-direction: column;
}
.modal__caixa--md { max-width: 560px; }
.modal__caixa--sm { max-width: 440px; }

@media (min-width: 640px) {
  .modal__fundo { align-items: center; padding: 1rem; }
  .modal__caixa { border-radius: 20px; max-height: 85vh; }
}

.modal__cabecalho {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--line);
  flex-shrink: 0;
}
.modal__cabecalho h3 { font-size: .98rem; font-weight: 700; color: var(--ink); }
.modal__fechar { background: none; border: none; color: var(--ink-faint); font-size: .95rem; padding: .3rem; line-height: 1; }
.modal__fechar:hover { color: var(--ink); }
.modal__corpo { padding: 1.25rem; flex: 1; }
.modal__rodape {
  padding: 1rem 1.25rem; border-top: 1px solid var(--line);
  display: flex; justify-content: flex-end; gap: .6rem; flex-shrink: 0;
}

.modal-enter-active, .modal-leave-active { transition: opacity .15s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>