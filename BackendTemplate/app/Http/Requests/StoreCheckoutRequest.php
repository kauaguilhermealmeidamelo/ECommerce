<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'endereco_id' => ['nullable', 'exists:enderecos,id'],
            'cupom_codigo' => ['nullable', 'string', 'exists:cupons,codigo'],
        ];
    }
}
