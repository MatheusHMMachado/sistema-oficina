<?php

namespace App\Services;

use App\Models\ItemOrdemServico;
use App\Models\OrdemServico;
use App\Models\ServicoPeca;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EstoqueService
{
    /**
     * Valida se há quantidade disponível para incluir a peça na O.S.
     */
    public static function validarDisponibilidade(ServicoPeca $peca, int $quantidade): void
    {
        if ($peca->tipo !== 'peca') {
            return;
        }

        $disponivel = (int) $peca->quantidade_estoque;

        if ($quantidade > $disponivel) {
            throw new RuntimeException(
                "Quantidade indisponível em estoque (Disponível: {$disponivel} un)."
            );
        }
    }

    /**
     * Baixa estoque de todos os itens de peça ainda não baixados da O.S.
     */
    public static function baixarPorOrdem(OrdemServico $ordem): void
    {
        DB::transaction(function () use ($ordem) {
            $itens = ItemOrdemServico::with('servicoPeca')
                ->where('ordem_servico_id', $ordem->id)
                ->where('estoque_baixado', false)
                ->lockForUpdate()
                ->get();

            foreach ($itens as $item) {
                self::baixarItem($item);
            }
        });
    }

    public static function baixarItem(ItemOrdemServico $item): void
    {
        $peca = $item->servicoPeca;

        if (! $peca || $peca->tipo !== 'peca' || $item->estoque_baixado) {
            return;
        }

        $peca = ServicoPeca::whereKey($peca->id)->lockForUpdate()->first();

        if ((int) $peca->quantidade_estoque < (int) $item->quantidade) {
            throw new RuntimeException(
                "Quantidade indisponível em estoque para \"{$peca->descricao}\" (Disponível: {$peca->quantidade_estoque} un)."
            );
        }

        $peca->decrement('quantidade_estoque', (int) $item->quantidade);
        $item->update(['estoque_baixado' => true]);

        if ($peca->fresh()->estoqueBaixo()) {
            NotificacaoService::notificarAdmins(
                'estoque_baixo',
                'Estoque baixo',
                "A peça \"{$peca->descricao}\" atingiu o estoque mínimo ({$peca->quantidade_estoque} un).",
                route('servicos-pecas.index', ['tipo' => 'peca']),
                $peca
            );
        }
    }

    public static function restaurarItem(ItemOrdemServico $item): void
    {
        $peca = $item->servicoPeca;

        if (! $peca || $peca->tipo !== 'peca' || ! $item->estoque_baixado) {
            return;
        }

        DB::transaction(function () use ($item, $peca) {
            $pecaLock = ServicoPeca::whereKey($peca->id)->lockForUpdate()->first();
            $pecaLock->increment('quantidade_estoque', (int) $item->quantidade);
            $item->update(['estoque_baixado' => false]);
        });
    }

    public static function restaurarPorOrdem(OrdemServico $ordem): void
    {
        $itens = ItemOrdemServico::with('servicoPeca')
            ->where('ordem_servico_id', $ordem->id)
            ->where('estoque_baixado', true)
            ->get();

        foreach ($itens as $item) {
            self::restaurarItem($item);
        }
    }
}
