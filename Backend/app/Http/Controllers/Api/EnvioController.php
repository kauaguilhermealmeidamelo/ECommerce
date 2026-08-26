<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusPedido;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    /**
     * GET /api/admin/envios/pendentes
     * Pedidos pagos, com método de entrega local ou transportadora,
     * que ainda não têm "enviado_em" preenchido — prontos pra postar.
     */
    public function pendentes(): JsonResponse
    {
        $pedidos = Pedido::with('itens.produto')
            ->where('status', StatusPedido::Pago->value)
            ->whereIn('metodo_entrega', ['local', 'transportadora'])
            ->whereNull('enviado_em')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Pedido $pedido) => [
                'id' => $pedido->id,
                'criado_em' => $pedido->created_at,
                'metodo_entrega' => $pedido->metodo_entrega,
                'valor_frete' => $pedido->valor_frete,
                'itens' => $pedido->itens->map(fn ($item) => [
                    'produto' => $item->produto->nome,
                    'quantidade' => $item->quantidade,
                ]),
                'destinatario' => [
                    'nome' => $pedido->destinatario_nome,
                    'cep' => $pedido->destinatario_cep,
                    'endereco' => $pedido->destinatario_endereco,
                    'numero' => $pedido->destinatario_numero,
                    'complemento' => $pedido->destinatario_complemento,
                    'bairro' => $pedido->destinatario_bairro,
                    'cidade' => $pedido->destinatario_cidade,
                    'uf' => $pedido->destinatario_uf,
                ],
            ]);

        return response()->json(['data' => $pedidos]);
    }

    /**
     * PATCH /api/admin/envios/{pedido}/marcar-enviado
     */
    public function marcarEnviado(Request $request, Pedido $pedido): JsonResponse
    {
        $dados = $request->validate([
            'codigo_rastreio' => ['required', 'string', 'max:100'],
            'transportadora' => ['required', 'string', 'max:100'],
        ]);

        $pedido->update([
            'codigo_rastreio' => $dados['codigo_rastreio'],
            'transportadora' => $dados['transportadora'],
            'enviado_em' => now(),
        ]);

        return response()->json(['data' => $pedido]);
    }
}
