<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EntregaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntregaController extends Controller
{
    public function __construct(private readonly EntregaService $entregaService)
    {
    }

    public function mostrar(): JsonResponse
    {
        return response()->json(['data' => $this->entregaService->obterConfiguracao()]);
    }

    public function atualizar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'config.retirada_ativa' => ['boolean'],
            'config.entrega_local_ativa' => ['boolean'],
            'config.transportadora_ativa' => ['boolean'],
            'config.token_melhor_envio' => ['nullable', 'string'],
            'zonas' => ['array'],
            'zonas.*.cep_inicial' => ['required_with:zonas', 'string'],
            'zonas.*.cep_final' => ['required_with:zonas', 'string'],
            'zonas.*.valor' => ['required_with:zonas', 'numeric', 'min:0'],
            'zonas.*.prazo_dias' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->entregaService->salvarConfiguracao($dados['config'] ?? [], $dados['zonas'] ?? []);

        return response()->json(['data' => $this->entregaService->obterConfiguracao()]);
    }

    /**
     * GET /api/frete/opcoes?cep=00000000&produto_id=1&quantidade=1
     * produto_id/quantidade são opcionais — sem eles, cotação de
     * transportadora é omitida (retirada e entrega local não precisam disso).
     */
    public function opcoes(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'cep' => ['required', 'string'],
            'produto_id' => ['nullable', 'integer', 'exists:produtos,id'],
            'quantidade' => ['nullable', 'integer', 'min:1'],
        ]);

        $opcoes = $this->entregaService->opcoesParaCep(
            $dados['cep'],
            $dados['produto_id'] ?? null,
            $dados['quantidade'] ?? 1,
        );

        return response()->json(['data' => $opcoes]);
    }
}
