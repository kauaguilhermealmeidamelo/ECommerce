<template>
  <li class="no">
    <div class="no__linha" :style="{ paddingLeft: `${nivel * 1.1}rem` }">
      <span class="no__nome">{{ categoria.nome }}</span>
      <div class="no__acoes">
        <button class="no__botao" @click="$emit('adicionar-filha', categoria)">+ Subcategoria</button>
        <button class="no__botao no__botao--perigo" @click="remover">Remover</button>
      </div>
    </div>

    <ul v-if="categoria.filhas_recursivas?.length" class="no__filhas">
      <CategoriaArvoreItem v-for="filha in categoria.filhas_recursivas" :key="filha.id" :categoria="filha"
        :nivel="nivel + 1" @adicionar-filha="$emit('adicionar-filha', $event)" @atualizar="$emit('atualizar')" />
    </ul>
  </li>
</template>

<script setup>
import api from '@/services/api'

const props = defineProps({
  categoria: { type: Object, required: true },
  nivel: { type: Number, default: 0 },
})

const emit = defineEmits(['adicionar-filha', 'atualizar'])

async function remover() {
  if (!confirm(`Remover a categoria "${props.categoria.nome}"?`)) return

  try {
    await api.delete(`/admin/categorias/${props.categoria.id}`)
    emit('atualizar')
  } catch (e) {
    alert(e.response?.data?.message || 'Não foi possível remover essa categoria.')
  }
}
</script>

<style scoped>
.no {
  list-style: none;
}

.no__linha {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: .55rem 0;
  border-bottom: 1px solid var(--line);
  gap: .5rem;
}

.no__nome {
  font-weight: 600;
  font-size: .92rem;
}

.no__acoes {
  display: flex;
  gap: .4rem;
  flex-shrink: 0;
}

.no__botao {
  border: none;
  background: var(--icon-bg);
  color: var(--navy);
  font-size: .72rem;
  font-weight: 600;
  padding: .3rem .6rem;
  border-radius: 999px;
  cursor: pointer;
  white-space: nowrap;
}

.no__botao--perigo {
  background: #fbe9e7;
  color: var(--danger);
}

.no__filhas {
  padding: 0;
  margin: 0;
}
</style>