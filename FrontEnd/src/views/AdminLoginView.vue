<template>
  <div class="autenticacao">
    <div class="autenticacao__cartao">
      <h1 class="font-display autenticacao__titulo">Painel Administrativo</h1>
      <p class="autenticacao__subtitulo">Acesse o painel para gerenciar a loja.</p>

      <form class="autenticacao__form" @submit.prevent="login">
        <label>
          E-mail
          <input 
            v-model="form.email" 
            type="email" 
            required 
            autocomplete="email" 
            placeholder="admin@sua-loja.com" 
          />
        </label>

        <label>
          Senha
          <input 
            v-model="form.senha" 
            type="password" 
            required 
            autocomplete="current-password" 
            placeholder="••••••••" 
          />
        </label>

        <p v-if="erro" class="autenticacao__erro">{{ erro }}</p>

        <button class="btn btn--primario btn--bloco" type="submit" :disabled="carregando">
          {{ carregando ? 'Autenticando...' : 'Acessar Painel' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAdminLoginViewModel } from '@/viewmodels/useAdminLoginViewModel'

const {
  form,
  erro,
  carregando,
  login
} = useAdminLoginViewModel()
</script>

<style scoped>
.autenticacao {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: var(--cor-fundo, #f3f4f6);
  padding: 1.5rem;
}

.autenticacao__cartao {
  width: 100%;
  max-width: 420px;
  background: var(--cor-superficie, #ffffff);
  padding: 2.5rem 2rem;
  border-radius: var(--raio-borda, 8px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.autenticacao__titulo {
  font-size: 1.6rem;
  margin: 0 0 .3rem;
  color: var(--cor-texto);
  text-align: center;
}

.autenticacao__subtitulo {
  color: var(--cor-texto-suave);
  margin: 0 0 2rem;
  font-size: .9rem;
  text-align: center;
}

.autenticacao__form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.autenticacao__form label {
  display: flex;
  flex-direction: column;
  gap: .4rem;
  font-size: .85rem;
  color: var(--cor-texto-suave);
  font-weight: 500;
}

.autenticacao__form input {
  border: 1px solid var(--cor-linha);
  border-radius: var(--raio-borda, 8px);
  padding: .75rem .85rem;
  font-size: .95rem;
  background: var(--cor-superficie);
  color: var(--cor-texto);
  transition: border-color 0.2s, box-shadow 0.2s;
}

.autenticacao__form input:focus {
  outline: none;
  border-color: var(--cor-primaria);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.autenticacao__erro {
  color: #dc2626;
  font-size: .85rem;
  margin: 0;
  text-align: center;
  font-weight: 500;
}

.btn {
  border: none;
  border-radius: var(--raio-borda, 8px);
  padding: .85rem 1rem;
  font-weight: 700;
  font-size: .95rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn--primario {
  background: var(--cor-primaria);
  color: #fff;
}

.btn--primario:hover {
  background: var(--cor-primaria-hover);
}

.btn--primario:disabled {
  opacity: .6;
  cursor: not-allowed;
}

.btn--bloco {
  width: 100%;
}
</style>