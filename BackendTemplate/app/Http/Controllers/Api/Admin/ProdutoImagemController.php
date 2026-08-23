<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoImagemRequest;
use App\Models\Produto;
use Illuminate\Support\Facades\Storage;

class ProdutoImagemController extends Controller
{
    public function store(StoreProdutoImagemRequest $request, Produto $produto)
    {
        // Salva no disco "public" (link simbólico storage -> public/storage).
        // Em hospedagem compartilhada sem symlink disponível, trocar por disco customizado
        // apontando direto para uma pasta dentro de public/.
        $caminho = $request->file('imagem')->store("produtos/{$produto->id}", 'public');

        $imagem = $produto->imagens()->create([
            'url' => Storage::url($caminho),
            'ordem' => $request->ordem ?? $produto->imagens()->count(),
        ]);

        return response()->json($imagem, 201);
    }

    public function destroy(Produto $produto, int $imagemId)
    {
        $imagem = $produto->imagens()->findOrFail($imagemId);

        // Remove o arquivo físico além do registro no banco.
        $caminhoRelativo = str_replace('/storage/', '', $imagem->url);
        Storage::disk('public')->delete($caminhoRelativo);

        $imagem->delete();

        return response()->json(null, 204);
    }
}
