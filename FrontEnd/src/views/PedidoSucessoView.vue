<template>
  <div class="placeholder">
    <h1 class="font-display">Pedido confirmado 🎉</h1>
    <p>Essa tela ainda precisa ser construída — deve exibir o número do pedido (via query string, ex: <code>?pedido=123</code>) e um link pra "Meus pedidos".</p>
    <router-link :to="{ name: 'meus-pedidos' }" class="btn">Ver meus pedidos</router-link>
  </div>
</template>

<style scoped>
.placeholder { max-width: 560px; margin: 3rem auto; padding: 0 1.25rem; text-align: center; color: var(--cor-texto); }
.placeholder p { color: var(--cor-texto-suave); font-size: .9rem; line-height: 1.6; }
.placeholder code { background: var(--cor-fundo); padding: .1rem .35rem; border-radius: 6px; font-size: .82rem; }
.btn { display: inline-block; margin-top: 1rem; color: var(--cor-primaria); font-weight: 600; text-decoration: none; }
</style>
