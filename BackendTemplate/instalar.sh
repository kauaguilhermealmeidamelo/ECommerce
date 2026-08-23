#!/usr/bin/env bash
# Roda isso de DENTRO da pasta do projeto Laravel já criado
# (depois do `composer create-project laravel/laravel loja-backend "^11.0"` e `cd loja-backend`)
#
# Uso:
#   ./instalar.sh /caminho/para/loja-backend-template/loja-backend
#
set -e

TEMPLATE_DIR="$1"

if [ -z "$TEMPLATE_DIR" ] || [ ! -d "$TEMPLATE_DIR" ]; then
  echo "Uso: ./instalar.sh /caminho/para/loja-backend (a pasta extraida do zip do template)"
  exit 1
fi

if [ ! -f "artisan" ]; then
  echo "Rode este script de dentro da raiz de um projeto Laravel (onde está o arquivo 'artisan')."
  exit 1
fi

echo "==> Instalando dependências extras (Sanctum, Socialite, Mercado Pago SDK)..."
composer require laravel/sanctum laravel/socialite mercadopago/dx-php --with-all-dependencies

echo "==> Publicando config do Sanctum..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

echo "==> Copiando código do template (Models, Controllers, Services, Migrations, rotas)..."
cp -R "$TEMPLATE_DIR/app/." app/
cp -R "$TEMPLATE_DIR/database/migrations/." database/migrations/
cp "$TEMPLATE_DIR/routes/api.php" routes/api.php
cp "$TEMPLATE_DIR/public/robots.txt" public/robots.txt
cp "$TEMPLATE_DIR/.htaccess.raiz-do-projeto" .htaccess
cp "$TEMPLATE_DIR/SECURITY.md" SECURITY.md

echo "==> Removendo migration/model User padrão do Laravel (o template usa Cliente/Administrador)..."
rm -f database/migrations/*_create_users_table.php
rm -f app/Models/User.php

echo ""
echo "Falta manual (não dá pra automatizar com segurança):"
echo "  1. bootstrap/app.php  -> substitua pelo bootstrap/app.php deste pacote"
echo "  2. config/services.php -> acrescente o conteúdo de config-services-adicionar.php"
echo "  3. .env -> copie os valores de .env.example deste pacote e preencha as credenciais"
echo ""
echo "Depois disso: php artisan migrate"
