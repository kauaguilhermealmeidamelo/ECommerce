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
import { computed, ref, onMounted } from "vue";
import { useClienteAuthStore } from "@/stores/clienteAuth";
import { useLojaStore } from "@/stores/lojaStore";
import CabecalhoLoja from "@/components/layout/CabecalhoLoja.vue";
import RodapeLoja from "@/components/layout/RodapeLoja.vue";
import NavInferior from "@/components/layout/NavInferior.vue";
import NavPilula from "@/components/layout/NavPilula.vue";

const auth = useClienteAuthStore();
const lojaStore = useLojaStore();
const cabecalhoRef = ref(null);

// 4 itens fixos — mesma ideia do painel admin (poucos itens, sempre
// visíveis). "Conta" muda de rota dependendo se o cliente já está logado.
const itensNav = computed(() => [
  { rota: "home", label: "Início", icone: "mdi-home" },
  { rota: "catalogo", label: "Catálogo", icone: "mdi-storefront" },
  { rota: "carrinho", label: "Carrinho", icone: "mdi-cart" },
  {
    rota: auth.autenticado ? "meus-pedidos" : "login-cliente",
    label: "Conta",
    icone: "mdi-account",
  },
]);

// Exposto pra quem adiciona item ao carrinho poder atualizar o badge do
// cabeçalho sem precisar de um store de carrinho global (ex: chamar
// `layoutRef.value.atualizarCarrinho()` após POST /carrinho/itens).
function atualizarCarrinho() {
  cabecalhoRef.value?.carregarQuantidadeCarrinho();
}

onMounted(() => {
  // Dispara o carregamento dos dados da loja (nome, logo, etc.) assim que o layout abre
  lojaStore.carregarLoja();
});

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
