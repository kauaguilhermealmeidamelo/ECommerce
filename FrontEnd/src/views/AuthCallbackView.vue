<template>
  <div class="callback-container">
    <p>Autenticando com o Google, aguarde um instante...</p>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useClienteAuthStore } from "@/stores/clienteAuth";

const route = useRoute();
const router = useRouter();
const authCliente = useClienteAuthStore();

onMounted(() => {
  const token = route.query.token;

  if (token) {
    // Salva na store e no localStorage automaticamente
    authCliente.definirToken(token);

    // Redireciona para a home da loja já autenticado
    router.push({ name: "home" });
  } else {
    router.push({ name: "login-cliente" });
  }
});
</script>

<style scoped>
.callback-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 60vh;
  font-family: var(--font-principal, sans-serif);
  color: var(--cor-texto, #333);
}
</style>
