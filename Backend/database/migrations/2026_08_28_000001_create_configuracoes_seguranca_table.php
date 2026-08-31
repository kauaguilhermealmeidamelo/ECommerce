<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uma única linha (singleton), igual ao padrão já usado em
     * ConfiguracaoLoja / ConfiguracaoEntrega — sempre lida via first()
     * ou criada com os padrões abaixo.
     */
    public function up(): void
    {
        Schema::create('configuracoes_seguranca', function (Blueprint $table) {
            $table->id();
            $table->boolean('notificacoes_email')->default(true);
            // Ao ativar, o login de administrador passa a exigir um código
            // de 6 dígitos enviado por e-mail (ver AuthController::login /
            // verificarDoisFatores). Nunca habilite sem MAIL_* configurado
            // no .env, ou o admin pode ficar trancado pra fora do painel.
            $table->boolean('autenticacao_dois_fatores')->default(false);
            // Enquanto ativo, o storefront público retorna 503 (ver
            // middleware ModoManutencao) — o painel /admin continua
            // acessível normalmente pra quem já está autenticado.
            $table->boolean('modo_manutencao')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_seguranca');
    }
};