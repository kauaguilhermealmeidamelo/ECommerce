import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Prefixo não-óbvio, único por cliente — defina em .env como algo tipo
// VITE_ADMIN_PATH=painel-9x4k2. Isso NÃO substitui autenticação: é só
// mais uma camada pra dificultar quem tenta achar o painel só chutando
// /admin. A proteção de verdade continua sendo o requerAuth abaixo +
// o middleware "admin" no backend.
const prefixo = import.meta.env.VITE_ADMIN_PATH || 'painel'

const routes = [
  {
    path: `/${prefixo}/login`,
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
  },
  {
    path: `/${prefixo}`,
    component: AdminLayout,
    meta: { requerAuth: true },
    children: [
      { path: '', name: 'dashboard', component: () => import('@/views/DashboardView.vue') },
      { path: 'produtos', name: 'produtos', component: () => import('@/views/ProdutosView.vue') },
      { path: 'produtos/novo', name: 'produto-novo', component: () => import('@/views/ProdutoFormView.vue') },
      {
        path: 'produtos/:id/editar',
        name: 'produto-editar',
        component: () => import('@/views/ProdutoFormView.vue'),
        props: true,
      },
      { path: 'pedidos', name: 'pedidos', component: () => import('@/views/PedidosView.vue') },
      { path: 'clientes', name: 'clientes', component: () => import('@/views/ClientesView.vue') },
      { path: 'envios', name: 'envios', component: () => import('@/views/EnviosView.vue') },
      { path: 'entregas', name: 'entregas', component: () => import('@/views/EntregasView.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  // Se a rota exige autenticação e o usuário NÃO está logado:
  if (to.meta.requerAuth && !auth.autenticado) {
    if (to.name !== 'login') {
      return { name: 'login' }
    }
  }

  // Se o usuário já está logado e tenta ir para a tela de login:
  if (to.name === 'login' && auth.autenticado) {
    return { name: 'dashboard' }
  }
})

export default router