<?php

namespace App\Infrastructure\Http\Controllers\Api;

use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ProdutoModel;
use App\Domain\Produto\UseCases\CriarProdutoUseCase;
use App\Domain\Produto\UseCases\ListarProdutosAtivosUseCase;
use App\Domain\Produto\UseCases\AtualizarEstoqueUseCase;
use App\Domain\Produto\UseCases\AtualizarProdutoUseCase;
use App\Domain\Produto\UseCases\ExcluirProdutoUseCase;
use App\Domain\Produto\UseCases\ObterProdutoUseCase;
use App\Domain\Produto\UseCases\ListarProdutosMaisVendidosUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\Storage;
use App\Models\ProdutoImagem;

class ProdutoController extends Controller
{
    public function __construct(
        private CriarProdutoUseCase $criarProdutoUseCase,
        private ListarProdutosAtivosUseCase $listarProdutosUseCase,
        private AtualizarEstoqueUseCase $atualizarEstoqueUseCase,
        private AtualizarProdutoUseCase $atualizarProdutoUseCase,
        private ExcluirProdutoUseCase $excluirProdutoUseCase,
        private ObterProdutoUseCase $obterProdutoUseCase,
        private ListarProdutosMaisVendidosUseCase $listarProdutosMaisVendidosUseCase
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $dados = $request->validate([
                'categoria_id' => 'nullable|integer|exists:categorias,id',
            ]);
            $produtos = $this->listarProdutosUseCase->executar($dados['categoria_id'] ?? null);
            return response()->json(['data' => $produtos], 200);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function maisVendidos(): JsonResponse
    {
        return response()->json([
            'data' => $this->listarProdutosMaisVendidosUseCase->executar(8),
        ]);
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
                'categoria_id' => 'required|integer|exists:categorias,id',
                'peso' => 'nullable|numeric|min:0',
                'altura' => 'nullable|numeric|min:0',
                'largura' => 'nullable|numeric|min:0',
                'comprimento' => 'nullable|numeric|min:0',
                'variacoes' => 'array',
                'variacoes.*.nome' => 'required|string',
                'variacoes.*.preco' => 'nullable|numeric|min:0',
                'variacoes.*.estoque' => 'required|integer|min:0',
                'imagens' => 'array',
                'imagens.*' => 'image|max:5120',
            ]);

            $produto = $this->criarProdutoUseCase->executar($dados);
            $this->salvarImagens($produto->id, $request);
            $produto = $this->obterProdutoUseCase->executar($produto->id);

            return response()->json([
                'mensagem' => 'Produto criado com sucesso!',
                'data' => $produto
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['erro' => 'Erro interno ao criar produto.'], 500);
        }
    }

    public function show(int $produto): JsonResponse
    {
        return response()->json(['data' => $this->obterProdutoUseCase->executar($produto)], 200);
    }

    public function update(Request $request, int $produto): JsonResponse
    {
        $dados = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'sometimes|required|numeric|min:0',
            'estoque' => 'sometimes|required|integer|min:0',
            'ativo' => 'sometimes|boolean',
            'categoria_id' => 'sometimes|nullable|integer',
            'peso' => 'sometimes|nullable|numeric|min:0',
            'altura' => 'sometimes|nullable|numeric|min:0',
            'largura' => 'sometimes|nullable|numeric|min:0',
            'comprimento' => 'sometimes|nullable|numeric|min:0',
            'imagens' => 'sometimes|array',
            'imagens.*' => 'image|max:5120',
            'imagens_removidas' => 'sometimes|array',
            'imagens_removidas.*' => 'integer|exists:produto_imagens,id',
        ]);

        $dadosProduto = [];
        foreach ([
            'nome' => 'nome',
            'descricao' => 'descricao',
            'preco' => 'preco',
            'estoque' => 'estoque',
            'ativo' => 'ativo',
            'categoria_id' => 'categoriaId',
            'peso' => 'peso',
            'altura' => 'altura',
            'largura' => 'largura',
            'comprimento' => 'comprimento',
        ] as $campoEntrada => $campoProduto) {
            if (array_key_exists($campoEntrada, $dados)) {
                $dadosProduto[$campoProduto] = $dados[$campoEntrada];
            }
        }

        $produtoAtualizado = $this->atualizarProdutoUseCase->executar($produto, $dadosProduto);
        $this->removerImagens($request);
        $this->salvarImagens($produto, $request);
        $produtoAtualizado = $this->obterProdutoUseCase->executar($produto);

        return response()->json(['data' => $produtoAtualizado], 200);
    }

    public function destroy(int $produto): JsonResponse
    {
        $this->excluirProdutoUseCase->executar($produto);

        return response()->json(status: 204);
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

    private function salvarImagens(int $produtoId, Request $request): void
    {
        foreach ($request->file('imagens', []) as $ordem => $arquivo) {
            ProdutoImagem::create([
                'produto_id' => $produtoId,
                'caminho' => $arquivo->store('produtos', 'public'),
                'ordem' => $ordem,
            ]);
        }
    }

    private function removerImagens(Request $request): void
    {
        foreach ($request->input('imagens_removidas', []) as $id) {
            $imagem = ProdutoImagem::find($id);
            if (!$imagem) continue;

            Storage::disk('public')->delete($imagem->caminho);
            $imagem->delete();
        }
    }
}
