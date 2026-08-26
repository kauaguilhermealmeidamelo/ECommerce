<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\MercadoPagoService;
use App\Services\MercadoPagoWebhookValidator;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookMercadoPagoController extends Controller
{
    public function __construct(
        private readonly MercadoPagoWebhookValidator $validador,
        private readonly MercadoPagoService $mercadoPagoService,
        private readonly PedidoService $pedidoService,
    ) {
    }

    public function processar(Request $request): Response
    {
        if (!$this->validador->valido($request)) {
            abort(401, 'Assinatura inválida.');
        }

        if ($request->input('type') !== 'payment') {
            return response()->noContent();
        }

        $pagamento = $this->mercadoPagoService->consultarPagamento($request->input('data.id'));

        if (($pagamento['status'] ?? null) !== 'approved') {
            return response()->noContent();
        }

        $pedido = Pedido::find($pagamento['external_reference'] ?? null);

        if (!$pedido) {
            Log::warning('Webhook MP: pedido não encontrado para external_reference recebido', [
                'external_reference' => $pagamento['external_reference'] ?? null,
            ]);

            return response()->noContent();
        }

        // Valor pago precisa bater com o total do pedido — evita confirmar
        // um pedido por engano caso o external_reference esteja incorreto
        // ou o pedido tenha sido alterado depois de criado.
        $diferenca = abs((float) $pagamento['transaction_amount'] - (float) $pedido->total);

        if ($diferenca > 0.01) {
            Log::warning('Webhook MP: valor pago diverge do total do pedido, pagamento não confirmado automaticamente', [
                'pedido_id' => $pedido->id,
                'total_pedido' => $pedido->total,
                'valor_pago' => $pagamento['transaction_amount'],
            ]);

            return response()->noContent();
        }

        $this->pedidoService->confirmarPagamento($pedido);

        return response()->noContent();
    }
}
