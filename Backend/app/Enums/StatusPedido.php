<?php

namespace App\Enums;

enum StatusPedido: string
{
    case Pendente = 'pendente';
    case Pago = 'pago';
    case Enviado = 'enviado';
    case Concluido = 'concluido';
    case Cancelado = 'cancelado';
}
