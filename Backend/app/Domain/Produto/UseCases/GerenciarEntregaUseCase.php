<?php

namespace App\Domain\Produto\UseCases;

use App\Models\ConfiguracaoEntrega;
use App\Models\InformacaoLoja;
use App\Models\Produto;
use App\Models\ZonaEntregaLocal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GerenciarEntregaUseCase
{
    public function obterConfiguracao(): array
    {
        $config = ConfiguracaoEntrega::first() ?? ConfiguracaoEntrega::create();

        return [
            'config' => [
                'retirada_ativa' => $config->retirada_ativa,
                'entrega_local_ativa' => $config->entrega_local_ativa,
                'transportadora_ativa' => $config->transportadora_ativa,
                'transportadora_conectada' => !is_null($config->token_melhor_envio),
            ],
            'zonas' => ZonaEntregaLocal::orderBy('cep_inicial')->get(),
        ];
    }

    public function salvarConfiguracao(array $config, array $zonas): void
    {
        $registro = ConfiguracaoEntrega::first() ?? new ConfiguracaoEntrega();

        $registro->retirada_ativa = $config['retirada_ativa'] ?? true;
        $registro->entrega_local_ativa = $config['entrega_local_ativa'] ?? false;
        $registro->transportadora_ativa = $config['transportadora_ativa'] ?? false;

        if (!empty($config['token_melhor_envio'])) {
            $registro->token_melhor_envio = Crypt::encryptString($config['token_melhor_envio']);
        }

        $registro->save();

        ZonaEntregaLocal::query()->delete();
        foreach ($zonas as $zona) {
            ZonaEntregaLocal::create([
                'cep_inicial' => $zona['cep_inicial'],
                'cep_final' => $zona['cep_final'],
                'valor' => $zona['valor'],
                'prazo_dias' => $zona['prazo_dias'] ?? 1,
            ]);
        }
    }

    public function opcoesParaCep(string $cep, ?int $produtoId = null, int $quantidade = 1): array
    {
        $config = ConfiguracaoEntrega::first() ?? ConfiguracaoEntrega::create();
        $opcoes = [];

        if ($config->retirada_ativa) {
            $opcoes[] = ['metodo' => 'retirada', 'label' => 'Retirar na loja', 'valor' => 0.0, 'prazo_dias' => 0];
        }

        if ($config->entrega_local_ativa) {
            $zona = $this->calcularFreteLocal($cep);
            if ($zona) {
                $opcoes[] = [
                    'metodo' => 'local',
                    'label' => 'Entrega local (motoboy)',
                    'valor' => $zona['valor'],
                    'prazo_dias' => $zona['prazo_dias'],
                ];
            }
        }

        if ($config->transportadora_ativa && $config->token_melhor_envio && $produtoId) {
            $itens = [['produto_id' => $produtoId, 'quantidade' => $quantidade]];

            foreach ($this->cotarTransportadora($cep, $itens) as $cotacao) {
                $opcoes[] = [
                    'metodo' => 'transportadora',
                    'label' => $cotacao['servico'],
                    'valor' => $cotacao['preco'],
                    'prazo_dias' => $cotacao['prazo'],
                ];
            }
        }

        return $opcoes;
    }

    public function calcularFreteLocal(string $cep): ?array
    {
        $cepNumerico = preg_replace('/\D/', '', $cep);

        $zona = ZonaEntregaLocal::all()->first(function ($z) use ($cepNumerico) {
            $inicio = preg_replace('/\D/', '', $z->cep_inicial);
            $fim = preg_replace('/\D/', '', $z->cep_final);
            return $cepNumerico >= $inicio && $cepNumerico <= $fim;
        });

        if (!$zona) {
            return null;
        }

        return ['valor' => (float) $zona->valor, 'prazo_dias' => $zona->prazo_dias];
    }

    public function cotarTransportadora(string $cepDestino, array $itens): array
    {
        [$token, $cepOrigem] = $this->credenciaisMelhorEnvio();

        if (!$token || !$cepOrigem) {
            Log::warning('Cotação Melhor Envio abortada: token ou CEP de origem ausentes.');
            return [];
        }

        if (empty($itens)) {
            return [];
        }

        $produtos = Produto::whereIn('id', array_column($itens, 'produto_id'))->get()->keyBy('id');

        $products = collect($itens)->map(function ($item) use ($produtos) {
            $produto = $produtos->get($item['produto_id']);

            if (!$produto) {
                return null;
            }

            return [
                'id' => (string) $produto->id,
                'width' => $produto->largura_cm ?? 15,
                'height' => $produto->altura_cm ?? 5,
                'length' => $produto->comprimento_cm ?? 20,
                'weight' => $produto->peso_gramas ? round($produto->peso_gramas / 1000, 3) : 0.3,
                'insurance_value' => (float) $produto->preco,
                'quantity' => $item['quantidade'],
            ];
        })->filter()->values()->toArray();

        if (empty($products)) {
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->post(config('services.melhorenvio.url'), [
                    'from' => ['postal_code' => preg_replace('/\D/', '', $cepOrigem)],
                    'to' => ['postal_code' => preg_replace('/\D/', '', $cepDestino)],
                    'products' => $products,
                ]);

            if ($response->failed()) {
                Log::error('Erro na API da Melhor Envio: '.$response->body());
                return [];
            }

            $opcoes = [];

            foreach ($response->json() ?? [] as $cotacao) {
                if (isset($cotacao['error'])) {
                    continue;
                }

                $opcoes[] = [
                    'servico' => $cotacao['name'].' ('.$cotacao['company']['name'].')',
                    'preco' => (float) ($cotacao['custom_price'] ?? $cotacao['price']),
                    'prazo' => $cotacao['custom_delivery_time'] ?? $cotacao['delivery_time'] ?? null,
                    'tipo' => 'transportadora',
                ];
            }

            return $opcoes;
        } catch (\Throwable $e) {
            Log::error('Exceção ao cotar frete na Melhor Envio: '.$e->getMessage());
            return [];
        }
    }

    private function credenciaisMelhorEnvio(): array
    {
        $config = ConfiguracaoEntrega::first();

        $token = null;
        if ($config?->token_melhor_envio) {
            try {
                $token = Crypt::decryptString($config->token_melhor_envio);
            } catch (\Exception $e) {
                Log::error('Não foi possível descriptografar o token da Melhor Envio.');
            }
        }
        $token ??= config('services.melhorenvio.token');

        $cepOrigem = InformacaoLoja::first()?->cep ?? config('services.melhorenvio.cep_origem');

        return [$token, $cepOrigem];
    }
}