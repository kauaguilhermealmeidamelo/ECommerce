<?php
namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:2', 'max:255'],
            'preco' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'estoque' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }
}