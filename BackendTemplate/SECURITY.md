# Checklist de Segurança

Itens organizados por onde o dano seria maior. Trate os três primeiros grupos como obrigatórios em qualquer entrega pro cliente; o resto conforme o orçamento do projeto permitir.

## 1. Exposição básica (o que qualquer um pode achar sem esforço)

- [x] **`robots.txt`** bloqueando indexação de `/admin`, `/api/`, `/carrinho`, `/checkout`, `/pedido/` — arquivo em `public/robots.txt` já incluso. Lembre-se: robots.txt é uma *convenção*, não uma trava. Ele impede que buscadores **indexem** essas rotas nos resultados de busca, mas não impede acesso direto — a segurança real dessas rotas continua sendo autenticação/autorização no backend.
- [ ] **`.env` nunca acessível pela web.** Teste sempre digitando `seu-dominio.com/.env` depois do deploy. O `.htaccess.raiz-do-projeto` incluso bloqueia isso via Apache, mas confirme manualmente em produção.
- [ ] **Listagem de diretório desabilitada** (`Options -Indexes`) — evita que alguém veja a árvore de pastas do projeto acessando uma URL de pasta direto.
- [ ] **Document root apontando pra `public/`**, nunca pra raiz do projeto. Isso sozinho já esconde `app/`, `database/`, `.env` do público. Confira isso primeiro em qualquer hospedagem nova.
- [ ] **`APP_DEBUG=false`** em produção. Com debug ligado, um erro qualquer expõe stack trace completo, incluindo trechos de código e variáveis de ambiente na tela.
- [ ] **`sitemap.xml`** existe e está correto — o `robots.txt` referencia um, então gere de fato (pacotes como `spatie/laravel-sitemap` fazem isso automaticamente a partir dos produtos ativos).

## 2. Autenticação e autorização

- [x] **Rate limiting nas rotas de login** (`throttle:6,1` — 6 tentativas por minuto) em `/auth/login`, `/auth/registro`, `/auth/google` e `/admin/login`. Sem isso, um script tenta milhares de senhas por minuto contra sua base de e-mails.
- [x] **Guard separado para admin** (`administradores`, não `clientes`) com ability própria no token (`admin.token` middleware). Isso impede que um bug de autorização deixe um cliente comum acessar rotas de publicação de produto.
- [ ] **Sem cadastro público de administrador** — confirmado no código (`Admin/AuthController` só tem `login`), mas reforce isso: nunca exponha uma rota que crie admin sem estar você mesmo logado como admin já existente.
- [ ] **Tokens Sanctum com expiração.** Por padrão o Sanctum não expira token — configure `expiration` em `config/sanctum.php` (algo como 7 dias pra cliente, poucas horas pra admin) e sempre revogue no logout (já feito nos controllers).
- [ ] **2FA no login do admin**, se o cliente aceitar o custo de implementar — o painel de admin é o alvo de maior valor (é ele que publica produto e vê pedidos).

## 3. Pagamento (Mercado Pago)

- [ ] **Validar a assinatura `x-signature` no webhook.** No código isso está marcado como pendência (`validarAssinatura()` vazio no `WebhookController`) — **preencher antes de ir pra produção**. Sem isso, qualquer pessoa pode forjar uma notificação de "pagamento aprovado" pro seu endpoint.
- [x] **Nunca confiar no status vindo do corpo do webhook** — o `MercadoPagoService::consultarPagamento` sempre reconsulta o pagamento direto na API do MP antes de marcar como pago.
- [x] **Nunca armazenar número de cartão, CVV ou validade completa** — só os tokens (`mp_customer_id`, `mp_card_id`) que o próprio Mercado Pago devolve depois de tokenizar no navegador do cliente.
- [ ] **Chaves do Mercado Pago (access token) só no `.env`**, nunca versionadas no Git, nunca hardcoded. Usar chave de produção só quando o fluxo estiver testado com credenciais de sandbox.

## 4. Dados e entrada do usuário

- [x] **Mass assignment controlado** — todos os models usam `$fillable` explícito (nunca `$guarded = []`), então um campo extra malicioso no corpo da requisição não consegue sobrescrever coisas como `ativo` ou `id`.
- [x] **Upload de imagem valida conteúdo real, não só extensão** (`mimes:jpg,jpeg,png,webp` + `image`) — impede um arquivo `.php` disfarçado de `.jpg`.
- [ ] **Reprocessar imagem depois do upload** (redimensionar/recomprimir com Intervention Image, por exemplo) — remove metadado EXIF e neutraliza a maior parte de payloads maliciosos escondidos dentro do arquivo, mesmo que ele passe na validação de tipo.
- [ ] **Sanitizar a descrição do produto** se o admin puder digitar HTML (editor rich-text). Se o frontend renderizar essa descrição com `v-html`, um admin malicioso (ou uma conta comprometida) pode injetar script — considere permitir só um subconjunto seguro de tags.
- [x] **Preço do pedido sempre recalculado no backend** no momento do checkout (`PedidoService::criarAPartirDoCarrinho`) — nunca aceita o preço que o frontend mandou, evitando manipulação via DevTools.

## 5. Rede e transporte

- [ ] **HTTPS obrigatório** — redirecionar todo tráfego HTTP pra HTTPS (isso geralmente é configuração do painel da hospedagem ou um `.htaccess` com `RewriteRule` de força HTTPS).
- [x] **Cabeçalhos de segurança** (`CabecalhosSeguranca` middleware incluso): `X-Frame-Options: DENY` (evita clickjacking no checkout), `X-Content-Type-Options`, `Referrer-Policy`, `HSTS` quando já em HTTPS estável.
- [ ] **CORS restrito ao domínio real da loja** — em `config/cors.php`, jamais deixar `allowed_origins => ['*']` numa API que aceita cookies/token de autenticação. Liste explicitamente o domínio do frontend de cada cliente.

## 6. Operação contínua

- [ ] **Backup automático do banco** — pergunte se a hospedagem do cliente já faz isso; se não, agende via cron um `mysqldump` periódico.
- [ ] **Usuário do banco com privilégio mínimo** — a aplicação não precisa de um usuário MySQL com privilégio de administrador do servidor, só CRUD nas tabelas da própria loja.
- [ ] **`composer audit`** e **`npm audit`** de tempos em tempos — checam se alguma dependência do projeto tem vulnerabilidade conhecida já divulgada.
- [ ] **Log sem dado sensível** — nunca logar senha, token completo ou payload de cartão em `storage/logs`. O `payload_json` da tabela `pagamentos` é aceitável guardar porque é o que o próprio Mercado Pago te manda (sem dado de cartão), mas cuidado ao logar outras entradas do checkout.

## 7. LGPD (dados de cliente)

- [ ] **Página de política de privacidade** explicando o que é coletado (nome, e-mail, endereço) e por quê.
- [ ] **Rota para o cliente pedir exclusão da própria conta** — mesmo que manual (via WhatsApp/e-mail no MVP), ter um processo definido.
- [ ] **Não reter dado além do necessário** — por exemplo, não é preciso guardar histórico de carrinho de visitante indefinidamente; um job periódico pode limpar carrinhos de sessão abandonados há mais de X dias.
