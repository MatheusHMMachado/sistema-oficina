<?php

namespace App\Http\Controllers;

use App\Models\AtribuicaoServico;
use App\Models\OrdemServico;
use App\Models\ServicoPeca;
use App\Models\User;
use Illuminate\Http\Request;

class AtribuicaoServicoController extends Controller
{
    public function index()
    {
        $isAdmin = auth()->user()->isAdmin();

        $query = AtribuicaoServico::with(['ordemServico.cliente', 'servicoPeca', 'mecanico', 'atribuidoPor'])
            ->orderByRaw("FIELD(status, 'pendente','em_andamento','nao_concluido','concluido')")
            ->latest();

        if (!$isAdmin) {
            $query->where('mecanico_id', auth()->id());
        }

        $atribuicoes = $query->paginate(20);

        $mecanicos = User::where('ativo', true)
            ->where('role', 'funcionario')
            ->orderBy('name')
            ->get();

        $ordens = OrdemServico::with(['cliente', 'veiculo'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $servicos = ServicoPeca::where('tipo', 'servico')->where('ativo', true)->orderBy('descricao')->get();

        return view('atribuicoes.index', compact('atribuicoes', 'isAdmin', 'mecanicos', 'ordens', 'servicos'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem criar atribuições.');
        }

        $request->validate([
            'ordem_servico_id' => 'required|exists:ordens_servico,id',
            'mecanico_id' => 'required|exists:users,id',
            'servico_peca_id' => 'nullable|exists:servicos_pecas,id',
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'prazo' => 'required|date',
        ]);

        AtribuicaoServico::create([
            'ordem_servico_id' => $request->ordem_servico_id,
            'mecanico_id' => $request->mecanico_id,
            'servico_peca_id' => $request->servico_peca_id,
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'prazo' => $request->prazo,
            'atribuido_por' => auth()->id(),
            'status' => 'pendente',
            'progresso' => 0,
        ]);

        return back()->with('success', 'Serviço atribuído ao mecânico com sucesso.');
    }

    public function atualizarProgresso(Request $request, AtribuicaoServico $atribuicao)
    {
        $isAdmin = auth()->user()->isAdmin();
        $isOwner = $atribuicao->mecanico_id === auth()->id();

        if (!$isAdmin && !$isOwner) {
            return back()->with('error', 'Você não tem permissão para atualizar esta atribuição.');
        }

        $request->validate([
            'status' => 'required|in:pendente,em_andamento,concluido,nao_concluido',
            'progresso' => 'required|integer|min:0|max:100',
            'observacao_mecanico' => 'nullable|string',
            'motivo_nao_conclusao' => 'nullable|string',
            'novo_prazo' => 'nullable|date',
        ]);

        if ($request->status === 'nao_concluido') {
            $request->validate([
                'motivo_nao_conclusao' => 'required|string',
                'novo_prazo' => 'required|date',
            ], [
                'motivo_nao_conclusao.required' => 'Informe o motivo de não conclusão.',
                'novo_prazo.required' => 'Informe o novo prazo.',
            ]);
        }

        $data = [
            'status' => $request->status,
            'progresso' => $request->status === 'concluido' ? 100 : $request->progresso,
            'observacao_mecanico' => $request->observacao_mecanico,
            'motivo_nao_conclusao' => $request->status === 'nao_concluido' ? $request->motivo_nao_conclusao : null,
            'novo_prazo' => $request->status === 'nao_concluido' ? $request->novo_prazo : null,
            'concluido_em' => $request->status === 'concluido' ? now() : null,
        ];

        $atribuicao->update($data);

        return back()->with('success', 'Progresso atualizado com sucesso.');
    }
}
