<?php

namespace App\Enums;

enum StatusPedido: string
{
    case AguardandoPagamento = 'aguardando_pagamento';
    case Pago = 'pago';
    case Enviado = 'enviado';
    case Entregue = 'entregue';
    case Cancelado = 'cancelado';
}
