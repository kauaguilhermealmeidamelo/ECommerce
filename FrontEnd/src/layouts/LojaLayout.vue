<template>
  <div class="layout">
    <CabecalhoLoja ref="cabecalhoRef" />

    <main class="layout__conteudo">
      <router-view />
    </main>

    <RodapeLoja />

    <NavInferior :itens="itensNav" />
    <NavPilula :itens="itensNav" />
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useClienteAuthStore } from "@/stores/clienteAuth";
import CabecalhoLoja from "@/components/layout/CabecalhoLoja.vue";
import RodapeLoja from "@/components/layout/RodapeLoja.vue";
import NavInferior from "@/components/layout/NavInferior.vue";
import NavPilula from "@/components/layout/NavPilula.vue";

const auth = useClienteAuthStore();
const cabecalhoRef = ref(null);

// 4 itens fixos — mesma ideia do painel admin (poucos itens, sempre
// visíveis). "Conta" muda de rota dependendo se o cliente já está logado.
const itensNav = computed(() => [
  { rota: "home", label: "Início", icone: "🏠" },
  { rota: "catalogo", label: "Catálogo", icone: "🛍️" },
  { rota: "carrinho", label: "Carrinho", icone: "🛒" },
  {
    rota: auth.autenticado ? "meus-pedidos" : "login-cliente",
    label: "Conta",
    icone: "👤",
  },
]);

// Exposto pra quem adiciona item ao carrinho poder atualizar o badge do
// cabeçalho sem precisar de um store de carrinho global (ex: chamar
// `layoutRef.value.atualizarCarrinho()` após POST /carrinho/itens).
function atualizarCarrinho() {
  cabecalhoRef.value?.carregarQuantidadeCarrinho();
}

defineExpose({ atualizarCarrinho });
</script>

<style scoped>
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--cor-fundo);
}
.layout__conteudo {
  flex: 1;
}
</style>
