<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Veiculo;
use App\Support\CompetenciaFinanceira;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        ['inicio' => $inicio, 'fim' => $fim] = CompetenciaFinanceira::periodo();

        $dados = [
            'is_admin' => $isAdmin,
            'total_clientes' => 0,
            'total_veiculos' => 0,
            'ordens_abertas' => 0,
            'ordens_em_andamento' => 0,
            'ordens_concluidas' => 0,
            'ordens_recentes' => collect(),
            'faturamento_mes' => 0,
        ];

        try {
            if ($isAdmin) {
                $dados['total_clientes'] = Cliente::count();
                $dados['total_veiculos'] = Veiculo::count();
            }

            $dados['ordens_abertas'] = OrdemServico::query()->visivelPara($user)->where('status', 'aberta')->count();
            $dados['ordens_em_andamento'] = OrdemServico::query()->visivelPara($user)->where('status', 'em_andamento')->count();
            $dados['ordens_concluidas'] = OrdemServico::query()->visivelPara($user)->where('status', 'concluida')->count();
            $dados['ordens_recentes'] = OrdemServico::query()->visivelPara($user)->with(['cliente', 'veiculo'])
                ->latest()
                ->limit(5)
                ->get();

            if ($isAdmin) {
                $dados['faturamento_mes'] = CompetenciaFinanceira::faturamentoMes($inicio, $fim);
            }
        } catch (\Throwable $e) {
            // Mantém defaults
        }

        return view('dashboard', compact('dados'));
    }
}
