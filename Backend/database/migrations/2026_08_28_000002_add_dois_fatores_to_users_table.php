<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Código nunca é salvo em texto puro — sempre hash (Hash::make),
            // igual senha. Comparação usa Hash::check (ver AuthController).
            $table->string('dois_fatores_codigo')->nullable()->after('remember_token');
            $table->timestamp('dois_fatores_expira_em')->nullable()->after('dois_fatores_codigo');
            // Zera a cada novo código gerado; bloqueia depois de 5 tentativas
            // erradas pro mesmo código (evita força bruta de 6 dígitos).
            $table->unsignedTinyInteger('dois_fatores_tentativas')->default(0)->after('dois_fatores_expira_em');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dois_fatores_codigo', 'dois_fatores_expira_em', 'dois_fatores_tentativas']);
        });
    }
};
