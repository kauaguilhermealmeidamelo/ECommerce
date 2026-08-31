<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campos usados pelo footer da vitrine (redes sociais + contato direto).
     * Todos nullable — o cliente pode não ter todas as redes, e o footer
     * só exibe o que estiver preenchido.
     */
    public function up(): void
    {
        Schema::table('informacoes_loja', function (Blueprint $table) {
            $table->string('whatsapp', 20)->nullable()->after('email_contato');
            $table->string('instagram_url')->nullable()->after('whatsapp');
            $table->string('facebook_url')->nullable()->after('instagram_url');
            $table->string('tiktok_url')->nullable()->after('facebook_url');
        });
    }

    public function down(): void
    {
        Schema::table('informacoes_loja', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'instagram_url', 'facebook_url', 'tiktok_url']);
        });
    }
};
