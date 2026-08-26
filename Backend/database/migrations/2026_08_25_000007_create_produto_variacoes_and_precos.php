<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nem todo brechó vende peça com tamanho variável (ex: bolsas, acessórios).
     * Por isso isso vira uma tabela à parte em vez de mexer no estoque do
     * produto principal — produto sem variação cadastrada usa o estoque
     * antigo normalmente.
     */
    public function up(): void
    {
        Schema::create('produto_variacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->enum('tamanho', ['PP', 'P', 'M', 'G', 'GG', 'XG']);
            $table->unsignedInteger('estoque')->default(0);
            $table->timestamps();

            $table->unique(['produto_id', 'tamanho']);
        });

        Schema::table('produtos', function (Blueprint $table) {
            // Preço "de" — se null, não mostra o riscado.
            $table->decimal('preco_original', 10, 2)->nullable()->after('preco');
            $table->unsignedTinyInteger('max_parcelas')->default(4)->after('preco_original');
            $table->decimal('desconto_pix_percentual', 5, 2)->default(5)->after('max_parcelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_variacoes');

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['preco_original', 'max_parcelas', 'desconto_pix_percentual']);
        });
    }
};
