<?php

namespace App\Enums;

enum StatusPedido: string
{
    case Pendente = 'pendente';
    // Pagamento em análise no MP (in_process / in_mediation) — comum em
    // boleto recém-gerado ou pagamentos que passam por checagem antifraude.
    case EmAnalise = 'em_analise';
    case Pago = 'pago';
    case Recusado = 'recusado';
    case Estornado = 'estornado';
    case Enviado = 'enviado';
    case Concluido = 'concluido';
    case Cancelado = 'cancelado';

    /**
     * Usado pelo DashboardService pra decidir o que entra em
     * faturamento/lucro — nunca inclua Recusado/Estornado/Cancelado aqui.
     */
    public static function statusDeVendaValida(): array
    {
        return [self::Pago, self::Concluido];
    }
}
