<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoImagemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'image' já valida extensão E conteúdo real do arquivo (não confia só na extensão
            // do nome — um .php renomeado pra .jpg não passa nessa validação).
            'imagem' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'ordem' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
