<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2);
            $table->decimal('preco_promocional', 10, 2)->nullable();
            $table->enum('condicao', ['novo', 'seminovo', 'usado'])->default('novo');
            $table->unsignedInteger('estoque')->default(0);
            $table->boolean('ativo')->default(true);
            $table->boolean('destaque')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
