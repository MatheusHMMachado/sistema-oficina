<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\AtribuicaoServico;
use App\Models\Cliente;
use App\Models\ItemOrdemServico;
use App\Models\OrdemServico;
use App\Models\ServicoPeca;
use App\Models\StatusAtualizacao;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgendamentoController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $agendamentos = Agendamento::with(['cliente', 'veiculo', 'ordemServico'])
            ->latest()
            ->paginate(20);

        $pendentes = Agendamento::where('status', 'pendente')->count();

        return view('agendamentos.index', compact('agendamentos', 'pendentes'));
    }

    /**
     * Painel de ocupação dos mecânicos (tempo real) para o modal de aprovação.
     */
    public function cargaMecanicos()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $statusAtivos = ['pendente', 'em_andamento', 'paralisado', 'excedido'];
        $hoje = now()->toDateString();

        $mecanicos = User::query()
            ->where('ativo', true)
            ->where('role', 'funcionario')
            ->orderBy('name')
            ->get()
            ->map(function (User $mecanico) use ($statusAtivos, $hoje) {
                $tarefas = AtribuicaoServico::with(['ordemServico.veiculo', 'ordemServico.cliente'])
                    ->where('mecanico_id', $mecanico->id)
                    ->whereIn('status', $statusAtivos)
                    ->orderBy('prazo')
                    ->get();

                $noDia = $tarefas->filter(function (AtribuicaoServico $t) use ($hoje) {
                    return optional($t->prazo)->toDateString() === $hoje
                        || optional($t->created_at)->toDateString() === $hoje
                        || optional($t->ordemServico?->data_entrada)->toDateString() === $hoje;
                })->values();

                $ativas = $tarefas->count();
                $carga = $ativas <= 1 ? 'livre' : ($ativas <= 3 ? 'moderada' : 'maxima');

                $labels = [
                    'livre' => 'Livre',
                    'moderada' => 'Carga Moderada',
                    'maxima' => 'Carga Máxima',
                ];

                return [
                    'id' => $mecanico->id,
                    'nome' => $mecanico->name,
                    'carga' => $carga,
                    'carga_label' => $labels[$carga],
                    'ativas_hoje' => $noDia->count(),
                    'ativas_total' => $ativas,
                    'tarefas' => $tarefas->take(6)->map(function (AtribuicaoServico $t) {
                        $veiculo = $t->ordemServico?->veiculo;
                        $placa = $veiculo?->placa_formatada ?? '—';

                        return [
                            'titulo' => $t->titulo,
                            'os' => $t->ordemServico?->numero_ordem,
                            'placa' => $placa,
                            'status' => AtribuicaoServico::STATUS[$t->status] ?? $t->status,
                            'prazo' => optional($t->prazo)->format('d/m/Y H:i'),
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json(['mecanicos' => $mecanicos]);
    }

    public function atualizarStatus(Request $request, Agendamento $agendamento)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:aprovado,recusado,concluido',
            'resposta_admin' => 'nullable|string|max:500',
            'criar_cadastro' => 'nullable|boolean',
            'mecanico_id' => 'nullable|exists:users,id',
            'prazo' => 'nullable|date',
        ]);

        if ($request->status === 'aprovado') {
            $request->validate([
                'mecanico_id' => 'required|exists:users,id',
                'prazo' => 'required|date',
            ], [
                'mecanico_id.required' => 'Selecione o mecânico responsável.',
                'prazo.required' => 'Informe o prazo estimado (SLA).',
            ]);

            $mecanico = User::where('id', $request->mecanico_id)
                ->where('ativo', true)
                ->where('role', 'funcionario')
                ->first();

            if (! $mecanico) {
                throw ValidationException::withMessages([
                    'mecanico_id' => 'O mecânico selecionado não está disponível.',
                ]);
            }
        }

        if ($agendamento->status !== 'pendente' && $request->status === 'aprovado') {
            return back()->with('error', 'Este agendamento já foi processado.');
        }

        $mensagemExtra = '';
        $ordemCriada = null;

        DB::transaction(function () use ($request, $agendamento, &$mensagemExtra, &$ordemCriada) {
            if ($request->status === 'aprovado') {
                $this->garantirClienteVeiculo($agendamento, $mensagemExtra);

                if (! $agendamento->cliente_id || ! $agendamento->veiculo_id) {
                    throw ValidationException::withMessages([
                        'status' => 'Não foi possível aprovar: cliente e veículo são obrigatórios. Marque “cadastrar cliente/veículo” se for visitante.',
                    ]);
                }

                if ($agendamento->ordem_servico_id) {
                    throw ValidationException::withMessages([
                        'status' => 'Já existe uma O.S. vinculada a este agendamento.',
                    ]);
                }

                $ordemCriada = $this->criarOrdemDoAgendamento($agendamento);
                $this->criarItemServicoSeExistir($ordemCriada, $agendamento);
                $atribuicao = $this->criarAtribuicao(
                    $ordemCriada,
                    $agendamento,
                    (int) $request->mecanico_id,
                    $request->prazo
                );

                $agendamento->ordem_servico_id = $ordemCriada->id;
                $agendamento->status = 'aprovado';
                $agendamento->resposta_admin = $request->resposta_admin
                    ?: "Agendamento aprovado. O.S. {$ordemCriada->numero_ordem} gerada e atribuída.";
                $agendamento->respondido_por = auth()->id();
                $agendamento->respondido_em = now();
                $agendamento->save();

                StatusAtualizacao::create([
                    'ordem_servico_id' => $ordemCriada->id,
                    'user_id' => auth()->id(),
                    'status_anterior' => 'aberta',
                    'status_novo' => 'aberta',
                    'mensagem' => "Agendamento aprovado. Serviço: {$agendamento->servico}. Horário: "
                        . $agendamento->data_agendada->format('d/m/Y') . ' às ' . $agendamento->hora . '.',
                    'observacoes' => $agendamento->observacoes,
                    'data_atualizacao' => now(),
                ]);

                NotificacaoService::criar(
                    (int) $request->mecanico_id,
                    'nova_atribuicao',
                    'Nova tarefa do portal',
                    "Você recebeu \"{$atribuicao->titulo}\" (O.S. {$ordemCriada->numero_ordem}). Prazo: "
                        . $atribuicao->prazo->format('d/m/Y H:i') . '.',
                    route('atribuicoes.index'),
                    $atribuicao
                );

                $mensagemExtra .= " O.S. {$ordemCriada->numero_ordem} criada e atribuída ao mecânico.";

                return;
            }

            $agendamento->status = $request->status;
            $agendamento->resposta_admin = $request->resposta_admin;
            $agendamento->respondido_por = auth()->id();
            $agendamento->respondido_em = now();
            $agendamento->save();
        });

        $sucesso = 'Agendamento atualizado com sucesso.' . $mensagemExtra;
        if ($ordemCriada) {
            $sucesso .= ' Abra a O.S. em Ordens de Serviço → ' . $ordemCriada->numero_ordem . '.';
        }

        return back()->with('success', $sucesso);
    }

    private function garantirClienteVeiculo(Agendamento $agendamento, string &$mensagemExtra): void
    {
        if (! $agendamento->cliente_id && $agendamento->cpf_contato) {
            $existente = Cliente::buscarPorCpf($agendamento->cpf_contato);
            if ($existente) {
                $agendamento->cliente_id = $existente->id;
                $agendamento->eh_visitante = false;
                $mensagemExtra = " Cliente já existia no sistema (#{$existente->id} {$existente->nome}) — vínculo pelo CPF.";
            }
        }

        // Visitante sem cadastro: cria cliente + veículo com os dados do formulário
        if (! $agendamento->cliente_id) {
            $cliente = $this->criarClienteDoAgendamento($agendamento);
            $veiculo = $this->criarVeiculoBasico($agendamento, $cliente);
            $agendamento->cliente_id = $cliente->id;
            $agendamento->veiculo_id = $veiculo?->id;
            $agendamento->eh_visitante = false;
            $mensagemExtra = " Cliente #{$cliente->id} e veículo cadastrados automaticamente.";
        }

        // Cliente vinculado, mas ainda sem veículo deste cliente: cadastra com dados do agendamento
        if ($agendamento->cliente_id && ! $agendamento->veiculo_id) {
            $veiculo = $this->criarVeiculoBasico($agendamento, Cliente::findOrFail($agendamento->cliente_id));
            if ($veiculo) {
                $agendamento->veiculo_id = $veiculo->id;
                $mensagemExtra .= " Veículo #{$veiculo->id} ({$veiculo->placa}) cadastrado/vinculado.";
            }
        }
    }

    private function criarOrdemDoAgendamento(Agendamento $agendamento): OrdemServico
    {
        $obs = collect([
            'Gerada automaticamente a partir do agendamento do portal.',
            'Serviço solicitado: ' . ($agendamento->servico ?: '—'),
            'Data/hora agendada: ' . $agendamento->data_agendada->format('d/m/Y') . ' às ' . $agendamento->hora,
            $agendamento->observacoes ? 'Relato do cliente: ' . $agendamento->observacoes : null,
        ])->filter()->implode("\n");

        $dataEntrada = $agendamento->data_agendada?->format('Y-m-d') ?: now()->format('Y-m-d');

        return OrdemServico::create([
            'cliente_id' => $agendamento->cliente_id,
            'veiculo_id' => $agendamento->veiculo_id,
            'data_entrada' => $dataEntrada,
            'km_entrada' => (int) ($agendamento->km_veiculo ?: ($agendamento->veiculo?->km ?? 0)),
            'status' => 'aberta',
            'status_pagamento' => 'pendente',
            'observacoes' => $obs,
            'valor_total' => 0,
        ]);
    }

    private function criarItemServicoSeExistir(OrdemServico $ordem, Agendamento $agendamento): void
    {
        if (! $agendamento->servico) {
            return;
        }

        $servico = ServicoPeca::where('tipo', 'servico')
            ->where('ativo', true)
            ->where('descricao', $agendamento->servico)
            ->first();

        if (! $servico) {
            return;
        }

        ItemOrdemServico::create([
            'ordem_servico_id' => $ordem->id,
            'servico_peca_id' => $servico->id,
            'quantidade' => 1,
            'valor_unitario' => $servico->valor_unitario,
            'valor_total' => $servico->valor_unitario,
            'estoque_baixado' => false,
        ]);
    }

    private function criarAtribuicao(
        OrdemServico $ordem,
        Agendamento $agendamento,
        int $mecanicoId,
        string $prazo
    ): AtribuicaoServico {
        $servicoPecaId = null;
        if ($agendamento->servico) {
            $servicoPecaId = ServicoPeca::where('tipo', 'servico')
                ->where('descricao', $agendamento->servico)
                ->value('id');
        }

        $descricao = collect([
            'Cliente: ' . $agendamento->nomeExibicao(),
            'Veículo: ' . $agendamento->veiculoExibicao(),
            'Agendado para: ' . $agendamento->data_agendada->format('d/m/Y') . ' às ' . $agendamento->hora,
            $agendamento->observacoes ? 'Relato: ' . $agendamento->observacoes : null,
        ])->filter()->implode("\n");

        return AtribuicaoServico::create([
            'ordem_servico_id' => $ordem->id,
            'servico_peca_id' => $servicoPecaId,
            'mecanico_id' => $mecanicoId,
            'atribuido_por' => auth()->id(),
            'titulo' => $agendamento->servico ?: ('O.S. ' . $ordem->numero_ordem),
            'descricao' => $descricao,
            'prazo' => $prazo,
            'status' => 'pendente',
            'progresso' => 0,
        ]);
    }

    private function criarClienteDoAgendamento(Agendamento $agendamento): Cliente
    {
        $cpf = preg_replace('/[^0-9]/', '', (string) $agendamento->cpf_contato);

        $existente = $cpf ? Cliente::buscarPorCpf($cpf) : null;
        if ($existente) {
            return $existente;
        }

        $email = $agendamento->email_contato
            ? strtolower(trim($agendamento->email_contato))
            : null;

        if ($email) {
            $emailEmUso = Cliente::whereRaw('LOWER(email) = ?', [$email])->exists();
            if ($emailEmUso) {
                $email = null;
            }
        }

        return Cliente::create([
            'nome' => $agendamento->nome_contato ?: 'Cliente Portal',
            'cpf' => $cpf ?: ('TEMP' . time()),
            'telefone' => $agendamento->telefone_contato,
            'celular' => $agendamento->telefone_contato,
            'email' => $email,
            'acesso_portal' => false,
        ]);
    }

    private function criarVeiculoBasico(Agendamento $agendamento, Cliente $cliente): ?Veiculo
    {
        $placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($agendamento->placa_veiculo ?? '')));

        if ($placa === '' && $agendamento->veiculo_info) {
            if (preg_match('/\b([A-Za-z]{3}[\-]?\d[A-Za-z0-9]\d{2}|[A-Za-z]{3}[\-]?\d{4})\b/', $agendamento->veiculo_info, $m)) {
                $placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $m[1]));
            }
        }

        // Só reutiliza veículo da MESMA placa se já for deste cliente
        if ($placa !== '') {
            $doCliente = Veiculo::where('placa', $placa)
                ->where('cliente_id', $cliente->id)
                ->first();

            if ($doCliente) {
                $doCliente->fill([
                    'marca' => $agendamento->marca_veiculo ?: $doCliente->marca,
                    'modelo' => $agendamento->modelo_veiculo ?: $doCliente->modelo,
                    'cor' => $agendamento->cor_veiculo ?: $doCliente->cor,
                    'ano' => $agendamento->ano_veiculo ?: $doCliente->ano,
                    'chassi' => $agendamento->chassi_veiculo ?: $doCliente->chassi,
                    'km' => $agendamento->km_veiculo !== null ? (int) $agendamento->km_veiculo : $doCliente->km,
                ])->save();

                return $doCliente;
            }
        }

        if ($placa === '' && ! $agendamento->marca_veiculo && ! $agendamento->veiculo_info) {
            return null;
        }

        if ($placa === '') {
            $placa = 'TMP' . substr((string) time(), -4);
        }

        return Veiculo::create([
            'cliente_id' => $cliente->id,
            'marca' => $agendamento->marca_veiculo ?: 'A definir',
            'modelo' => $agendamento->modelo_veiculo ?: 'A definir',
            'cor' => $agendamento->cor_veiculo ?: 'A definir',
            'ano' => (int) ($agendamento->ano_veiculo ?: date('Y')),
            'placa' => $placa,
            'chassi' => $agendamento->chassi_veiculo,
            'km' => (int) ($agendamento->km_veiculo ?: 0),
        ]);
    }
}
