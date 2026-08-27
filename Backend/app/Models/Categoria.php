<?php

namespace App\Models;

// STUB — substitua pelo seu model real se já existir no projeto principal.

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nome', 'slug', 'categoria_pai_id'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function pai()
    {
        return $this->belongsTo(Categoria::class, 'categoria_pai_id');
    }

    public function filhas()
    {
        return $this->hasMany(Categoria::class, 'categoria_pai_id')->orderBy('nome');
    }

    /**
     * Carrega recursivamente todos os níveis abaixo — usado pra montar
     * a árvore inteira do menu numa única chamada, sem N+1 queries.
     */
    public function filhasRecursivas()
    {
        return $this->filhas()->with('filhasRecursivas');
    }

    /**
     * Verdadeiro se essa categoria não tem filhas — ou seja, é o nível
     * onde produtos podem de fato ser cadastrados.
     */
    public function getEhFolhaAttribute(): bool
    {
        return $this->filhas()->doesntExist();
    }
}