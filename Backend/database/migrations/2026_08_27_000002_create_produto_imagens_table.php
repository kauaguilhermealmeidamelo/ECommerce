<?php
// Backend/database/migrations/2026_08_27_000002_create_produto_imagens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_imagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('caminho'); // caminho no disco 'public', ex: produtos/abc123.jpg
            $table->unsignedSmallInteger('ordem')->default(0); // posição no carrossel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_imagens');
    }
};