<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $fillable = ['pagina', 'sessao_id', 'ip_hash'];
}
