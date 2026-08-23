<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private MercadoPagoService $mercadoPagoService,
        private PedidoService $pedidoService,
    ) {}

    public function mercadoPago(Request $request)
    {
        // Em produção: validar o header x-signature contra o segredo do webhook
        // antes de processar qualquer coisa, para garantir que a notificação é legítima.
        $this->validarAssinatura($request);

        $paymentId = $request->input('data.id');

        if (! $paymentId) {
            return response()->json(['message' => 'Notificação ignorada.'], 200);
        }

        try {
            // Nunca confia no status vindo do corpo do webhook — sempre reconsulta na API.
            $dadosPagamento = $this->mercadoPagoService->consultarPagamento($paymentId);
            $this->pedidoService->confirmarPagamento($dadosPagamento);
        } catch (\Throwable $e) {
            Log::error('Falha ao processar webhook do Mercado Pago', ['erro' => $e->getMessage()]);
        }

        // Sempre responde 200 rapidamente, mesmo em caso de erro interno,
        // para o Mercado Pago não ficar reenviando a notificação indefinidamente.
        return response()->json(['message' => 'ok'], 200);
    }

    private function validarAssinatura(Request $request): void
    {
        // Implementação real: comparar o header x-signature usando o
        // webhook secret configurado no painel do Mercado Pago (hash HMAC-SHA256).
    }
}
