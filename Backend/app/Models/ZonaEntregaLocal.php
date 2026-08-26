<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaEntregaLocal extends Model
{
    protected $table = 'zonas_entrega_local';

    protected $fillable = ['cep_inicial', 'cep_final', 'valor', 'prazo_dias'];
}
