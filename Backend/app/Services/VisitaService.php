<?php

namespace App\Services;

use App\Models\Visita;
use Illuminate\Support\Facades\DB;

class VisitaService
{
    /**
     * Chamado pelo storefront público a cada carregamento de página.
     * IP nunca é salvo em texto puro — só o hash, o suficiente pra
     * contar "visitantes únicos" sem guardar dado pessoal identificável.
     */
    public function registrar(string $pagina, string $sessaoId, string $ip): void
    {
        Visita::create([
            'pagina' => $pagina,
            'sessao_id' => $sessaoId,
            'ip_hash' => hash('sha256', $ip),
        ]);
    }

    public function resumo(): array
    {
        return [
            'total' => Visita::count(),
            'hoje' => Visita::whereDate('created_at', today())->count(),
            'visitantes_unicos_hoje' => Visita::whereDate('created_at', today())->distinct('sessao_id')->count('sessao_id'),
            'esta_semana' => Visita::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'este_mes' => Visita::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'ultimos_7_dias' => $this->ultimosDias(7),
        ];
    }

    private function ultimosDias(int $dias): array
    {
        $inicio = now()->subDays($dias - 1)->startOfDay();

        $porDia = Visita::where('created_at', '>=', $inicio)
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $serie = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $data = now()->subDays($i);
            $chave = $data->format('Y-m-d');

            $serie[] = [
                'data' => $data->format('d/m'),
                'total' => (int) ($porDia[$chave] ?? 0),
            ];
        }

        return $serie;
    }
}
