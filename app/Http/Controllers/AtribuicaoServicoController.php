<?php

namespace App\Http\Controllers;

use App\Models\AtribuicaoServico;
use App\Models\OrdemServico;
use App\Models\ServicoPeca;
use App\Models\User;
use App\Services\AtribuicaoSlaService;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;

class AtribuicaoServicoController extends Controller
{
    public function index()
    {
        AtribuicaoSlaService::processarExcedidos();

        $isAdmin = auth()->user()->isAdmin();

        $query = AtribuicaoServico::with([
            'ordemServico.cliente',
            'ordemServico.veiculo',
            'servicoPeca',
            'mecanico',
            'atribuidoPor',
        ])->latest();

        if (! $isAdmin) {
            // Isolamento total: mecânico só vê as próprias tarefas
            $query->where('mecanico_id', auth()->id());
        }

        $todas = $query->get();

        // Segurança extra: nunca misturar cards de outros mecânicos
        if (! $isAdmin) {
            $todas = $todas->filter(fn ($a) => (int) $a->mecanico_id === (int) auth()->id())->values();
        }

        $colunas = [
            'pendente' => [],
            'em_andamento' => [],
            'paralisado' => [],
            'excedido' => [],
            'concluido' => [],
        ];

        foreach ($todas as $item) {
            $status = array_key_exists($item->status, $colunas) ? $item->status : 'pendente';
            $colunas[$status][] = $item;
        }

        $alertasPrazo = $todas->filter(fn ($a) => in_array($a->status, ['excedido'], true) || $a->temReagendamentoPendente());
        $reagendamentosPendentes = $todas->filter(fn ($a) => $a->temReagendamentoPendente());

        $mecanicos = collect();
        $ordens = collect();
        $servicos = collect();

        if ($isAdmin) {
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
        }

        return view('atribuicoes.index', compact(
            'colunas',
            'isAdmin',
            'mecanicos',
            'ordens',
            'servicos',
            'alertasPrazo',
            'reagendamentosPendentes'
        ));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
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

        $atribuicao = AtribuicaoServico::create([
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

        NotificacaoService::criar(
            (int) $request->mecanico_id,
            'nova_atribuicao',
            'Nova tarefa atribuída',
            "Você recebeu a tarefa \"{$atribuicao->titulo}\" com prazo até " . $atribuicao->prazo->format('d/m/Y H:i') . '.',
            route('atribuicoes.index'),
            $atribuicao
        );

        return back()->with('success', 'Tarefa atribuída ao mecânico com sucesso.');
    }

    public function atualizarProgresso(Request $request, AtribuicaoServico $atribuicao)
    {
        $isAdmin = auth()->user()->isAdmin();
        $isOwner = (int) $atribuicao->mecanico_id === (int) auth()->id();

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'Você só pode atualizar as suas próprias atribuições.');
        }

        $request->validate([
            'status' => 'required|in:pendente,em_andamento,paralisado,concluido,excedido',
            'observacao_mecanico' => 'nullable|string',
            'motivo_nao_conclusao' => 'nullable|string',
            'novo_prazo' => 'nullable|date',
            'solicitar_reagendamento' => 'nullable|boolean',
        ]);

        $status = $request->status;

        // Mecânico não pode forçar "excedido" manualmente — só o sistema
        if (! $isAdmin && $status === 'excedido') {
            $status = $atribuicao->status === 'excedido' ? 'excedido' : 'em_andamento';
        }

        $solicitarReagendamento = $request->boolean('solicitar_reagendamento')
            || $status === 'excedido'
            || ($atribuicao->isExcedido() && $request->filled('novo_prazo'));

        if ($solicitarReagendamento && ! $isAdmin) {
            $request->validate([
                'motivo_nao_conclusao' => 'required|string|min:5',
                'novo_prazo' => 'required|date|after:now',
            ], [
                'motivo_nao_conclusao.required' => 'A justificativa é obrigatória para solicitar prorrogação.',
                'novo_prazo.required' => 'Informe o novo prazo desejado.',
                'novo_prazo.after' => 'O novo prazo deve ser futuro.',
            ]);
        }

        $data = [
            'status' => $status === 'concluido' ? 'concluido' : $status,
            'progresso' => $status === 'concluido' ? 100 : ($status === 'pendente' ? 0 : max(10, (int) $atribuicao->progresso)),
            'observacao_mecanico' => $request->observacao_mecanico,
            'concluido_em' => $status === 'concluido' ? now() : null,
        ];

        if ($solicitarReagendamento && ! $isAdmin) {
            $data['motivo_nao_conclusao'] = $request->motivo_nao_conclusao;
            $data['novo_prazo'] = $request->novo_prazo;
            $data['reagendamento_status'] = 'pendente';
            $data['status'] = $atribuicao->prazo->isPast() ? 'excedido' : $status;

            NotificacaoService::notificarAdmins(
                'reagendamento',
                'Solicitação de prorrogação',
                "O mecânico {$atribuicao->mecanico->name} solicitou novo prazo para \"{$atribuicao->titulo}\". Justificativa: {$request->motivo_nao_conclusao}",
                route('atribuicoes.index'),
                $atribuicao
            );
        }

        $atribuicao->update($data);

        return back()->with('success', 'Status da tarefa atualizado com sucesso.');
    }

    public function aprovarReagendamento(Request $request, AtribuicaoServico $atribuicao)
    {
        if (! auth()->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem aprovar prorrogações.');
        }

        $request->validate([
            'novo_prazo' => 'required|date|after:now',
        ]);

        $atribuicao->update([
            'prazo' => $request->novo_prazo,
            'novo_prazo' => $request->novo_prazo,
            'reagendamento_status' => 'aprovado',
            'reagendamento_respondido_em' => now(),
            'status' => 'em_andamento',
            'alerta_prazo_em' => null,
        ]);

        NotificacaoService::criar(
            $atribuicao->mecanico_id,
            'reagendamento_aprovado',
            'Prazo prorrogado',
            "A prorrogação da tarefa \"{$atribuicao->titulo}\" foi aprovada. Novo prazo: " . $atribuicao->prazo->format('d/m/Y H:i'),
            route('atribuicoes.index'),
            $atribuicao
        );

        return back()->with('success', 'Prorrogação aprovada e prazo atualizado.');
    }

    public function recusarReagendamento(AtribuicaoServico $atribuicao)
    {
        if (! auth()->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem recusar prorrogações.');
        }

        $atribuicao->update([
            'reagendamento_status' => 'recusado',
            'reagendamento_respondido_em' => now(),
        ]);

        NotificacaoService::criar(
            $atribuicao->mecanico_id,
            'reagendamento_recusado',
            'Prorrogação recusada',
            "A solicitação de prorrogação da tarefa \"{$atribuicao->titulo}\" foi recusada.",
            route('atribuicoes.index'),
            $atribuicao
        );

        return back()->with('success', 'Solicitação de prorrogação recusada.');
    }

    public function destroy(AtribuicaoServico $atribuicao)
    {
        if (! auth()->user()->isAdmin()) {
            return back()->with('error', 'Apenas administradores podem excluir atribuições.');
        }

        $titulo = $atribuicao->titulo;
        $atribuicao->delete();

        return back()->with('success', "Atribuição \"{$titulo}\" excluída com sucesso.");
    }
}
