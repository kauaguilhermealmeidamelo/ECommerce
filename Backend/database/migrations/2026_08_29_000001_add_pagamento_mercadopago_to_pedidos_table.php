<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // ID do pagamento no Mercado Pago (data.id do webhook) — único
            // pra nunca dois pedidos ficarem associados ao mesmo pagamento,
            // e usado como segunda trava de idempotência além do status.
            $table->string('mercadopago_payment_id')->nullable()->unique()->after('total');

            // Valores possíveis: pix, credit_card, debit_card, ticket
            // (boleto) — vem de payment_type_id na resposta da API do MP.
            $table->string('metodo_pagamento')->nullable()->after('mercadopago_payment_id');

            $table->timestamp('pago_em')->nullable()->after('metodo_pagamento');

            // status_detail do MP quando o pagamento é recusado (ex:
            // cc_rejected_insufficient_amount) — ajuda o lojista a
            // entender por que sem precisar abrir o painel do MP.
            $table->string('motivo_recusa')->nullable()->after('pago_em');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['mercadopago_payment_id', 'metodo_pagamento', 'pago_em', 'motivo_recusa']);
        });
    }
};
