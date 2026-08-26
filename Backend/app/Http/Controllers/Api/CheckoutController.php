<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinalizarCheckoutRequest;
use App\Models\Carrinho;
use App\Services\CarrinhoService;
use App\Services\EntregaService;
use App\Services\MercadoPagoService;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CarrinhoService $carrinhoService,
        private readonly EntregaService $entregaService,
        private readonly PedidoService $pedidoService,
        private readonly MercadoPagoService $mercadoPagoService,
    ) {
    }

    /**
     * POST /api/checkout/finalizar
     * AJUSTE os nomes de método abaixo (obterCarrinhoAtual, criarAPartirDoCarrinho,
     * criarPreferencia) pros nomes reais já existentes nos seus services.
     */
    public function finalizar(FinalizarCheckoutRequest $request): JsonResponse
    {
        $dados = $request->validated();
        $sessaoId = $request->header('X-Session-Id');

        $carrinho = $this->carrinhoService->obterCarrinhoAtual($sessaoId, $request->user()?->id);

        if ($carrinho->itens->isEmpty()) {
            throw ValidationException::withMessages(['carrinho' => 'Seu carrinho está vazio.']);
        }

        $valorFrete = $this->calcularFrete($dados, $carrinho);

        $pedido = $this->pedidoService->criarAPartirDoCarrinho(
            carrinho: $carrinho,
            dadosEntrega: $dados,
            valorFrete: $valorFrete,
        );

        $preferencia = $this->mercadoPagoService->criarPreferencia($pedido);

        return response()->json([
            'data' => [
                'pedido_id' => $pedido->id,
                'valor_frete' => $valorFrete,
                'total' => $pedido->total,
                'checkout_url' => $preferencia['init_point'],
            ],
        ]);
    }

    /**
     * POST /api/checkout/frete
     * Pra retirada/local, retorna um valor único. Pra transportadora,
     * retorna uma LISTA de opções (Correios, Jadlog etc) — o frontend
     * mostra a lista e o cliente escolhe uma antes de finalizar.
     */
    public function calcularFreteEndpoint(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'metodo_entrega' => ['required', 'in:retirada,local,transportadora'],
            'cep' => ['required_unless:metodo_entrega,retirada', 'string'],
        ]);

        $sessaoId = $request->header('X-Session-Id');
        $carrinho = $this->carrinhoService->obterCarrinhoAtual($sessaoId, $request->user()?->id);

        if ($dados['metodo_entrega'] === 'transportadora') {
            $opcoes = $this->cotarTransportadoraDoCarrinho($dados['cep'], $carrinho);

            if (empty($opcoes)) {
                throw ValidationException::withMessages([
                    'cep' => 'Não conseguimos cotar frete de transportadora pra esse CEP agora.',
                ]);
            }

            return response()->json(['data' => ['opcoes' => $opcoes]]);
        }

        return response()->json(['data' => ['valor_frete' => $this->calcularFrete($dados, $carrinho)]]);
    }

    private function calcularFrete(array $dados, Carrinho $carrinho): float
    {
        return match ($dados['metodo_entrega']) {
            'retirada' => 0.0,
            'local' => $this->resolverFreteLocal($dados['cep']),
            'transportadora' => $this->resolverFreteTransportadora($dados, $carrinho),
        };
    }

    private function resolverFreteLocal(string $cep): float
    {
        $zona = $this->entregaService->calcularFreteLocal($cep);

        if (!$zona) {
            throw ValidationException::withMessages([
                'cep' => 'Esse CEP está fora da área de entrega local. Escolha outro método.',
            ]);
        }

        return $zona['valor'];
    }

    /**
     * SEGURANÇA: nunca usar um valor de frete vindo do frontend direto.
     * Recotamos aqui e só aceitamos o serviço se ele ainda aparecer entre
     * as opções válidas — o cliente não pode inventar um preço menor.
     */
    private function resolverFreteTransportadora(array $dados, Carrinho $carrinho): float
    {
        if (empty($dados['servico'])) {
            throw ValidationException::withMessages([
                'servico' => 'Escolha uma opção de transportadora antes de finalizar.',
            ]);
        }

        $opcoes = $this->cotarTransportadoraDoCarrinho($dados['cep'], $carrinho);

        $escolhida = collect($opcoes)->firstWhere('servico', $dados['servico']);

        if (!$escolhida) {
            throw ValidationException::withMessages([
                'servico' => 'Essa opção de frete não está mais disponível — recalcule antes de finalizar.',
            ]);
        }

        return $escolhida['preco'];
    }

    private function cotarTransportadoraDoCarrinho(string $cep, Carrinho $carrinho): array
    {
        $itens = $carrinho->itens->map(fn ($item) => [
            'produto_id' => $item->produto_id,
            'quantidade' => $item->quantidade,
        ])->toArray();

        return $this->entregaService->cotarTransportadora($cep, $itens);
    }
}
