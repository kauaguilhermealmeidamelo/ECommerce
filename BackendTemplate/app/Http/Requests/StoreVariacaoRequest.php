<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tamanho' => ['nullable', 'string', 'max:20'],
            'cor' => ['nullable', 'string', 'max:40'],
            'sku' => ['required', 'string', 'unique:produto_variacoes,sku'],
            'estoque' => ['required', 'integer', 'min:0'],
        ];
    }
}
