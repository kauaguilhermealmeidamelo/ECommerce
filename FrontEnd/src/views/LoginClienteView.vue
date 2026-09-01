<template>
  <div class="autenticacao">
    <h1 class="font-display autenticacao__titulo">Entrar</h1>
    <p class="autenticacao__subtitulo">
      Acesse sua conta para ver e rastrear seus pedidos.
    </p>

    <form class="autenticacao__form" @submit.prevent="entrar">
      <label>
        E-mail
        <input v-model="email" type="email" required autocomplete="email" />
      </label>

      <label>
        Senha
        <input
          v-model="senha"
          type="password"
          required
          autocomplete="current-password"
        />
      </label>

      <p v-if="erro" class="autenticacao__erro">{{ erro }}</p>

      <button
        class="btn btn--primario btn--bloco"
        type="submit"
        :disabled="carregando"
      >
        {{ carregando ? "Entrando..." : "Entrar" }}
      </button>
    </form>

    <div class="autenticacao__divisor"><span>ou</span></div>

    <a :href="urlGoogle" class="btn btn--google btn--bloco">
      <span>🔵</span> Entrar com Google
    </a>

    <p class="autenticacao__rodape">
      Ainda não tem conta?
      <router-link :to="{ name: 'cadastro' }">Criar conta</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useClienteAuthStore } from "@/stores/clienteAuth";

const router = useRouter();
const route = useRoute();
const auth = useClienteAuthStore();

const email = ref("");
const senha = ref("");
const erro = ref(null);
const carregando = ref(false);

// O backend faz o redirect final pro FRONTEND_URL configurado no .env dele
// com ?token=... — ajuste a rota /auth/callback abaixo se preferir outro nome.
const urlGoogle = computed(() => {
  const base = (
    import.meta.env.VITE_API_URL || "http://localhost:8000/api"
  ).replace(/\/api\/?$/, "");
  return `${base}/auth/google`;
});

async function entrar() {
  erro.value = null;
  carregando.value = true;
  try {
    await auth.login(email.value, senha.value);
    // Se o guard de rota mandou pra cá com ?redirecionar=..., volta pra
    // onde o cliente queria ir (ex: /meus-pedidos) em vez de sempre pra home.
    router.push(route.query.redirecionar || { name: "home" });
  } catch (e) {
    erro.value = "E-mail ou senha inválidos.";
  } finally {
    carregando.value = false;
  }
}
</script>

<style scoped>
.autenticacao {
  max-width: 420px;
  margin: 3rem auto;
  padding: 0 1.5rem 4rem;
}
.autenticacao__titulo {
  font-size: 1.7rem;
  margin: 0 0 0.3rem;
  color: var(--cor-texto);
}
.autenticacao__subtitulo {
  color: var(--cor-texto-suave);
  margin: 0 0 1.5rem;
  font-size: 0.9rem;
}
.autenticacao__form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.autenticacao__form label {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  font-size: 0.85rem;
  color: var(--cor-texto-suave);
}
.autenticacao__form input {
  border: 1px solid var(--cor-linha);
  border-radius: var(--raio-borda);
  padding: 0.7rem 0.85rem;
  font-size: 0.9rem;
  background: var(--cor-superficie);
  color: var(--cor-texto);
}
.autenticacao__form input:focus {
  outline: none;
  border-color: var(--cor-primaria);
}
.autenticacao__erro {
  color: #dc2626;
  font-size: 0.85rem;
  margin: 0;
}
.autenticacao__rodape {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.85rem;
  color: var(--cor-texto-suave);
}
.autenticacao__rodape a {
  color: var(--cor-primaria);
  font-weight: 600;
  text-decoration: none;
}

.autenticacao__divisor {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 1.25rem 0;
  color: var(--cor-texto-suave);
  font-size: 0.8rem;
}
.autenticacao__divisor::before,
.autenticacao__divisor::after {
  content: "";
  flex: 1;
  height: 1px;
  background: var(--cor-linha);
}

.btn {
  border: none;
  border-radius: var(--raio-borda);
  padding: 0.8rem 1rem;
  font-weight: 700;
  font-size: 0.92rem;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.btn--primario {
  background: var(--cor-primaria);
  color: #fff;
}
.btn--primario:hover {
  background: var(--cor-primaria-hover);
}
.btn--primario:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn--google {
  background: var(--cor-superficie);
  color: var(--cor-texto);
  border: 1px solid var(--cor-linha);
}
.btn--google:hover {
  background: var(--cor-fundo);
}
.btn--bloco {
  width: 100%;
}
</style>
