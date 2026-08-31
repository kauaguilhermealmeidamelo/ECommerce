<template>
  <div class="layout">
    <TopHeader :titulo="tituloPagina.titulo" :subtitulo="tituloPagina.subtitulo" :notificacoes="notificacoes" />

    <main class="layout__conteudo">
      <router-view />
    </main>

    <BottomNav :itens="itensNav" />
    <DesktopNav :itens="itensNav" />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import TopHeader from '@/components/TopHeader.vue'
import BottomNav from '@/components/BottomNav.vue'
import DesktopNav from '@/components/DesktopNav.vue'
import api from '@/services/api'

const route = useRoute()

// 5 itens — os mesmos do protótipo (Dashboard, Produtos, Pedidos, Clientes,
// Configurações). Categorias fica dentro de Produtos e Envios dentro de
// Pedidos (aba), pra não poluir a navegação principal.
const pedidosPendentesEnvio = ref(0)
const totalProdutos = ref(0)

const itensNav = computed(() => [
  { rota: 'dashboard', label: 'Início', icone: 'mdi-home' },
  { rota: 'produtos', label: 'Produtos', icone: 'mdi-package-variant', contador: totalProdutos.value || null },
  { rota: 'pedidos', label: 'Pedidos', icone: 'mdi-shopping', contador: pedidosPendentesEnvio.value || null },
  { rota: 'clientes', label: 'Clientes', icone: 'mdi-account-group' },
  { rota: 'configuracoes', label: 'Config.', icone: 'mdi-cog' },
])

const titulosPorRota = {
  dashboard: { titulo: 'Dashboard', subtitulo: novaDataFormatada() },
  produtos: { titulo: 'Produtos', subtitulo: 'Gerencie seu catálogo' },
  'produto-novo': { titulo: 'Produtos', subtitulo: 'Novo produto' },
  'produto-editar': { titulo: 'Produtos', subtitulo: 'Editar produto' },
  categorias: { titulo: 'Categorias', subtitulo: 'Organize seu catálogo' },
  pedidos: { titulo: 'Pedidos', subtitulo: 'Acompanhe suas vendas' },
  clientes: { titulo: 'Clientes', subtitulo: 'Base de clientes da loja' },
  configuracoes: { titulo: 'Configurações', subtitulo: 'Preferências da loja' },
}

function novaDataFormatada() {
  return new Date().toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}

const tituloPagina = computed(() => titulosPorRota[route.name] ?? { titulo: '', subtitulo: '' })

// Notificações reais e simples: pedidos aguardando envio + avisos de
// estoque baixo. Sem infraestrutura de notificação dedicada no backend
// ainda — isso é só um resumo operacional montado a partir de endpoints
// que já existem.
const notificacoes = ref([])

async function carregarResumoOperacional() {
  try {
    const { data } = await api.get('/admin/envios/pendentes')
    pedidosPendentesEnvio.value = data.data.length

    notificacoes.value = data.data.slice(0, 3).map((pedido) => ({
      texto: `Pedido #${pedido.id} aguardando envio`,
      tempo: new Date(pedido.criado_em).toLocaleDateString('pt-BR'),
      cor: 'azul',
    }))
  } catch (e) {
    // painel segue funcionando normalmente sem o resumo
  }
}

async function carregarTotalProdutos() {
  try {
    const { data } = await api.get('/admin/produtos')
    totalProdutos.value = data.meta?.total ?? data.data.length
  } catch (e) {
    // badge simplesmente não aparece
  }
}

onMounted(() => {
  carregarResumoOperacional()
  carregarTotalProdutos()
})
</script>

<style scoped>
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg);
}

.layout__conteudo {
  flex: 1;
}
</style>