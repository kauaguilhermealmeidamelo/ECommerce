<script setup lang="ts">
interface Props {
  variant?: 'primario' | 'secundario' | 'perigo'
  disabled?: boolean
  type?: 'button' | 'submit'
}
withDefaults(defineProps<Props>(), { variant: 'primario', disabled: false, type: 'button' })
</script>

<template>
  <button :type="type" class="btn" :class="`btn--${variant}`" :disabled="disabled">
    <slot />
  </button>
</template>