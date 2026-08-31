<?php

namespace App\Http\Controllers\Api;

// STUB — CRUD mínimo. Substitua pelo seu ProdutoController real (com
// Form Requests e API Resources, conforme já construído anteriormente).

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProdutoController extends Controller
{
    private const TAMANHOS_VALIDOS = ['PP', 'P', 'M', 'G', 'GG', 'XG'];
    private const MAX_IMAGENS = 8;

    /**
     * GET /api/produtos?categoria_id=&por_pagina=
     * categoria_id: opcional — usado pela vitrine pra montar cada seção
     * do catálogo (uma categoria por vez) sem trazer o catálogo inteiro.
     * por_pagina: opcional (padrão 20) — a seção de prévia do catálogo
     * pede poucos itens (ex: 8), a página "ver tudo" pede mais.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Produto::with(['categoria', 'variacoes', 'imagens']);

        if (!$request->user()?->is_admin) {
            $query->where('ativo', true);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', (int) $request->query('categoria_id'));
        }

        $porPagina = (int) $request->query('por_pagina', 20);

        return response()->json(['data' => $query->paginate($porPagina)->items()]);
    }

    /**
     * GET /api/produtos/achadinhos
     */
    public function achadinhos(): JsonResponse
    {
        $produtos = Produto::with(['categoria', 'variacoes', 'imagens'])
            ->where('ativo', true)
            ->recentes(7)
            ->latest()
            ->paginate(20)
            ->items();

        return response()->json(['data' => $produtos]);
    }

    public function show(Request $request, Produto $produto): JsonResponse
    {
        if (!$produto->ativo && !$request->user()?->is_admin) {
            abort(404);
        }

        $produto->load(['categoria', 'imagens']);

        // Público só vê tamanhos com estoque > 0. Admin vê todos, inclusive
        // esgotados, pra conseguir repor.
        $variacoes = $request->user()?->is_admin
            ? $produto->variacoes()->orderByRaw("FIELD(tamanho, 'PP','P','M','G','GG','XG')")->get()
            : $produto->variacoesDisponiveis()->orderByRaw("FIELD(tamanho, 'PP','P','M','G','GG','XG')")->get();

        $produto->setRelation('variacoes', $variacoes);

        return response()->json(['data' => $produto]);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $this->validarDados($request);
        $usaVariacao = $request->boolean('usa_variacao');

        $produto = DB::transaction(function () use ($dados, $usaVariacao, $request) {
            $produto = Produto::create($this->dadosProduto($dados));

            $this->sincronizarVariacoes($produto, $usaVariacao ? ($dados['variacoes'] ?? []) : []);

            if ($request->hasFile('imagens')) {
                $this->adicionarImagens($produto, $request->file('imagens'));
            }

            return $produto;
        });

        return response()->json(['data' => $produto->load(['variacoes', 'imagens'])], 201);
    }

    public function update(Request $request, Produto $produto): JsonResponse
    {
        $dados = $this->validarDados($request, atualizando: true);

        DB::transaction(function () use ($produto, $dados, $request) {
            $produto->update($this->dadosProduto($dados));

            // "usa_variacao" é enviado explicitamente pelo frontend sempre
            // que o form é salvo — evita a ambiguidade de "variacoes não
            // veio" (não mexer) vs "variacoes veio vazio de propósito
            // porque o lojista desligou a variação e quer limpar os
            // tamanhos salvos antes".
            if ($request->has('usa_variacao')) {
                $usaVariacao = $request->boolean('usa_variacao');
                $this->sincronizarVariacoes($produto, $usaVariacao ? ($dados['variacoes'] ?? []) : []);
            }

            if (!empty($dados['imagens_removidas'])) {
                $this->removerImagens($produto, $dados['imagens_removidas']);
            }

            if ($request->hasFile('imagens')) {
                $this->adicionarImagens($produto, $request->file('imagens'));
            }
        });

        return response()->json(['data' => $produto->fresh(['variacoes', 'imagens'])]);
    }

    public function destroy(Produto $produto): JsonResponse
    {
        foreach ($produto->imagens as $imagem) {
            Storage::disk('public')->delete($imagem->caminho);
        }

        $produto->delete();

        return response()->json(status: 204);
    }

    private function validarDados(Request $request, bool $atualizando = false): array
    {
        $obrigatorio = $atualizando ? 'sometimes' : 'required';

        return $request->validate([
            'nome' => [$obrigatorio, 'required', 'string', 'max:255'],
            'preco' => [$obrigatorio, 'required', 'numeric', 'min:0'],
            'categoria_id' => [$obrigatorio, 'required', Rule::exists('categorias', 'id'), $this->regraCategoriaFolha()],
            // Nunca obrigatório — só usado quando o produto NÃO tem
            // variação de tamanho (peça única de brechó, acessório etc).
            'estoque' => ['nullable', 'integer', 'min:0'],
            'descricao' => ['nullable', 'string'],

            'usa_variacao' => ['sometimes', 'boolean'],
            'variacoes' => ['sometimes', 'array'],
            'variacoes.*.tamanho' => ['required_with:variacoes', 'string', Rule::in(self::TAMANHOS_VALIDOS)],
            'variacoes.*.estoque' => ['required_with:variacoes', 'integer', 'min:0'],

            // Novas imagens enviadas nesse salvamento (upload real de arquivo).
            'imagens' => ['sometimes', 'array', 'max:'.self::MAX_IMAGENS],
            'imagens.*' => ['image', 'max:4096'], // até 4MB cada — ajuste se precisar de fotos maiores

            // IDs de imagens já existentes marcadas pra remover.
            'imagens_removidas' => ['sometimes', 'array'],
            'imagens_removidas.*' => ['integer', Rule::exists('produto_imagens', 'id')],
        ]);
    }

    /**
     * Remove os campos que não são colunas da tabela produtos — variacoes
     * vira produto_variacoes, imagens/imagens_removidas viram
     * produto_imagens, usa_variacao é só uma flag de controle.
     */
    private function dadosProduto(array $dados): array
    {
        unset($dados['variacoes'], $dados['imagens'], $dados['imagens_removidas'], $dados['usa_variacao']);

        return $dados;
    }

    private function sincronizarVariacoes(Produto $produto, array $variacoes): void
    {
        $produto->variacoes()->delete();

        foreach ($variacoes as $variacao) {
            $produto->variacoes()->create([
                'tamanho' => $variacao['tamanho'],
                'estoque' => $variacao['estoque'],
            ]);
        }
    }

    /**
     * Salva os arquivos no disco 'public' (storage/app/public/produtos) e
     * cria os registros em produto_imagens, continuando a ordem a partir
     * da última imagem já existente — novas imagens entram no fim do
     * carrossel, sem embaralhar a ordem atual.
     */
    private function adicionarImagens(Produto $produto, array $arquivos): void
    {
        $ordem = (int) $produto->imagens()->max('ordem') + 1;

        foreach ($arquivos as $arquivo) {
            $caminho = $arquivo->store('produtos', 'public');

            $produto->imagens()->create([
                'caminho' => $caminho,
                'ordem' => $ordem++,
            ]);
        }
    }

    private function removerImagens(Produto $produto, array $idsRemover): void
    {
        $imagens = $produto->imagens()->whereIn('id', $idsRemover)->get();

        foreach ($imagens as $imagem) {
            Storage::disk('public')->delete($imagem->caminho);
            $imagem->delete();
        }
    }

    private function regraCategoriaFolha(): \Closure
    {
        return function (string $atributo, mixed $valor, \Closure $fail) {
            $temFilhas = Categoria::where('categoria_pai_id', $valor)->exists();

            if ($temFilhas) {
                $fail('Escolha uma subcategoria mais específica — essa categoria possui subcategorias e não aceita produtos diretamente.');
            }
        };
    }
}
