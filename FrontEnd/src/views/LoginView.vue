<template>
  <div class="login">
    <h1 class="font-display login__titulo">Entrar</h1>
    <p class="login__subtitulo">Acesse o painel de gerenciamento da sua loja.</p>

    <form @submit.prevent="entrar" class="login__form">
      <label>
        E-mail
        <input v-model="email" type="email" required autocomplete="username" />
      </label>

      <label>
        Senha
        <input v-model="senha" type="password" required autocomplete="current-password" />
      </label>

      <p v-if="erro" class="login__erro">{{ erro }}</p>

      <button class="btn btn--primario btn--bloco" type="submit" :disabled="carregando">
        {{ carregando ? 'Entrando...' : 'Entrar' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const senha = ref('')
const erro = ref(null)
const carregando = ref(false)

const router = useRouter()
const auth = useAuthStore()

async function entrar() {
  carregando.value = true
  erro.value = null

  try {
    await auth.login(email.value, senha.value)
    router.push({ name: 'dashboard' })
  } catch (e) {
    erro.value = 'E-mail ou senha inválidos.'
  } finally {
    carregando.value = false
  }
}
</script>

<style scoped>
.login {
  max-width: 380px;
  margin: 4rem auto;
  padding: 0 1.5rem;
}
.login__titulo { font-size: 1.8rem; margin: 0 0 .3rem; font-weight: 800; color: var(--ink); }
.login__subtitulo { color: var(--ink-soft); margin: 0 0 1.5rem; font-size: .92rem; }
.login__form { display: flex; flex-direction: column; gap: 1rem; }
.login__form label { display: flex; flex-direction: column; gap: .4rem; font-size: .85rem; color: var(--ink-soft); }
.login__erro { color: var(--danger); font-size: .85rem; margin: 0; }
</style>