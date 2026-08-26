# Template de Loja Virtual — Backend + FrontEnd

Pacote com as duas partes do sistema, prontas pra descompactar e rodar:

```
Backend/    → API Laravel (php artisan serve)
FrontEnd/   → Painel de administração em Vue (npm run dev)
            → FrontEnd/storefront-base/ → ponto de partida da vitrine pública (não é um app completo, ver nota abaixo)
```

## Como rodar

### 1. Backend

```bash
cd Backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure o banco no `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) e depois:

```bash
php artisan migrate --seed
php artisan serve
```

A API sobe em `http://localhost:8000`. O seeder cria um usuário admin de teste:
- **E-mail:** admin@loja.test
- **Senha:** senha123

### 2. FrontEnd (painel administrativo)

```bash
cd FrontEnd
npm install
cp .env.example .env
npm run dev
```

Abre em `http://localhost:5173`, e as rotas do painel ficam sob o prefixo definido em `VITE_ADMIN_PATH` (ex: `http://localhost:5173/painel-9x4k2/login`).

## ⚠️ Sobre os "STUBs" no Backend

Vários arquivos do Backend estão marcados com o comentário `// STUB` no topo. Isso significa: **essa é uma versão mínima criada só pra esse pacote rodar sozinho**, porque os arquivos reais e completos desses módulos (Produto, Categoria, Pedido, Carrinho, User, AuthController, autenticação com Google) foram desenvolvidos em outras sessões de trabalho, fora desta conversa, e eu não tinha acesso a eles pra incluir aqui.

**Antes de usar este pacote em produção**, substitua os arquivos marcados como STUB pelos seus arquivos reais e equivalentes — os nomes de tabela e de campo usados aqui (`produtos.preco_custo`, `produtos.ativo`, `pedidos.destinatario_*`, etc.) foram mantidos consistentes com tudo que construímos ao longo da conversa, então a integração deve ser direta.

**O que NÃO é stub — já está completo e pode ser usado direto:**
- Dashboard (faturamento, lucro, categorias mais vendidas)
- Visitas do site
- Sistema de entrega (retirada, local por CEP, transportadora) + variação de produto por tamanho
- Envios (lista de pedidos prontos pra postar)
- Clientes e dados da loja
- Configuração "produto expira após venda"
- Webhook do Mercado Pago com validação de assinatura HMAC
- Todo o painel administrativo em Vue (FrontEnd/)

## Sobre a pasta storefront-base

Ela **não é um app Vue completo** (não tem `package.json`, `App.vue` ou roteamento) — é o ponto de partida de componentes themáveis (`tema.js`, `aplicarTema.js`, `ProductCard.vue`, `ProductDetail.vue`) que você usa como base pra montar a vitrine de cada cliente, trocando só a identidade visual em `tema.js`. Monte um projeto Vite/Vue novo em volta desses arquivos por cliente.
