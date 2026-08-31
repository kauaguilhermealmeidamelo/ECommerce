<template>
  <header class="topo">
    <router-link :to="{ name: 'home' }" class="topo__marca">
      <img v-if="tema.logo" :src="tema.logo" :alt="tema.nome" class="topo__logo-img" />
      <span v-else class="topo__logo-texto">{{ tema.nome }}</span>
    </router-link>

    <div class="busca topo__busca">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
      </svg>
      <input
        v-model="busca"
        type="text"
        placeholder="Buscar produtos..."
        @keyup.enter="buscar"
      />
      <button v-if="busca" class="topo__limpar-busca" @click="busca = ''" aria-label="Limpar busca">✕</button>
    </div>

    <div class="topo__acoes">
      <router-link :to="{ name: 'carrinho' }" class="topo__icone-botao" aria-label="Carrinho">
        🛒
        <span v-if="quantidadeCarrinho > 0" class="topo__contador">{{ quantidadeCarrinho }}</span>
      </router-link>

      <div class="topo__conta">
        <button class="topo__usuario" @click="menuAberto = !menuAberto">
          <span class="avatar">{{ auth.autenticado ? iniciais : '👤' }}</span>
          <span class="topo__usuario-nome">{{ auth.autenticado ? auth.primeiroNome : 'Entrar' }}</span>
        </button>

        <div v-if="menuAberto" class="topo__dropdown" @click="menuAberto = false">
          <template v-if="auth.autenticado">
            <router-link :to="{ name: 'meus-pedidos' }" class="topo__dropdown-item-link">📦 Meus pedidos</router-link>
            <button class="topo__dropdown-sair" @click="sair">🚪 Sair</button>
          </template>
          <template v-else>
            <router-link :to="{ name: 'login-cliente' }" class="topo__dropdown-item-link">Entrar</router-link>
            <router-link :to="{ name: 'cadastro' }" class="topo__dropdown-item-link">Criar conta</router-link>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { tema } from '@/theme/tema'
import { useClienteAuthStore } from '@/stores/clienteAuth'
import apiLoja from '@/services/apiLoja'

const router = useRouter()
const auth = useClienteAuthStore()

const busca = ref('')
const menuAberto = ref(false)
const quantidadeCarrinho = ref(0)

const iniciais = computed(() => {
  const nome = auth.usuario?.name ?? '?'
  return nome.trim().split(/\s+/).slice(0, 2).map((p) => p[0]).join('').toUpperCase()
})

function buscar() {
  if (!busca.value.trim()) return
  router.push({ name: 'catalogo', query: { busca: busca.value } })
}

function sair() {
  auth.logout()
  router.push({ name: 'home' })
}

async function carregarQuantidadeCarrinho() {
  try {
    const { data } = await apiLoja.get('/carrinho')
    quantidadeCarrinho.value = (data.data?.itens ?? []).reduce((soma, item) => soma + item.quantidade, 0)
  } catch (e) {
    quantidadeCarrinho.value = 0
  }
}

defineExpose({ carregarQuantidadeCarrinho })
onMounted(carregarQuantidadeCarrinho)
</script>

<style scoped>
.topo {
  background: var(--cor-superficie);
  border-bottom: 1px solid var(--cor-linha);
  padding: .8rem 1rem;
  display: flex;
  align-items: center;
  gap: .75rem;
  flex-shrink: 0;
  position: sticky;
  top: 0;
  z-index: 30;
}

.topo__marca { display: flex; align-items: center; flex-shrink: 0; }
.topo__logo-img { height: 30px; }
.topo__logo-texto { font-family: var(--fonte-display); font-size: 1.2rem; font-weight: 700; color: var(--cor-texto); }

.busca {
  display: flex; align-items: center; gap: .5rem;
  background: var(--cor-fundo); border: 1px solid var(--cor-linha); border-radius: var(--raio-borda);
  padding: .55rem .8rem;
}
.busca svg { flex-shrink: 0; color: var(--cor-texto-suave); }
.busca input { border: none; background: transparent; padding: 0; flex: 1; color: var(--cor-texto); }
.busca input:focus { outline: none; box-shadow: none; }
.topo__limpar-busca { border: none; background: none; color: var(--cor-texto-suave); cursor: pointer; }
.topo__busca { flex: 1; max-width: 380px; margin: 0 auto; display: none; }

.topo__acoes { display: flex; align-items: center; gap: .4rem; margin-left: auto; flex-shrink: 0; }

.topo__icone-botao {
  position: relative; padding: .55rem; border-radius: var(--raio-borda);
  font-size: 1.05rem; line-height: 1; text-decoration: none;
}
.topo__icone-botao:hover { background: var(--cor-fundo); }
.topo__contador {
  position: absolute; top: 2px; right: 2px;
  background: var(--cor-primaria); color: #fff; font-size: .6rem; font-weight: 700;
  min-width: 15px; height: 15px; border-radius: 999px;
  display: flex; align-items: center; justify-content: center; padding: 0 .2rem;
}

.topo__conta { position: relative; }
.topo__usuario {
  display: flex; align-items: center; gap: .5rem; background: none;
  border: 1px solid var(--cor-linha); border-radius: var(--raio-borda);
  padding: .3rem .6rem .3rem .3rem; cursor: pointer;
}
.topo__usuario:hover { background: var(--cor-fundo); }
.topo__usuario-nome { display: none; font-size: .82rem; font-weight: 600; color: var(--cor-texto); }
.avatar {
  width: 30px; height: 30px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .95rem; flex-shrink: 0;
  background: var(--cor-fundo); color: var(--cor-primaria);
}

.topo__dropdown {
  position: absolute; right: 0; top: 3rem; width: 180px;
  background: var(--cor-superficie); border-radius: var(--raio-borda);
  box-shadow: 0 10px 30px rgba(0,0,0,.1); border: 1px solid var(--cor-linha);
  overflow: hidden; z-index: 50; display: flex; flex-direction: column;
}
.topo__dropdown-item-link {
  padding: .75rem 1rem; font-size: .82rem; font-weight: 600; color: var(--cor-texto);
  text-decoration: none; border-bottom: 1px solid var(--cor-linha);
}
.topo__dropdown-item-link:last-child { border-bottom: none; }
.topo__dropdown-item-link:hover { background: var(--cor-fundo); }
.topo__dropdown-sair {
  text-align: left; background: none; border: none; padding: .75rem 1rem;
  font-size: .82rem; font-weight: 600; color: #dc2626; cursor: pointer;
}
.topo__dropdown-sair:hover { background: #fef2f2; }

@media (min-width: 640px) {
  .topo__busca { display: flex; }
  .topo__usuario-nome { display: block; }
}
</style>
