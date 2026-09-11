<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaModel extends Model
{
    protected $table = 'categorias';
    protected $fillable = ['nome', 'categoria_pai_id', 'ativo'];
}