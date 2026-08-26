<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('metodo_entrega', ['retirada', 'local', 'transportadora'])->nullable()->after('total');
            $table->decimal('valor_frete', 8, 2)->default(0)->after('metodo_entrega');
            $table->string('codigo_rastreio')->nullable()->after('valor_frete');
            $table->string('transportadora')->nullable()->after('codigo_rastreio');
            $table->timestamp('enviado_em')->nullable()->after('transportadora');

            // Snapshot do endereço no momento da compra — não depende do endereço
            // atual do cliente, que pode mudar depois.
            $table->string('destinatario_nome')->nullable();
            $table->string('destinatario_cep', 9)->nullable();
            $table->string('destinatario_endereco')->nullable();
            $table->string('destinatario_numero', 20)->nullable();
            $table->string('destinatario_complemento')->nullable();
            $table->string('destinatario_bairro')->nullable();
            $table->string('destinatario_cidade')->nullable();
            $table->string('destinatario_uf', 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'metodo_entrega', 'valor_frete', 'codigo_rastreio', 'transportadora', 'enviado_em',
                'destinatario_nome', 'destinatario_cep', 'destinatario_endereco', 'destinatario_numero',
                'destinatario_complemento', 'destinatario_bairro', 'destinatario_cidade', 'destinatario_uf',
            ]);
        });
    }
};
