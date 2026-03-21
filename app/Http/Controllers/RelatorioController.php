<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\ServicoPeca;
use App\Models\ItemOrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Página inicial de relatórios
     */
    public function index()
    {
        return view('relatorios.index');
    }

    /**
     * Relatório financeiro
     */
    public function financeiro(Request $request)
    {
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->format('Y-m-d'));

        $query = OrdemServico::where('status', 'concluida')
                            ->whereBetween('data_saida', [$dataInicio, $dataFim]);

        $totalFaturamento = $query->sum('valor_total');
        $totalOrdens = $query->count();
        $ticketMedio = $totalOrdens > 0 ? $totalFaturamento / $totalOrdens : 0;

        // Faturamento por mês
        $faturamentoPorMes = OrdemServico::where('status', 'concluida')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->select(
                DB::raw('DATE_FORMAT(data_saida, "%Y-%m") as mes'),
                DB::raw('SUM(valor_total) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Top serviços/peças mais vendidos
        $topItens = ItemOrdemServico::whereHas('ordemServico', function($q) use ($dataInicio, $dataFim) {
                $q->where('status', 'concluida')
                  ->whereBetween('data_saida', [$dataInicio, $dataFim]);
            })
            ->select('servico_peca_id', DB::raw('SUM(quantidade) as total_vendido'), DB::raw('SUM(valor_total) as total_faturado'))
            ->with('servicoPeca')
            ->groupBy('servico_peca_id')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        return view('relatorios.financeiro', compact(
            'totalFaturamento',
            'totalOrdens',
            'ticketMedio',
            'faturamentoPorMes',
            'topItens',
            'dataInicio',
            'dataFim'
        ));
    }

    /**
     * Relatório de serviços
     */
    public function servicos(Request $request)
    {
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->format('Y-m-d'));

        // Serviços mais realizados
        $servicosMaisRealizados = ItemOrdemServico::whereHas('servicoPeca', function($q) {
                $q->where('tipo', 'servico');
            })
            ->whereHas('ordemServico', function($q) use ($dataInicio, $dataFim) {
                $q->where('status', 'concluida')
                  ->whereBetween('data_saida', [$dataInicio, $dataFim]);
            })
            ->select('servico_peca_id', DB::raw('SUM(quantidade) as total'), DB::raw('SUM(valor_total) as faturado'))
            ->with('servicoPeca')
            ->groupBy('servico_peca_id')
            ->orderByDesc('total')
            ->get();

        // Peças mais vendidas
        $pecasMaisVendidas = ItemOrdemServico::whereHas('servicoPeca', function($q) {
                $q->where('tipo', 'peca');
            })
            ->whereHas('ordemServico', function($q) use ($dataInicio, $dataFim) {
                $q->where('status', 'concluida')
                  ->whereBetween('data_saida', [$dataInicio, $dataFim]);
            })
            ->select('servico_peca_id', DB::raw('SUM(quantidade) as total'), DB::raw('SUM(valor_total) as faturado'))
            ->with('servicoPeca')
            ->groupBy('servico_peca_id')
            ->orderByDesc('total')
            ->get();

        // Status das ordens
        $statusOrdens = OrdemServico::whereBetween('created_at', [$dataInicio, $dataFim])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return view('relatorios.servicos', compact(
            'servicosMaisRealizados',
            'pecasMaisVendidas',
            'statusOrdens',
            'dataInicio',
            'dataFim'
        ));
    }

    /**
     * Relatório de clientes
     */
    public function clientes(Request $request)
    {
        $dataInicio = $request->input('data_inicio', now()->startOfYear()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->format('Y-m-d'));

        // Top clientes por faturamento
        $topClientes = OrdemServico::where('status', 'concluida')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->select('cliente_id', DB::raw('SUM(valor_total) as total_faturado'), DB::raw('COUNT(*) as total_ordens'))
            ->with('cliente')
            ->groupBy('cliente_id')
            ->orderByDesc('total_faturado')
            ->limit(10)
            ->get();

        // Clientes mais frequentes
        $clientesFrequentes = OrdemServico::whereBetween('created_at', [$dataInicio, $dataFim])
            ->select('cliente_id', DB::raw('COUNT(*) as total_visitas'))
            ->with('cliente')
            ->groupBy('cliente_id')
            ->orderByDesc('total_visitas')
            ->limit(10)
            ->get();

        // Novos clientes no período
        $novosClientes = Cliente::whereBetween('created_at', [$dataInicio, $dataFim])
            ->count();

        // Total de clientes
        $totalClientes = Cliente::count();

        return view('relatorios.clientes', compact(
            'topClientes',
            'clientesFrequentes',
            'novosClientes',
            'totalClientes',
            'dataInicio',
            'dataFim'
        ));
    }
}

