<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\OrdemServico;
use App\Support\CompetenciaFinanceira;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $mesRelatorio = $request->input('mes', now()->format('Y-m'));
        ['inicio' => $inicio, 'fim' => $fim, 'mesRef' => $mesRef] = CompetenciaFinanceira::periodo($mesRelatorio);
        $mesRelatorio = $mesRef;

        $ordensQuery = CompetenciaFinanceira::queryOrdensPagasNoMes($inicio, $fim)
            ->with('cliente')
            ->orderByDesc('updated_at');

        // Lista de entradas do mês (pagas) + pendentes do mês (criadas no mês) para gestão
        $ordens = OrdemServico::with('cliente')
            ->where('status', '!=', 'cancelada')
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('created_at', [$inicio, $fim])
                    ->orWhereBetween('updated_at', [$inicio, $fim])
                    ->orWhereBetween('data_saida', [$inicio->toDateString(), $fim->toDateString()]);
            })
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'ordens_page');

        $despesas = CompetenciaFinanceira::queryDespesasNoMes($inicio, $fim)
            ->with('usuario')
            ->orderByDesc('data_despesa')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'despesas_page');

        $valorBruto = CompetenciaFinanceira::faturamentoMes($inicio, $fim);
        $totalDespesas = CompetenciaFinanceira::totalDespesasMes($inicio, $fim);
        $valorLiquido = $valorBruto - $totalDespesas;

        $servicosPagos = (clone $ordensQuery)->count();

        $servicosPendentes = OrdemServico::query()
            ->where('status', '!=', 'cancelada')
            ->where('status_pagamento', 'pendente')
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('created_at', [$inicio, $fim])
                    ->orWhereBetween('updated_at', [$inicio, $fim]);
            })
            ->count();

        $categorias = Despesa::CATEGORIAS;

        $despesasPorCategoria = CompetenciaFinanceira::queryDespesasNoMes($inicio, $fim)
            ->selectRaw('categoria, SUM(valor) as total')
            ->groupBy('categoria')
            ->pluck('total', 'categoria');

        return view('financeiro.index', compact(
            'ordens',
            'despesas',
            'valorBruto',
            'totalDespesas',
            'valorLiquido',
            'servicosPagos',
            'servicosPendentes',
            'categorias',
            'despesasPorCategoria',
            'mesRelatorio'
        ));
    }

    public function storeDespesa(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'categoria' => 'required|in:' . implode(',', array_keys(Despesa::CATEGORIAS)),
            'data_despesa' => 'nullable|date',
        ], [
            'descricao.required' => 'Informe a descrição do gasto.',
            'valor.required' => 'Informe o valor da despesa.',
            'valor.min' => 'O valor deve ser maior que zero.',
            'categoria.required' => 'Selecione a categoria do gasto.',
            'categoria.in' => 'Categoria inválida.',
        ]);

        Despesa::create([
            'descricao' => $validated['descricao'],
            'valor' => $validated['valor'],
            'categoria' => $validated['categoria'],
            'data_despesa' => $validated['data_despesa'] ?? now()->toDateString(),
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('financeiro.index')
            ->with('success', 'Despesa cadastrada com sucesso. O saldo líquido foi atualizado.');
    }

    public function destroyDespesa(Despesa $despesa)
    {
        $despesa->delete();

        return redirect()
            ->route('financeiro.index')
            ->with('success', 'Despesa removida. O saldo líquido foi atualizado.');
    }

    public function atualizarPagamento(Request $request, OrdemServico $ordem)
    {
        $validated = $request->validate([
            'status_pagamento' => 'required|in:pago,pendente',
        ]);

        $ordem->update([
            'status_pagamento' => $validated['status_pagamento'],
        ]);

        $label = $validated['status_pagamento'] === 'pago' ? 'Pago' : 'Pendente';

        return redirect()
            ->route('financeiro.index')
            ->with('success', "Status de pagamento da OS #{$ordem->numero_ordem} atualizado para {$label}.");
    }

    public function relatorioMensalPdf(Request $request)
    {
        $request->validate([
            'mes' => 'nullable|date_format:Y-m',
        ]);

        ['inicio' => $inicio, 'fim' => $fim, 'mesRef' => $mesRef] = CompetenciaFinanceira::periodo(
            $request->input('mes')
        );

        $ordensPagas = CompetenciaFinanceira::queryOrdensPagasNoMes($inicio, $fim)
            ->with('cliente')
            ->orderBy('numero_ordem')
            ->get();

        $faturamento = (float) $ordensPagas->sum('valor_total');

        $despesasMes = CompetenciaFinanceira::queryDespesasNoMes($inicio, $fim)
            ->orderBy('data_despesa')
            ->get();

        $totalDespesas = (float) $despesasMes->sum('valor');
        $liquido = $faturamento - $totalDespesas;

        $despesasPorCategoria = $despesasMes
            ->groupBy('categoria')
            ->map(fn ($grupo) => (float) $grupo->sum('valor'));

        $pdf = Pdf::loadView('financeiro.relatorio-mensal-pdf', [
            'mesLabel' => $inicio->locale('pt_BR')->translatedFormat('F/Y'),
            'mesRef' => $mesRef,
            'inicio' => $inicio,
            'fim' => $fim,
            'ordensPagas' => $ordensPagas,
            'despesasMes' => $despesasMes,
            'faturamento' => $faturamento,
            'totalDespesas' => $totalDespesas,
            'liquido' => $liquido,
            'despesasPorCategoria' => $despesasPorCategoria,
            'categorias' => Despesa::CATEGORIAS,
            'geradoEm' => now(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('relatorio_financeiro_' . $inicio->format('Y_m') . '.pdf');
    }
}
