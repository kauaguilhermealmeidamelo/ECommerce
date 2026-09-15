<template>
  <div class="callback">
    <p v-if="!erro">Entrando...</p>
    <p v-else class="callback__erro">Não foi possível concluir o login. Redirecionando...</p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useClienteAuthStore } from '@/stores/clienteAuth'
import apiLoja from '@/services/apiLoja'

const route = useRoute()
const router = useRouter()
const auth = useClienteAuthStore()
const erro = ref(false)

onMounted(async () => {
  const token = route.query.token as string | undefined

  if (!token) {
    router.replace({ name: 'login-cliente' })
    return
  }

  // Salva o token primeiro pra que a chamada seguinte já vá autenticada
  // (o interceptor de request do apiLoja lê do localStorage).
  localStorage.setItem('cliente_token', token)

  try {
    const { data } = await apiLoja.get('/auth/me')
    auth.definirSessao({ token, usuario: data.usuario })
    router.replace({ name: 'home' })
  } catch {
    erro.value = true
    localStorage.removeItem('cliente_token')
    setTimeout(() => router.replace({ name: 'login-cliente' }), 1500)
  }
})
</script>

<style scoped>
.callback {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  color: var(--cor-texto-suave);
  font-size: .95rem;
}

.callback__erro {
  color: #dc2626;
}
</style>