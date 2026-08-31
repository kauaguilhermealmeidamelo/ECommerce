<template>
  <div class="pagina">
    <div class="pagina__cabecalho">
      <div>
        <h1 class="pagina__titulo">Clientes</h1>
        <p class="pagina__subtitulo">{{ filtrados.length }} encontrado{{ filtrados.length !== 1 ? 's' : '' }}</p>
      </div>
      <button class="btn btn--secundario" :class="{ 'btn--ativo': !!busca }" @click="filtroAberto = true">
        <v-icon icon="mdi-magnify" size="small" class="mr-1"></v-icon> Filtros
      </button>
    </div>

    <p class="aviso-cadastro">
      ℹ️ Clientes se cadastram sozinhos pela loja (login/criação de conta no storefront). Não existe cadastro manual
      aqui —
      esta tela é só consulta.
    </p>

    <div class="grade-mini-stats">
      <div class="card mini-stat">
        <div class="icon-box icon-box--roxo"><v-icon icon="mdi-star-outline"></v-icon></div>
        <div><strong>{{ contagem.vip }}</strong><span>VIP (gastaram mais)</span></div>
      </div>
      <div class="card mini-stat">
        <div class="icon-box icon-box--azul"><v-icon icon="mdi-account-group-outline"></v-icon></div>
        <div><strong>{{ contagem.regular }}</strong><span>Regulares</span></div>
      </div>
      <div class="card mini-stat">
        <div class="icon-box icon-box--verde"><v-icon icon="mdi-account-plus-outline"></v-icon></div>
        <div><strong>{{ contagem.novo }}</strong><span>Novos (30 dias)</span></div>
      </div>
    </div>

    <div class="card">
      <div v-if="carregando" class="estado-carregando">Carregando...</div>
      <p v-else-if="erro" class="erro-mensagem" style="padding:1.5rem">{{ erro }}</p>
      <div v-else-if="filtrados.length === 0" class="estado-vazio">Nenhum cliente encontrado.</div>

      <template v-else>
        <div class="tabela__scroll clientes-tabela--desktop">
          <table class="tabela">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Contato</th>
                <th>Pedidos</th>
                <th>Total Gasto</th>
                <th>Desde</th>
                <th>Perfil</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in filtrados" :key="c.id">
                <td>
                  <div style="display:flex; align-items:center; gap:.6rem">
                    <span class="avatar">{{ inicial(c.nome) }}</span>
                    <strong>{{ c.nome }}</strong>
                  </div>
                </td>
                <td>
                  <div style="font-size:.78rem; color:var(--ink-soft)">{{ c.email }}</div>
                  <div v-if="c.telefone" style="font-size:.72rem; color:var(--ink-faint)">{{ c.telefone }}</div>
                </td>
                <td><strong>{{ c.total_pedidos }}</strong></td>
                <td><strong>{{ formatarMoeda(c.total_gasto) }}</strong></td>
                <td>{{ formatarData(c.desde) }}</td>
                <td><span class="badge" :class="perfil(c).badge">{{ perfil(c).rotulo }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="lista-mobile clientes-tabela--mobile">
          <div v-for="c in filtrados" :key="c.id" class="lista-mobile__item">
            <span class="avatar">{{ inicial(c.nome) }}</span>
            <div style="flex:1; min-width:0">
              <div style="display:flex; align-items:center; gap:.4rem">
                <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis">{{ c.nome }}</strong>
                <span class="badge" :class="perfil(c).badge" style="flex-shrink:0">{{ perfil(c).rotulo }}</span>
              </div>
              <p class="clientes__meta">{{ c.email }}</p>
              <p class="clientes__meta">{{ c.total_pedidos }} pedido(s) · {{ formatarMoeda(c.total_gasto) }}</p>
            </div>
          </div>
        </div>
      </template>
    </div>

    <Modal :aberto="filtroAberto" titulo="Filtrar Clientes" @fechar="filtroAberto = false">
      <div class="filtro-campo">
        <label>Buscar</label>
        <div class="busca">
          <v-icon icon="mdi-magnify" size="small" class="ml-2"></v-icon>
          <input v-model="buscaPendente" placeholder="Nome ou e-mail..." />
        </div>
      </div>
      <div class="filtro-campo">
        <label>Perfil</label>
        <div class="filtro-chips">
          <button v-for="p in ['Todos', 'VIP', 'Regular', 'Novo']" :key="p" class="filtro-chip"
            :class="{ 'filtro-chip--ativo': perfilPendente === p }" @click="perfilPendente = p">{{ p }}</button>
        </div>
      </div>
      <template #rodape>
        <button class="btn btn--fantasma" @click="limparFiltro">Limpar</button>
        <button class="btn btn--primario" @click="aplicarFiltro">Aplicar</button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'

const clientes = ref([])
const carregando = ref(true)
const erro = ref(null)

const busca = ref('')
const perfilFiltro = ref('Todos')
const filtroAberto = ref(false)
const buscaPendente = ref('')
const perfilPendente = ref('Todos')

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const formatarData = (d) => new Date(d).toLocaleDateString('pt-BR')
const inicial = (nome) => (nome ?? '?').trim().charAt(0).toUpperCase()

function perfil(cliente) {
  const diasDesdeCadastro = (Date.now() - new Date(cliente.desde).getTime()) / 86400000
  if (diasDesdeCadastro <= 30) return { rotulo: 'Novo', badge: 'badge--success' }
  if (cliente.total_gasto >= 2000) return { rotulo: 'VIP', badge: 'badge--info' }
  return { rotulo: 'Regular', badge: 'badge--azul' }
}

const contagem = computed(() => ({
  vip: clientes.value.filter((c) => perfil(c).rotulo === 'VIP').length,
  regular: clientes.value.filter((c) => perfil(c).rotulo === 'Regular').length,
  novo: clientes.value.filter((c) => perfil(c).rotulo === 'Novo').length,
}))

const filtrados = computed(() => clientes.value.filter((c) => {
  const q = busca.value.toLowerCase()
  const buscaOk = c.nome.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
  const perfilOk = perfilFiltro.value === 'Todos' || perfil(c).rotulo === perfilFiltro.value
  return buscaOk && perfilOk
}))

function aplicarFiltro() { busca.value = buscaPendente.value; perfilFiltro.value = perfilPendente.value; filtroAberto.value = false }
function limparFiltro() { busca.value = ''; perfilFiltro.value = 'Todos'; buscaPendente.value = ''; perfilPendente.value = 'Todos'; filtroAberto.value = false }

async function carregar() {
  carregando.value = true
  try {
    const { data } = await api.get('/admin/clientes')
    clientes.value = data.data
  } catch (e) {
    erro.value = 'Não foi possível carregar os clientes.'
  } finally {
    carregando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.aviso-cadastro {
  background: var(--blue-50);
  color: var(--blue-700);
  font-size: .78rem;
  padding: .75rem 1rem;
  border-radius: var(--radius-md);
}

.mini-stat {
  padding: .9rem;
  display: flex;
  align-items: center;
  gap: .7rem;
}

.mini-stat strong {
  display: block;
  font-size: 1.1rem;
  font-weight: 800;
  color: var(--ink);
}

.mini-stat span {
  font-size: .7rem;
  color: var(--ink-soft);
}

.clientes-tabela--mobile {
  display: flex;
  flex-direction: column;
}

.clientes-tabela--desktop {
  display: none;
}

@media (min-width: 640px) {
  .clientes-tabela--mobile {
    display: none;
  }

  .clientes-tabela--desktop {
    display: block;
  }
}

.clientes__meta {
  font-size: .72rem;
  color: var(--ink-faint);
  margin: .1rem 0 0;
}

.filtro-campo {
  margin-bottom: 1.1rem;
}

.filtro-campo label {
  display: block;
  font-size: .75rem;
  font-weight: 700;
  color: var(--ink);
  margin-bottom: .45rem;
}

.filtro-chips {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
}
</style>