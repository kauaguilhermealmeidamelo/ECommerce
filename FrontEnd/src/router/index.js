import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useClienteAuthStore } from "@/stores/clienteAuth";
import AdminLayout from "@/layouts/AdminLayout.vue";
import LojaLayout from "@/layouts/LojaLayout.vue";

const prefixo = import.meta.env.VITE_ADMIN_PATH || "painel";

const routes = [
  /*
  |------------------------------------------------------------------
  | Painel Administrativo
  |------------------------------------------------------------------
  */
  {
    path: `/${prefixo}/login`,
    name: "login",
    component: () => import("@/views/LoginView.vue"),
  },
  {
    path: `/${prefixo}/`,
    redirect: { name: "dashboard" },
  },
  {
    path: `/${prefixo}`,
    component: AdminLayout,
    meta: { requerAuth: true },
    children: [
      {
        path: "",
        name: "dashboard",
        component: () => import("@/views/DashboardView.vue"),
      },
      {
        path: "produtos",
        name: "produtos",
        component: () => import("@/views/ProdutosView.vue"),
      },
      {
        path: "produtos/novo",
        name: "produto-novo",
        component: () => import("@/views/ProdutoFormView.vue"),
      },
      {
        path: "produtos/:id/editar",
        name: "produto-editar",
        component: () => import("@/views/ProdutoFormView.vue"),
        props: true,
      },
      {
        path: "categorias",
        name: "categorias",
        component: () => import("@/views/CategoriasView.vue"),
      },
      {
        path: "pedidos",
        name: "pedidos",
        component: () => import("@/views/PedidosView.vue"),
      },
      {
        path: "clientes",
        name: "clientes",
        component: () => import("@/views/ClientesView.vue"),
      },
      {
        path: "configuracoes",
        name: "configuracoes",
        component: () => import("@/views/ConfiguracoesView.vue"),
      },
    ],
  },

  /*
  |------------------------------------------------------------------
  | Vitrine da Loja (Pública e Área do Cliente)
  |------------------------------------------------------------------
  */
  {
    path: "/",
    component: LojaLayout,
    children: [
      {
        path: "",
        name: "home",
        component: () => import("@/views/HomeView.vue"),
      },
      {
        path: "catalogo",
        name: "catalogo",
        component: () => import("@/views/CatalogoView.vue"),
      },
      {
        path: "produto/:id",
        name: "produto",
        component: () => import("@/views/ProdutoView.vue"),
        props: true,
      },
      {
        path: "carrinho",
        name: "carrinho",
        component: () => import("@/views/CarrinhoView.vue"),
      },
      {
        path: "checkout",
        name: "checkout",
        component: () => import("@/views/CheckoutView.vue"),
      },
      {
        path: "pedido-sucesso",
        name: "pedido-sucesso",
        component: () => import("@/views/PedidoSucessoView.vue"),
      },
      {
        path: "cadastro",
        name: "cadastro",
        component: () => import("@/views/CadastroView.vue"),
      },
      {
        path: "entrar",
        name: "login-cliente",
        component: () => import("@/views/LoginClienteView.vue"),
      },
      {
        path: "auth/callback",
        name: "auth-callback",
        component: () => import("@/views/AuthCallbackView.vue"),
      },
      {
        path: "meus-pedidos",
        name: "meus-pedidos",
        component: () => import("@/views/MeusPedidosView.vue"),
        meta: { requerAuthCliente: true },
      },
      {
        path: "meus-pedidos/:id",
        name: "pedido-detalhe",
        component: () => import("@/views/PedidoDetalheView.vue"),
        meta: { requerAuthCliente: true },
      },
    ],
  },

  // Rota curinga para páginas não encontradas
  {
    path: "/:pathMatch(.*)*",
    redirect: { name: "home" },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Guard de navegação para Admin e Cliente
router.beforeEach((to) => {
  const auth = useAuthStore();
  const authCliente = useClienteAuthStore();

  // Proteção do Admin
  if (to.meta.requerAuth && !auth.autenticado) {
    if (to.name !== "login") return { name: "login" };
  }
  if (to.name === "login" && auth.autenticado) {
    return { name: "dashboard" };
  }

  // Proteção da Área do Cliente
  if (to.meta.requerAuthCliente && !authCliente.autenticado) {
    return { name: "login-cliente", query: { redirecionar: to.fullPath } };
  }
  if (
    (to.name === "login-cliente" || to.name === "cadastro") &&
    authCliente.autenticado
  ) {
    return { name: "home" };
  }
});

export default router;