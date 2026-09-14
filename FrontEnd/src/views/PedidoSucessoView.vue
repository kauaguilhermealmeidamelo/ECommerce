<template>
  <div class="placeholder">
    <h1 class="font-display"><v-icon icon="mdi-check-circle-outline" /> Pedido confirmado</h1>

    <p v-if="numeroPedido">
      O número do seu pedido é <strong>#{{ numeroPedido }}</strong>. Obrigado pela compra!
    </p>
    <p v-else>
      Seu pedido foi realizado com sucesso!
    </p>

    <router-link :to="{ name: 'meus-pedidos' }" class="btn">Ver meus pedidos</router-link>
  </div>
</template>

<script setup lang="ts">
import { usePedidoConfirmadoViewModel } from '@/viewmodels/usePedidoConfirmadoViewModel'

const { numeroPedido } = usePedidoConfirmadoViewModel()
</script>

<style scoped>
.placeholder {
  max-width: 560px;
  margin: 3rem auto;
  padding: 0 1.25rem;
  text-align: center;
  color: var(--cor-texto);
}

.placeholder p {
  color: var(--cor-texto-suave);
  font-size: .9rem;
  line-height: 1.6;
}

.placeholder strong {
  color: var(--cor-texto);
}

.btn {
  display: inline-block;
  margin-top: 1rem;
  color: var(--cor-primaria);
  font-weight: 600;
  text-decoration: none;
}
</style>