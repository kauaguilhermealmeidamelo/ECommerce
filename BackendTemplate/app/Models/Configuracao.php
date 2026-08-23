<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracao extends Model
{
    
    protected $table = 'configuracoes';
    
    protected $primaryKey = 'chave';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['chave', 'valor'];

    public static function obter(string $chave, $padrao = null)
    {
        return Cache::rememberForever("config:{$chave}", function () use ($chave, $padrao) {
            return static::find($chave)?->valor ?? $padrao;
        });
    }
}
