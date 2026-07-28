<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Illuminate\Http\Request;

class NotificacaoController extends Controller
{
    public function index()
    {
        $notificacoes = Notificacao::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('notificacoes.index', compact('notificacoes'));
    }

    public function marcarLida($id)
    {
        $notificacao = Notificacao::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notificacao->update(['lida' => true]);

        if ($notificacao->link) {
            return redirect($notificacao->link);
        }

        return back()->with('success', 'Notificação marcada como lida.');
    }

    public function marcarTodasLidas()
    {
        Notificacao::where('user_id', auth()->id())
            ->where('lida', false)
            ->update(['lida' => true]);

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
