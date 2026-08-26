<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas_entrega_local', function (Blueprint $table) {
            $table->id();
            $table->string('cep_inicial', 9);
            $table->string('cep_final', 9);
            $table->decimal('valor', 8, 2);
            $table->unsignedTinyInteger('prazo_dias')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas_entrega_local');
    }
};
