<template>
  <div class="carrossel">
    <div class="carrossel__viewport" ref="viewport" @scroll="atualizarIndice">
      <img v-for="(imagem, i) in imagensExibidas" :key="imagem.id ?? i" :src="imagem.url" :alt="alt"
        class="carrossel__imagem" />
    </div>

    <div v-if="imagensExibidas.length > 1" class="carrossel__pontos">
      <button v-for="(imagem, i) in imagensExibidas" :key="'ponto-' + (imagem.id ?? i)" class="carrossel__ponto"
        :class="{ 'carrossel__ponto--ativo': i === indiceAtual }" @click="irPara(i)"
        :aria-label="`Ver imagem ${i + 1}`"></button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Imagem {
  id?: number | string
  url: string
}

const props = defineProps<{
  imagens?: Imagem[]
  imagemUrlFallback?: string | null
  alt?: string
}>()

const imagensExibidas = computed<Imagem[]>(() => {
  if (props.imagens?.length) return props.imagens
  if (props.imagemUrlFallback) return [{ url: props.imagemUrlFallback }]
  return []
})

const viewport = ref<HTMLDivElement | null>(null)
const indiceAtual = ref(0)

function irPara(indice: number) {
  const largura = viewport.value?.clientWidth ?? 0
  viewport.value?.scrollTo({ left: largura * indice, behavior: 'smooth' })
}

function atualizarIndice() {
  const largura = viewport.value?.clientWidth ?? 1
  if (!viewport.value) return
  indiceAtual.value = Math.round(viewport.value.scrollLeft / largura)
}
</script>

<style scoped>
.carrossel {
  position: relative;
}

.carrossel__viewport {
  display: flex;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
}

.carrossel__viewport::-webkit-scrollbar {
  display: none;
}

.carrossel__imagem {
  flex: 0 0 100%;
  width: 100%;
  aspect-ratio: 4/5;
  object-fit: cover;
  scroll-snap-align: start;
}

.carrossel__pontos {
  position: absolute;
  bottom: .75rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: .4rem;
}

.carrossel__ponto {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, .6);
  padding: 0;
  cursor: pointer;
}

.carrossel__ponto--ativo {
  background: #fff;
  width: 9px;
  height: 9px;
}
</style>