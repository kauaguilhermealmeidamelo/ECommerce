<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProdutoImagem extends Model
{
    protected $table = 'produto_imagens';

    protected $fillable = ['produto_id', 'caminho', 'ordem'];

    // "url" não existe como coluna — é montada a partir do caminho salvo
    // no disco, então sempre reflete a config atual (local, S3, etc.)
    protected $appends = ['url'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->caminho);
    }
}