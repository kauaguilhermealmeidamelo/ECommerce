<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:clientes,email'],
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
