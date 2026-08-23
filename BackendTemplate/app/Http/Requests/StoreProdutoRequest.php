<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controle de acesso feito pelo middleware auth:sanctum,admin na rota
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'nome' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['required', 'numeric', 'min:0'],
            'preco_promocional' => ['nullable', 'numeric', 'min:0', 'lt:preco'],
            'condicao' => ['required', 'in:novo,seminovo,usado'],
            'estoque' => ['required', 'integer', 'min:0'],
            'ativo' => ['boolean'],
            'destaque' => ['boolean'],
        ];
    }

    // Gera o slug automaticamente a partir do nome antes de validar/salvar.
    protected function prepareForValidation(): void
    {
        if ($this->nome && ! $this->slug) {
            $this->merge(['slug' => Str::slug($this->nome).'-'.Str::random(5)]);
        }
    }
}
