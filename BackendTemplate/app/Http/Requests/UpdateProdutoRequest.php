<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['sometimes', 'required', 'numeric', 'min:0'],
            'preco_promocional' => ['nullable', 'numeric', 'min:0', 'lt:preco'],
            'condicao' => ['sometimes', 'required', 'in:novo,seminovo,usado'],
            'estoque' => ['sometimes', 'required', 'integer', 'min:0'],
            'ativo' => ['boolean'],
            'destaque' => ['boolean'],
        ];
    }
}
