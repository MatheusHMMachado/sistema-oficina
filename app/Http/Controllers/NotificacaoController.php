<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacaoController extends Controller
{
    /**
     * Lista todas as notificações do usuário logado
     */
    public function index()
    {
        // Usando a tabela de notificações do Laravel (se existir)
        // Ou criando uma estrutura simples baseada em sessão/cache
        
        $notificacoes = [];
        
        // Se você tiver uma tabela de notificações customizada, use:
        // $notificacoes = Notificacao::where('user_id', auth()->id())
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(20);
        
        // Por enquanto, vamos usar uma estrutura simples
        // Você pode criar uma migration para criar uma tabela de notificações
        
        return view('notificacoes.index', compact('notificacoes'));
    }

    /**
     * Marca uma notificação como lida
     */
    public function marcarLida($id)
    {
        // Se você tiver uma tabela de notificações:
        // $notificacao = Notificacao::where('id', $id)
        //     ->where('user_id', auth()->id())
        //     ->firstOrFail();
        // 
        // $notificacao->update(['lida' => true]);
        
        // Por enquanto, apenas retornamos sucesso
        return back()->with('success', 'Notificação marcada como lida!');
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function marcarTodasLidas()
    {
        // Se você tiver uma tabela de notificações:
        // Notificacao::where('user_id', auth()->id())
        //     ->where('lida', false)
        //     ->update(['lida' => true]);
        
        return back()->with('success', 'Todas as notificações foram marcadas como lidas!');
    }
}

