# Painel de gerenciamento (admin padrão)

Este app é **o mesmo para todos os clientes** — a única coisa que muda por loja é
o `.env` apontando pro backend correto. A vitrine pública (o que o cliente final
vê) é um projeto separado, com identidade visual própria por loja.

## Rodando localmente

```bash
npm install
cp .env.example .env   # ajuste VITE_API_URL
npm run dev
```

## Variáveis de ambiente

| Variável | Descrição |
|---|---|
| `VITE_API_URL` | URL base da API Laravel dessa loja (ex: `https://loja1.com/api`) |

## Estrutura

```
src/
  layouts/AdminLayout.vue   # casca com bottom nav, usada por todas as páginas autenticadas
  components/               # StatCard, BottomNav — visual fixo, não mexe por cliente
  views/                    # Login, Dashboard, Produtos, Pedidos, Entregas
  stores/auth.js            # Pinia — token + usuário logado
  services/api.js           # instância Axios com header de auth automático
  router/index.js           # rotas + guarda de autenticação
  style.css                 # tokens de cor/tipografia globais (navy + Playfair Display)
```

## Convenção de endpoints esperados

- `POST /auth/login` → `{ token, usuario }`
- `GET /dashboard` → payload do `DashboardService` (resumo, série mensal, categorias)
- `GET /produtos` → lista paginada de produtos
- `GET /pedidos` → lista paginada de pedidos
- `GET /entregas/configuracao` e `PUT /entregas/configuracao` → métodos ativos + zonas locais
  (ainda não implementado no backend — a tela já está pronta pra consumir assim que existir)

## Próximos passos sugeridos

- Implementar `EntregaController` + `EntregaService` no backend (config por loja + zonas + cotação Melhor Envio)
- Paginação/infinite scroll em Produtos e Pedidos
- Tela de criação/edição de produto
- Guarda de rota por papel (admin vs funcionário), se o template vier a precisar
