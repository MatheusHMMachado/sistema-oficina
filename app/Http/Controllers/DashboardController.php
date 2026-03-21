<?php
// SUBSTITUA TODO O CONTEÚDO do arquivo: app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Versão simplificada para evitar erros
        $dados = [
            'total_clientes' => Cliente::count(),
            'total_veiculos' => Veiculo::count(),
            'ordens_abertas' => 0,
            'ordens_em_andamento' => 0,
            'ordens_concluidas' => 0,
            'ordens_recentes' => collect(), // Lista vazia por enquanto
            'faturamento_mes' => 0
        ];

        // Tentar buscar dados das ordens se a tabela existir
        try {
            $dados['ordens_abertas'] = OrdemServico::where('status', 'aberta')->count();
            $dados['ordens_em_andamento'] = OrdemServico::where('status', 'em_andamento')->count();
            $dados['ordens_concluidas'] = OrdemServico::where('status', 'concluida')->count();
            $dados['ordens_recentes'] = OrdemServico::with(['cliente', 'veiculo'])
                                                ->latest()
                                                ->limit(5)
                                                ->get();
            $dados['faturamento_mes'] = OrdemServico::where('status', 'concluida')
                                               ->whereMonth('created_at', now()->month)
                                               ->sum('valor_total');
        } catch (\Exception $e) {
            // Se der erro, manter os valores padrão
        }

        return view('dashboard', compact('dados'));
    }
}