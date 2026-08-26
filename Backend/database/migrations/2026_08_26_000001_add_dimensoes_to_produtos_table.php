<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable + com fallback no código: lojista pode não preencher ainda,
     * o EntregaService usa um valor padrão conservador nesse caso (ver
     * EntregaService::dimensoesDoProduto()), mas o correto é cadastrar
     * peso/dimensões reais pra cotação não sair errada.
     */
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->unsignedInteger('peso_gramas')->nullable()->after('estoque');
            $table->unsignedSmallInteger('altura_cm')->nullable()->after('peso_gramas');
            $table->unsignedSmallInteger('largura_cm')->nullable()->after('altura_cm');
            $table->unsignedSmallInteger('comprimento_cm')->nullable()->after('largura_cm');
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['peso_gramas', 'altura_cm', 'largura_cm', 'comprimento_cm']);
        });
    }
};
