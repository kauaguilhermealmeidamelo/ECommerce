<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteProvedor extends Model
{
    protected $table = 'clientes_provedores';

    protected $fillable = ['cliente_id', 'provedor', 'provedor_id'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
