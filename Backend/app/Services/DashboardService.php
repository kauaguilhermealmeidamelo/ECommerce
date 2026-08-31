<?php

namespace App\Services;

use App\Enums\StatusPedido;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Payload principal consumido pela tela de dashboard.
     */
    public function resumoGeral(): array
    {
        $inicioMesAtual = now()->startOfMonth();
        $fimMesAtual = now()->endOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $fimMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        return [
            'mes_atual' => [
                ...$this->resumoPeriodo($inicioMesAtual, $fimMesAtual),
                'novos_clientes' => $this->novosClientesPeriodo($inicioMesAtual, $fimMesAtual),
            ],
            'mes_anterior' => [
                ...$this->resumoPeriodo($inicioMesAnterior, $fimMesAnterior),
                'novos_clientes' => $this->novosClientesPeriodo($inicioMesAnterior, $fimMesAnterior),
            ],
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
     * Quantos usuários não-admin (ou seja, clientes — cadastrados pelo
     * próprio fluxo de login/registro do storefront público, nunca criados
     * manualmente pelo admin) se cadastraram num período. Alimenta o card
     * "Novos Clientes" do dashboard.
     */
    private function novosClientesPeriodo(Carbon $inicio, Carbon $fim): int
    {
        return User::where('is_admin', false)
            ->whereBetween('created_at', [$inicio, $fim])
            ->count();
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
     * Ranking de categorias por quantidade e faturamento vendidos num
     * período (dias corridos). Usado tanto na lista de "mais vendidas"
     * quanto no gráfico de vendas por categoria — ambos consomem o mesmo
     * payload, só mudando a métrica exibida (quantidade x faturamento).
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
            ->orderByDesc('faturamento')
            ->limit($limite)
            ->get();

        $totalFaturamento = $linhas->sum('faturamento') ?: 1;
        $totalQuantidade = $linhas->sum('quantidade_vendida') ?: 1;

        return $linhas->map(fn ($linha) => [
            'categoria' => $linha->nome,
            'quantidade_vendida' => (int) $linha->quantidade_vendida,
            'faturamento' => round((float) $linha->faturamento, 2),
            'percentual' => round(($linha->quantidade_vendida / $totalQuantidade) * 100, 1),
            'percentual_faturamento' => round(($linha->faturamento / $totalFaturamento) * 100, 1),
        ])->toArray();
    }

    /**
     * Base compartilhada: itens de pedidos pagos/concluídos num período.
     * Usa StatusPedido::statusDeVendaValida() — Recusado, Estornado,
     * Cancelado, EmAnalise nunca entram aqui, então um pedido que foi
     * pago e depois estornado some do faturamento automaticamente
     * (porque seu status muda de 'pago' pra 'estornado').
     */
    private function baseQuery(Carbon $inicio, Carbon $fim)
    {
        $statusValidos = array_map(fn ($status) => $status->value, StatusPedido::statusDeVendaValida());

        return DB::table('itens_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->join('produtos as pr', 'pr.id', '=', 'ip.produto_id')
            ->whereIn('p.status', $statusValidos)
            ->whereBetween('p.created_at', [$inicio, $fim]);
    }
}
