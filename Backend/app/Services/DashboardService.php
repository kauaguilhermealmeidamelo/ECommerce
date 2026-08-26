<?php

namespace App\Services;

use App\Enums\StatusPedido;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Status considerados "venda concretizada" pra fins de faturamento/lucro.
     * AJUSTE os cases abaixo para bater com os valores reais do seu enum StatusPedido.
     */
    private const STATUS_VALIDOS = [
        StatusPedido::Pago->value,
        StatusPedido::Concluido->value,
    ];

    /**
     * Payload principal consumido pela tela de dashboard.
     */
    public function resumoGeral(): array
    {
        return [
            'mes_atual' => $this->resumoPeriodo(now()->startOfMonth(), now()->endOfMonth()),
            'mes_anterior' => $this->resumoPeriodo(
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth()
            ),
            'serie_mensal' => $this->serieMensal(6),
            'categorias_mais_vendidas' => $this->categoriasMaisVendidas(90),
        ];
    }

    /**
     * Faturamento, custo, lucro e ticket médio num intervalo de datas.
     * Usa uma única query agregada (não carrega itens em coleção PHP).
     */
    private function resumoPeriodo(Carbon $inicio, Carbon $fim): array
    {
        $linha = $this->baseQuery($inicio, $fim)
            ->selectRaw('
                COALESCE(SUM(ip.quantidade * ip.preco_unitario), 0) as faturamento,
                COALESCE(SUM(ip.quantidade * COALESCE(pr.preco_custo, 0)), 0) as custo_total,
                COUNT(DISTINCT p.id) as pedidos
            ')
            ->first();

        $faturamento = (float) $linha->faturamento;
        $custo = (float) $linha->custo_total;
        $pedidos = (int) $linha->pedidos;

        return [
            'faturamento' => round($faturamento, 2),
            'lucro' => round($faturamento - $custo, 2),
            'pedidos' => $pedidos,
            'ticket_medio' => $pedidos > 0 ? round($faturamento / $pedidos, 2) : 0.0,
        ];
    }

    /**
     * Série dos últimos N meses (incluindo o atual), pra gráfico de linha.
     */
    private function serieMensal(int $meses): array
    {
        $serie = [];

        for ($i = $meses - 1; $i >= 0; $i--) {
            $referencia = now()->subMonthsNoOverflow($i);
            $resumo = $this->resumoPeriodo($referencia->copy()->startOfMonth(), $referencia->copy()->endOfMonth());

            $serie[] = array_merge(
                ['mes' => $referencia->translatedFormat('M/Y')],
                $resumo
            );
        }

        return $serie;
    }

    /**
     * Ranking de categorias por quantidade vendida num período (dias corridos).
     */
    public function categoriasMaisVendidas(int $dias = 90, int $limite = 10): array
    {
        $inicio = now()->subDays($dias)->startOfDay();
        $fim = now()->endOfDay();

        $linhas = $this->baseQuery($inicio, $fim)
            ->join('categorias as c', 'c.id', '=', 'pr.categoria_id')
            ->select('c.id', 'c.nome')
            ->selectRaw('SUM(ip.quantidade) as quantidade_vendida')
            ->selectRaw('SUM(ip.quantidade * ip.preco_unitario) as faturamento')
            ->groupBy('c.id', 'c.nome')
            ->orderByDesc('quantidade_vendida')
            ->limit($limite)
            ->get();

        $totalQuantidade = $linhas->sum('quantidade_vendida') ?: 1;

        return $linhas->map(fn ($linha) => [
            'categoria' => $linha->nome,
            'quantidade_vendida' => (int) $linha->quantidade_vendida,
            'faturamento' => round((float) $linha->faturamento, 2),
            'percentual' => round(($linha->quantidade_vendida / $totalQuantidade) * 100, 1),
        ])->toArray();
    }

    /**
     * Base compartilhada: itens de pedidos pagos/concluídos num período.
     * AJUSTE os nomes de tabela/coluna se divergirem do seu schema.
     */
    private function baseQuery(Carbon $inicio, Carbon $fim)
    {
        return DB::table('itens_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->join('produtos as pr', 'pr.id', '=', 'ip.produto_id')
            ->whereIn('p.status', self::STATUS_VALIDOS)
            ->whereBetween('p.created_at', [$inicio, $fim]);
    }
}
