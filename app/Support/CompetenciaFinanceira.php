<?php

namespace App\Support;

use App\Models\Despesa;
use App\Models\OrdemServico;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CompetenciaFinanceira
{
    public static function periodo(?string $mesYmd = null): array
    {
        $mesRef = $mesYmd ?: now()->format('Y-m');
        $inicio = Carbon::createFromFormat('Y-m', $mesRef)->startOfMonth();
        $fim = (clone $inicio)->endOfMonth();

        return compact('mesRef', 'inicio', 'fim');
    }

    /**
     * OS pagas na competência do mês (não apaga histórico — só filtra).
     */
    public static function queryOrdensPagasNoMes(Carbon $inicio, Carbon $fim): Builder
    {
        return OrdemServico::query()
            ->where('status', '!=', 'cancelada')
            ->where('status_pagamento', 'pago')
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('updated_at', [$inicio, $fim])
                    ->orWhereBetween('data_saida', [$inicio->toDateString(), $fim->toDateString()]);
            });
    }

    public static function faturamentoMes(Carbon $inicio, Carbon $fim): float
    {
        return (float) self::queryOrdensPagasNoMes($inicio, $fim)->sum('valor_total');
    }

    public static function queryDespesasNoMes(Carbon $inicio, Carbon $fim): Builder
    {
        return Despesa::query()
            ->whereBetween('data_despesa', [$inicio->toDateString(), $fim->toDateString()]);
    }

    public static function totalDespesasMes(Carbon $inicio, Carbon $fim): float
    {
        return (float) self::queryDespesasNoMes($inicio, $fim)->sum('valor');
    }
}
