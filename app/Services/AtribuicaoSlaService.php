<?php

namespace App\Services;

use App\Models\AtribuicaoServico;
use Illuminate\Support\Collection;

class AtribuicaoSlaService
{
    /**
     * Marca tarefas vencidas como "excedido" e dispara alertas (uma vez).
     */
    public static function processarExcedidos(): Collection
    {
        $excedidas = AtribuicaoServico::query()
            ->with(['mecanico', 'ordemServico'])
            ->whereNotIn('status', ['concluido', 'excedido'])
            ->where('prazo', '<', now())
            ->get();

        foreach ($excedidas as $atribuicao) {
            $jaAlertou = (bool) $atribuicao->alerta_prazo_em;

            $atribuicao->update([
                'status' => 'excedido',
                'alerta_prazo_em' => $atribuicao->alerta_prazo_em ?? now(),
            ]);

            if (! $jaAlertou) {
                NotificacaoService::alertaPrazoExcedido($atribuicao->fresh());
            }
        }

        $semAlerta = AtribuicaoServico::query()
            ->where('status', 'excedido')
            ->whereNull('alerta_prazo_em')
            ->get();

        foreach ($semAlerta as $atribuicao) {
            $atribuicao->update(['alerta_prazo_em' => now()]);
            NotificacaoService::alertaPrazoExcedido($atribuicao);
        }

        return $excedidas;
    }
}
