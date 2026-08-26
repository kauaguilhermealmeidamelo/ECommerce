<?php

namespace App\Models;

// STUB — substitua pelo seu model real se já existir no projeto principal.

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nome', 'slug'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}
