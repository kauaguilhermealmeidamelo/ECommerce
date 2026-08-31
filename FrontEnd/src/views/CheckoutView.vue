<template>
  <div class="placeholder">
    <h1 class="font-display">Checkout</h1>
    <p>
      Essa tela ainda não foi construída — o backend já está pronto
      (<code>POST /checkout/frete</code> e <code>POST /checkout/finalizar</code>,
      com re-validação de frete no servidor), falta só a UI: escolha de
      entrega (retirada/local/transportadora), formulário de endereço e
      redirecionamento pro <code>checkout_url</code> do Mercado Pago.
    </p>
    <router-link :to="{ name: 'carrinho' }" class="btn">← Voltar ao carrinho</router-link>
  </div>
</template>

<style scoped>
.placeholder { max-width: 560px; margin: 3rem auto; padding: 0 1.25rem; text-align: center; color: var(--cor-texto); }
.placeholder p { color: var(--cor-texto-suave); font-size: .9rem; line-height: 1.6; }
.placeholder code { background: var(--cor-fundo); padding: .1rem .35rem; border-radius: 6px; font-size: .82rem; }
.btn { display: inline-block; margin-top: 1rem; color: var(--cor-primaria); font-weight: 600; text-decoration: none; }
</style>
