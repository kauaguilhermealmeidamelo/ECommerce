<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_loja', function (Blueprint $table) {
            $table->id();
            // Ligado: assim que o pagamento de um pedido é confirmado, cada
            // produto comprado sai de venda automaticamente (comum em brechó,
            // onde a peça é única e não tem reposição de estoque).
            $table->boolean('produto_expira_apos_venda')->default(false);
            $table->timestamps();
        });

        // Se sua tabela produtos já tiver uma coluna equivalente (ex: `disponivel`),
        // pule essa parte e reaproveite a existente — ajuste o restante do código.
        if (!Schema::hasColumn('produtos', 'ativo')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->boolean('ativo')->default(true)->after('preco');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_loja');

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('ativo');
        });
    }
};
