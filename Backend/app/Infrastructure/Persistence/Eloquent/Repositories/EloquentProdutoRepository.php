<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;
use App\Domain\Produto\Entities\Produto;
use App\Infrastructure\Persistence\Eloquent\Models\ProdutoModel;
use App\Models\ProdutoImagem;
use App\Models\ProdutoVariacao;
use App\Models\Categoria;
use App\Enums\StatusPedido;
use Illuminate\Support\Facades\DB;

class EloquentProdutoRepository implements ProdutoRepositoryInterface
{
    public function obterPorId(int $id): ?Produto
    {
        $model = ProdutoModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function listarTodos(?int $categoriaId = null): array
    {
        return ProdutoModel::query()
            ->when($categoriaId !== null, fn ($query) => $query->where('categoria_id', $categoriaId))
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function listarMaisVendidos(int $limite = 8): array
    {
        $statusValidos = array_map(
            fn (StatusPedido $status) => $status->value,
            StatusPedido::statusDeVendaValida()
        );

        $ids = DB::table('itens_pedido')
            ->join('pedidos', 'pedidos.id', '=', 'itens_pedido.pedido_id')
            ->whereIn('pedidos.status', $statusValidos)
            ->select('itens_pedido.produto_id')
            ->selectRaw('SUM(itens_pedido.quantidade) as quantidade_vendida')
            ->groupBy('itens_pedido.produto_id')
            ->orderByDesc('quantidade_vendida')
            ->limit($limite)
            ->pluck('produto_id')
            ->all();

        if (!$ids) return [];

        $produtos = ProdutoModel::whereIn('id', $ids)->get()->keyBy('id');

        return array_values(array_filter(array_map(
            fn (int $id) => isset($produtos[$id]) ? $this->toEntity($produtos[$id]) : null,
            $ids
        )));
    }

    public function listarAtivos(): array
    {
        return ProdutoModel::where('ativo', true)
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function salvar(Produto $produto): Produto
    {
        $model = ProdutoModel::updateOrCreate(
            ['id' => $produto->id ?? null],
            [
                'nome' => $produto->nome,
                'preco' => $produto->preco,
                'preco_custo' => $produto->precoCusto ?? 0,
                'estoque' => $produto->estoque,
                'categoria_id' => $produto->categoriaId,
                'peso_gramas' => $produto->peso !== null ? (int) round($produto->peso * 1000) : null,
                'altura_cm' => $produto->altura !== null ? (int) round($produto->altura) : null,
                'largura_cm' => $produto->largura !== null ? (int) round($produto->largura) : null,
                'comprimento_cm' => $produto->comprimento !== null ? (int) round($produto->comprimento) : null,
                'descricao' => $produto->descricao ?? null,
                'ativo' => $produto->ativo ?? true,
            ]
        );

        return $this->toEntity($model);
    }

    public function deletar(int $id): bool
    {
        return ProdutoModel::destroy($id) > 0;
    }

    private function toEntity(ProdutoModel $model): Produto
    {
        return new Produto(
            id: $model->id,
            nome: $model->nome,
            preco: (float) $model->preco,
            estoque: (int) $model->estoque,
            categoriaId: $model->categoria_id,
            categoria: Categoria::find($model->categoria_id)?->nome ?? 'Geral',
            precoCusto: $model->preco_custo !== null ? (float) $model->preco_custo : null,
            peso: $model->peso_gramas !== null ? (float) $model->peso_gramas / 1000 : null,
            altura: $model->altura_cm !== null ? (float) $model->altura_cm : null,
            largura: $model->largura_cm !== null ? (float) $model->largura_cm : null,
            comprimento: $model->comprimento_cm !== null ? (float) $model->comprimento_cm : null,
            sku: $model->sku ?? null,
            descricao: $model->descricao ?? null,
            variacoes: ProdutoVariacao::where('produto_id', $model->id)
                ->orderBy('id')
                ->get()
                ->map(fn (ProdutoVariacao $variacao) => [
                    'id' => $variacao->id,
                    'nome' => $variacao->tamanho,
                    'estoque' => (int) $variacao->estoque,
                    'preco' => null,
                ])
                ->all(),
            imagens: ProdutoImagem::where('produto_id', $model->id)
                ->orderBy('ordem')
                ->get()
                ->map(fn (ProdutoImagem $imagem) => [
                    'id' => $imagem->id,
                    'url' => $imagem->url,
                ])
                ->all()
        );
    }
}
