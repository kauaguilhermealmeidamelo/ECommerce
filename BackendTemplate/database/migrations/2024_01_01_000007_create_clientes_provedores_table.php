<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes_provedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('provedor'); // 'google'
            $table->string('provedor_id'); // 'sub' retornado pelo Google
            $table->timestamps();

            $table->unique(['provedor', 'provedor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_provedores');
    }
};
