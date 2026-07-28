<?php

namespace App\Services;

use App\Models\AtribuicaoServico;
use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificacaoService
{
    public static function criar(
        User|int $user,
        string $tipo,
        string $titulo,
        string $mensagem,
        ?string $link = null,
        ?Model $relacionado = null
    ): Notificacao {
        $userId = $user instanceof User ? $user->id : $user;

        return Notificacao::create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link,
            'lida' => false,
            'relacionado_type' => $relacionado ? $relacionado->getMorphClass() : null,
            'relacionado_id' => $relacionado?->getKey(),
        ]);
    }

    public static function notificarAdmins(
        string $tipo,
        string $titulo,
        string $mensagem,
        ?string $link = null,
        ?Model $relacionado = null
    ): void {
        User::where('ativo', true)
            ->where(function ($q) {
                $q->where('role', 'admin')->orWhere('id', 1);
            })
            ->get()
            ->each(fn (User $admin) => self::criar($admin, $tipo, $titulo, $mensagem, $link, $relacionado));
    }

    public static function alertaPrazoExcedido(AtribuicaoServico $atribuicao): void
    {
        $link = route('atribuicoes.index');
        $titulo = 'Prazo excedido';
        $mensagem = "A tarefa \"{$atribuicao->titulo}\" ultrapassou o prazo SLA.";

        self::criar(
            $atribuicao->mecanico_id,
            'prazo_excedido',
            $titulo,
            $mensagem,
            $link,
            $atribuicao
        );

        self::notificarAdmins('prazo_excedido', $titulo, $mensagem, $link, $atribuicao);
    }
}
