<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable de propósito: produto sem variação de tamanho continua
     * funcionando normalmente, só não preenche esse campo.
     * AJUSTE os nomes das tabelas se forem diferentes de itens_carrinho / itens_pedido.
     */
    public function up(): void
    {
        Schema::table('itens_carrinho', function (Blueprint $table) {
            $table->foreignId('produto_variacao_id')->nullable()->after('produto_id')
                ->constrained('produto_variacoes')->nullOnDelete();
        });

        Schema::table('itens_pedido', function (Blueprint $table) {
            $table->foreignId('produto_variacao_id')->nullable()->after('produto_id')
                ->constrained('produto_variacoes')->nullOnDelete();
            // Snapshot do tamanho no momento da compra — sobrevive mesmo se a
            // variação for excluída depois (nullOnDelete acima).
            $table->string('tamanho', 5)->nullable()->after('produto_variacao_id');
        });
    }

    public function down(): void
    {
        Schema::table('itens_carrinho', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produto_variacao_id');
        });

        Schema::table('itens_pedido', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produto_variacao_id');
            $table->dropColumn('tamanho');
        });
    }
};
