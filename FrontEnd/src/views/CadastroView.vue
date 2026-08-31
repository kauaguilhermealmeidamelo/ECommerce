<template>
  <div class="autenticacao">
    <h1 class="font-display autenticacao__titulo">Criar conta</h1>
    <p class="autenticacao__subtitulo">Cadastre-se para acompanhar seus pedidos e agilizar suas próximas compras.</p>

    <form class="autenticacao__form" @submit.prevent="cadastrar">
      <label>
        Nome completo
        <input v-model="form.name" type="text" required autocomplete="name" />
      </label>

      <label>
        E-mail
        <input v-model="form.email" type="email" required autocomplete="email" />
      </label>

      <label>
        Telefone (opcional)
        <input v-model="form.telefone" type="tel" placeholder="(00) 00000-0000" autocomplete="tel" />
      </label>

      <label>
        Senha
        <input v-model="form.senha" type="password" required minlength="6" autocomplete="new-password" />
      </label>

      <label>
        Confirmar senha
        <input v-model="form.senha_confirmation" type="password" required minlength="6" autocomplete="new-password" />
      </label>

      <p v-if="erro" class="autenticacao__erro">{{ erro }}</p>

      <button class="btn btn--primario btn--bloco" type="submit" :disabled="carregando">
        {{ carregando ? 'Criando conta...' : 'Criar conta' }}
      </button>
    </form>

    <p class="autenticacao__rodape">
      Já tem conta? <router-link :to="{ name: 'login-cliente' }">Entrar</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useClienteAuthStore } from '@/stores/clienteAuth'

const router = useRouter()
const auth = useClienteAuthStore()

const form = ref({ name: '', email: '', telefone: '', senha: '', senha_confirmation: '' })
const erro = ref(null)
const carregando = ref(false)

async function cadastrar() {
  erro.value = null

  if (form.value.senha !== form.value.senha_confirmation) {
    erro.value = 'As senhas não coincidem.'
    return
  }

  carregando.value = true
  try {
    await auth.registrar(form.value)
    router.push({ name: 'home' })
  } catch (e) {
    const erros = e.response?.data?.errors
    erro.value = erros ? Object.values(erros)[0][0] : 'Não foi possível criar sua conta. Confira os dados.'
  } finally {
    carregando.value = false
  }
}
</script>

<style scoped>
.autenticacao { max-width: 420px; margin: 3rem auto; padding: 0 1.5rem 4rem; }
.autenticacao__titulo { font-size: 1.7rem; margin: 0 0 .3rem; color: var(--cor-texto); }
.autenticacao__subtitulo { color: var(--cor-texto-suave); margin: 0 0 1.5rem; font-size: .9rem; }
.autenticacao__form { display: flex; flex-direction: column; gap: 1rem; }
.autenticacao__form label { display: flex; flex-direction: column; gap: .4rem; font-size: .85rem; color: var(--cor-texto-suave); }
.autenticacao__form input {
  border: 1px solid var(--cor-linha); border-radius: var(--raio-borda);
  padding: .7rem .85rem; font-size: .9rem; background: var(--cor-superficie); color: var(--cor-texto);
}
.autenticacao__form input:focus { outline: none; border-color: var(--cor-primaria); }
.autenticacao__erro { color: #dc2626; font-size: .85rem; margin: 0; }
.autenticacao__rodape { text-align: center; margin-top: 1.5rem; font-size: .85rem; color: var(--cor-texto-suave); }
.autenticacao__rodape a { color: var(--cor-primaria); font-weight: 600; text-decoration: none; }

.btn { border: none; border-radius: var(--raio-borda); padding: .8rem 1rem; font-weight: 700; font-size: .92rem; cursor: pointer; }
.btn--primario { background: var(--cor-primaria); color: #fff; }
.btn--primario:hover { background: var(--cor-primaria-hover); }
.btn--primario:disabled { opacity: .6; cursor: not-allowed; }
.btn--bloco { width: 100%; }
</style>
