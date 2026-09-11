<?php

namespace App\Infrastructure\Http\Controllers\Api;

use App\Infrastructure\Http\Controllers\Controller;
use App\Domain\Produtos\UseCases\CriarProdutoUseCase;
use App\Domain\Produtos\UseCases\ListarProdutosUseCase;
use App\Domain\Produtos\UseCases\AtualizarEstoqueUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;

class ProdutoController extends Controller
{
    public function __construct(
        private CriarProdutoUseCase $criarProdutoUseCase,
        private ListarProdutosUseCase $listarProdutosUseCase,
        private AtualizarEstoqueUseCase $atualizarEstoqueUseCase
    ) {}

    public function index(): JsonResponse
    {
        try {
            $produtos = $this->listarProdutosUseCase->executar();
            return response()->json($produtos, 200);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $dados = $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'preco' => 'required|numeric|min:0',
                'preco_custo' => 'nullable|numeric|min:0',
                'estoque' => 'required|integer|min:0',
                'ativo' => 'boolean',
                'categoria_id' => 'nullable|integer',
                'peso' => 'nullable|numeric|min:0',
                'altura' => 'nullable|numeric|min:0',
                'largura' => 'nullable|numeric|min:0',
                'comprimento' => 'nullable|numeric|min:0',
                'variacoes' => 'array',
                'variacoes.*.nome' => 'required|string',
                'variacoes.*.preco' => 'nullable|numeric|min:0',
                'variacoes.*.estoque' => 'required|integer|min:0',
                'imagens' => 'array'
            ]);

            $produto = $this->criarProdutoUseCase->executar($dados);

            return response()->json([
                'mensagem' => 'Produto criado com sucesso!',
                'produto' => $produto
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['erro' => 'Erro interno ao criar produto.'], 500);
        }
    }

    public function atualizarEstoque(Request $request, int $id): JsonResponse
    {
        try {
            $dados = $request->validate([
                'quantidade' => 'required|integer|min:1',
                'operacao' => 'sometimes|in:adicionar,remover,definir',
                'nome_variacao' => 'nullable|string'
            ]);

            $this->atualizarEstoqueUseCase->executar(
                produtoId: $id,
                quantidade: $dados['quantidade'],
                operacao: $dados['operacao'] ?? 'adicionar',
                nomeVariacao: $dados['nome_variacao'] ?? null
            );

            return response()->json(['mensagem' => 'Estoque atualizado com sucesso!'], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['erro' => 'Erro interno ao atualizar estoque.'], 500);
        }
    }
}
