<template>
  <div v-if="carregando" class="estado">Carregando produto...</div>
  <p v-else-if="erro" class="estado estado--erro">{{ erro }}</p>
  <ProductDetail
    v-else-if="produto"
    :produto="produto"
    @adicionar-carrinho="onAdicionado"
  />
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import apiLoja from "@/services/apiLoja";
import ProductDetail from "@/components/ProductDetail.vue";

const props = defineProps({ id: { type: [String, Number], required: true } });
const route = useRoute();

const produto = ref(null);
const carregando = ref(true);
const erro = ref(null);

async function carregar() {
  carregando.value = true;
  try {
    const { data } = await apiLoja.get(
      `/produtos/${props.id ?? route.params.id}`,
    );
    produto.value = data.data;
  } catch (e) {
    erro.value = "Produto não encontrado.";
  } finally {
    carregando.value = false;
  }
}

function onAdicionado() {
  // ProductDetail.vue já chama POST /carrinho/itens sozinho — aqui só
  // reagimos ao evento se precisar atualizar algo na página (ex: toast).
}

onMounted(carregar);
</script>

<style scoped>
.estado {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--cor-texto-suave);
  font-size: 0.9rem;
}
.estado--erro {
  color: #dc2626;
}
</style>
