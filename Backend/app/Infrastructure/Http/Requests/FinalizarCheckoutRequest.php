<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizarCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo_entrega' => ['required', 'in:retirada,local,transportadora'],

            // Só obrigatório se não for retirada
            'nome' => ['required_unless:metodo_entrega,retirada', 'string', 'max:255'],
            'cep' => ['required_unless:metodo_entrega,retirada', 'string', 'size:9'],
            'endereco' => ['required_unless:metodo_entrega,retirada', 'string', 'max:255'],
            'numero' => ['required_unless:metodo_entrega,retirada', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['required_unless:metodo_entrega,retirada', 'string', 'max:255'],
            'cidade' => ['required_unless:metodo_entrega,retirada', 'string', 'max:255'],
            'uf' => ['required_unless:metodo_entrega,retirada', 'string', 'size:2'],

            // Identifica qual cotação de transportadora o cliente escolheu
            // (label retornado por /checkout/frete) — o backend recota e
            // confere antes de aceitar, nunca confia no preço do frontend.
            'servico' => ['required_if:metodo_entrega,transportadora', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cep.size' => 'CEP deve estar no formato 00000-000.',
            'required_unless' => 'Esse campo é obrigatório pra entrega local ou transportadora.',
        ];
    }
}
