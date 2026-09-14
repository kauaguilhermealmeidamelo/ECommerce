<template>
  <span class="badge badge--dot" :class="classeBadge">{{ rotulo }}</span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ status: string }>()

const mapa: Record<string, { rotulo: string; classe: string }> = {
  pendente: { rotulo: 'Pendente', classe: 'badge--warning' },
  em_analise: { rotulo: 'Em Análise', classe: 'badge--warning' },
  pago: { rotulo: 'Pago', classe: 'badge--success' },
  recusado: { rotulo: 'Recusado', classe: 'badge--danger' },
  estornado: { rotulo: 'Estornado', classe: 'badge--danger' },
  enviado: { rotulo: 'Enviado', classe: 'badge--azul' },
  concluido: { rotulo: 'Concluído', classe: 'badge--success' },
  cancelado: { rotulo: 'Cancelado', classe: 'badge--danger' },
}

const info = computed(() => mapa[props.status] ?? { rotulo: props.status, classe: 'badge--neutral' })
const rotulo = computed(() => info.value.rotulo)
const classeBadge = computed(() => info.value.classe)
</script>