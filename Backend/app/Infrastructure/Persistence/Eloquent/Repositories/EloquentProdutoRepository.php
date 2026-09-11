<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Produtos\Entities\Produto;
use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Produto as ProdutoModel;

class EloquentProdutoRepository implements ProdutoRepositoryInterface
{
    public function obterPorId(int $id): ?Produto
    {
        $model = ProdutoModel::with(['variacoes', 'imagens'])->find($id);

        if (!$model) {
            return null;
        }

        return $this->mapearParaDominio($model);
    }

    public function listarTodos(): array
    {
        $models = ProdutoModel::with(['variacoes', 'imagens'])->get();

        return $models->map(fn($model) => $this->mapearParaDominio($model))->toArray();
    }

    public function salvar(Produto $produto): Produto
    {
        // Salva ou atualiza o produto principal
        $model = ProdutoModel::updateOrCreate(
            ['id' => $produto->id],
            [
                'nome' => $produto->nome,
                'descricao' => $produto->descricao,
                'preco' => $produto->preco,
                'preco_custo' => $produto->precoCusto,
                'estoque' => $produto->estoque,
                'ativo' => $produto->ativo,
                'categoria_id' => $produto->categoriaId,
                'peso' => $produto->peso,
                'altura' => $produto->altura,
                'largura' => $produto->largura,
                'comprimento' => $produto->comprimento,
            ]
        );

        // Sincroniza as variações dinâmicas
        $model->variacoes()->delete();
        foreach ($produto->variacoes as $variacao) {
            $model->variacoes()->create([
                'nome' => $variacao['nome'], // Nome/Atributo livre criado pelo lojista (ex: Tamanho, Voltagem, Sabor)
                'preco' => $variacao['preco'] ?? $produto->preco,
                'estoque' => $variacao['estoque'] ?? 0,
            ]);
        }

        $produto->id = $model->id;
        return $produto;
    }

    public function deletar(int $id): bool
    {
        return ProdutoModel::destroy($id) > 0;
    }

    private function mapearParaDominio(ProdutoModel $model): Produto
    {
        return new Produto(
            id: $model->id,
            nome: $model->nome,
            descricao: $model->descricao ?? '',
            preco: (float) $model->preco,
            precoCusto: $model->preco_custo ? (float) $model->preco_custo : null,
            estoque: (int) $model->estoque,
            ativo: (bool) $model->ativo,
            categoriaId: $model->categoria_id,
            peso: $model->peso ? (float) $model->peso : null,
            altura: $model->altura ? (float) $model->altura : null,
            largura: $model->largura ? (float) $model->largura : null,
            comprimento: $model->comprimento ? (float) $model->comprimento : null,
            variacoes: $model->variacoes->map(fn($v) => [
                'id' => $v->id,
                'nome' => $v->nome,
                'preco' => (float) $v->preco,
                'estoque' => (int) $v->estoque,
            ])->toArray(),
            imagens: $model->imagens->map(fn($i) => [
                'id' => $i->id,
                'url' => $i->url,
            ])->toArray()
        );
    }
}