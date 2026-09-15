<template>
  <div v-if="lojaCarregada" class="layout">
    <CabecalhoLoja />

    <main class="layout__conteudo">
      <router-view />
    </main>

    <RodapeLoja />

    <NavInferior :itens="itensNav" />
    <NavPilula :itens="itensNav" />

    <Toast :mensagem="carrinho.toastMsg" :tipo="carrinho.toastTipo" @fechar="carrinho.limparAviso" />
  </div>
  <div v-else class="layout layout--carregando" aria-busy="true">
    <div class="layout__carregando"></div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useClienteAuthStore } from '@/stores/clienteAuth'
import { useCarrinhoStore } from '@/stores/carrinho.store'
import { carregarTemaDaLoja } from '@/theme/tema'
import CabecalhoLoja from '@/components/CabecalhoLoja.vue'
import RodapeLoja from '@/components/RodapeLoja.vue'
import NavInferior from '@/components/NavInferior.vue'
import NavPilula from '@/components/NavPilula.vue'
import Toast from '@/components/common/Toast.vue'

const auth = useClienteAuthStore()
const carrinho = useCarrinhoStore()
const lojaCarregada = ref(false)

const itensNav = computed(() => [
  { rota: 'home', label: 'Início', icone: 'mdi-home-outline' },
  { rota: 'catalogo', label: 'Catálogo', icone: 'mdi-shopping-outline' },
  { rota: 'carrinho', label: 'Carrinho', icone: 'mdi-cart-outline' },
  { rota: auth.autenticado ? 'meus-pedidos' : 'login-cliente', label: 'Conta', icone: 'mdi-account-outline' },
])

onMounted(async () => {
  try {
    await carregarTemaDaLoja()
  } catch {
    // A loja ainda monta com valores neutros se a API estiver indisponível.
  } finally {
    lojaCarregada.value = true
  }
})
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

.layout--carregando {
  min-height: 100vh;
  background: var(--cor-fundo);
}

.layout__carregando {
  min-height: 100vh;
}
</style>  