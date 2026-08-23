<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnderecoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controle real de dono feito via auth:sanctum + escopo no controller
    }

    public function rules(): array
    {
        return [
            'apelido' => ['nullable', 'string', 'max:60'],
            'cep' => ['required', 'string', 'max:9'],
            'logradouro' => ['required', 'string', 'max:150'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['required', 'string', 'max:100'],
            'cidade' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'size:2'],
            'padrao' => ['boolean'],
        ];
    }
}
