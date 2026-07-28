@extends('layouts.app')

@section('title', $isAdmin ? 'Atribuições - Kanban' : 'Minhas Tarefas')
@section('page-title', $isAdmin ? 'Distribuição de Tarefas' : 'Painel do Dia')

@section('content')
@php
    $labels = \App\Models\AtribuicaoServico::STATUS;
    $cores = [
        'pendente' => 'border-slate-400 bg-slate-50',
        'em_andamento' => 'border-sky-500 bg-sky-50',
        'paralisado' => 'border-amber-500 bg-amber-50',
        'excedido' => 'border-rose-600 bg-rose-50',
        'concluido' => 'border-emerald-500 bg-emerald-50',
    ];
@endphp

<div class="space-y-6" x-data="{ modalNova: false, cardAberto: null }">

    @if($alertasPrazo->count())
        <div class="rounded-xl border-2 border-rose-500 bg-rose-50 p-4 shadow-lg animate-pulse">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-rose-800 text-lg">Alerta de prazo excedido</h3>
                    <p class="text-sm text-rose-700 mb-2">{{ $alertasPrazo->count() }} tarefa(s) precisam de atenção imediata.</p>
                    <ul class="text-sm text-rose-900 space-y-1">
                        @foreach($alertasPrazo->take(5) as $alerta)
                            <li>
                                <strong>{{ $alerta->titulo }}</strong>
                                — prazo {{ $alerta->prazo?->format('d/m/Y H:i') }}
                                @if($alerta->temReagendamentoPendente())
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 text-xs">Reagendamento pendente</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isAdmin ? 'Quadro de Atribuições' : 'Minhas Tarefas do Dia' }}</h1>
            <p class="text-gray-600 text-sm">
                {{ $isAdmin ? 'Crie e distribua tarefas com prazo SLA para cada mecânico.' : 'Atualize o status das suas atribuições e solicite prorrogação quando necessário.' }}
            </p>
        </div>
        @if($isAdmin)
            <button type="button" @click="modalNova = true"
                    class="inline-flex items-center px-5 py-3 btn-primary text-white rounded-lg font-medium">
                <i class="fas fa-plus mr-2"></i> Nova Atribuição
            </button>
        @endif
    </div>

    @if($isAdmin && $reagendamentosPendentes->count())
        <div class="bg-white rounded-xl shadow border border-amber-200 p-4">
            <h3 class="font-semibold text-amber-800 mb-3"><i class="fas fa-clock mr-2"></i>Solicitações de prorrogação</h3>
            <div class="space-y-3">
                @foreach($reagendamentosPendentes as $req)
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3 p-3 bg-amber-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ $req->titulo }} — {{ $req->mecanico->name ?? 'Mecânico' }}</p>
                            <p class="text-sm text-gray-600">Justificativa: {{ $req->motivo_nao_conclusao }}</p>
                            <p class="text-sm text-amber-800">Novo prazo solicitado: {{ $req->novo_prazo?->format('d/m/Y H:i') }}</p>
                        </div>
                        <form method="POST" action="{{ route('atribuicoes.reagendar.aprovar', $req) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="datetime-local" name="novo_prazo"
                                   value="{{ $req->novo_prazo?->format('Y-m-d\TH:i') }}"
                                   class="px-2 py-1 border rounded text-sm" required>
                            <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Aprovar</button>
                        </form>
                        <form method="POST" action="{{ route('atribuicoes.reagendar.recusar', $req) }}">
                            @csrf
                            <button class="px-3 py-1.5 bg-rose-600 text-white rounded text-sm">Recusar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Kanban --}}
    <div class="overflow-x-auto pb-4">
        <div class="inline-flex min-w-full gap-4">
            @foreach($colunas as $status => $cards)
                <div class="w-72 shrink-0 flex flex-col rounded-xl border-t-4 {{ $cores[$status] }} shadow-sm bg-white/80">
                    <div class="px-3 py-3 border-b flex items-center justify-between">
                        <h3 class="font-semibold text-sm text-gray-800">{{ $labels[$status] }}</h3>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-gray-700">{{ count($cards) }}</span>
                    </div>
                    <div class="p-2 space-y-2 max-h-[70vh] overflow-y-auto">
                        @forelse($cards as $card)
                            @php
                                $os = $card->ordemServico;
                                $cliente = $os?->cliente;
                                $veiculo = $os?->veiculo;
                            @endphp
                            <div class="bg-white rounded-lg border shadow-sm p-3 space-y-2 {{ $status === 'excedido' ? 'ring-2 ring-rose-400' : '' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-sm text-gray-900 leading-snug">{{ $card->titulo }}</p>
                                    @if($isAdmin)
                                        <form method="POST" action="{{ route('atribuicoes.destroy', $card) }}" onsubmit="return confirm('Excluir esta atribuição?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-500 hover:text-rose-700 text-xs" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>

                                @if($card->servicoPeca)
                                    <p class="text-[11px] text-violet-700 font-medium">
                                        <i class="fas fa-wrench mr-1"></i>{{ $card->servicoPeca->descricao }}
                                    </p>
                                @endif

                                @if($isAdmin)
                                    <p class="text-xs text-gray-500"><i class="fas fa-user-cog mr-1"></i>{{ $card->mecanico->name ?? '—' }}</p>
                                @endif

                                @if($cliente)
                                    <p class="text-xs text-gray-800 font-medium truncate" title="{{ $cliente->nome }}">
                                        <i class="fas fa-user mr-1 text-blue-600"></i>{{ $cliente->nome }}
                                    </p>
                                @endif

                                <p class="text-xs {{ $card->prazo && $card->prazo->isPast() && $status !== 'concluido' ? 'text-rose-700 font-semibold' : 'text-gray-600' }}">
                                    <i class="fas fa-hourglass-half mr-1"></i>
                                    SLA: {{ $card->prazo?->format('d/m/Y H:i') ?? '—' }}
                                </p>

                                @if($veiculo)
                                    <p class="text-[11px] text-gray-500">{{ $veiculo->placa_formatada }} · {{ $veiculo->marca }} {{ $veiculo->modelo }} · {{ $veiculo->ano }}</p>
                                @endif

                                {{-- Atalhos principais: Cliente + O.S. --}}
                                <div class="grid grid-cols-2 gap-1.5 pt-1">
                                    @if($cliente)
                                        <a href="{{ route('clientes.show', $cliente) }}"
                                           class="inline-flex items-center justify-center gap-1 text-[11px] font-semibold px-2 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                                           title="Abrir cadastro do cliente">
                                            <i class="fas fa-id-card"></i>
                                            <span>Cliente</span>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center text-[11px] px-2 py-2 rounded-lg bg-gray-100 text-gray-400">Sem cliente</span>
                                    @endif

                                    @if($os)
                                        <a href="{{ route('ordens.show', $os) }}"
                                           class="inline-flex items-center justify-center gap-1 text-[11px] font-semibold px-2 py-2 rounded-lg text-white transition-colors"
                                           style="background: var(--brand-primary, #FF6B35)"
                                           title="Abrir ordem de serviço {{ $os->numero_ordem }}">
                                            <i class="fas fa-clipboard-list"></i>
                                            <span>O.S.</span>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center text-[11px] px-2 py-2 rounded-lg bg-gray-100 text-gray-400">Sem O.S.</span>
                                    @endif
                                </div>

                                @if($veiculo)
                                    <a href="{{ route('veiculos.show', $veiculo) }}"
                                       class="block w-full text-center text-[11px] font-medium py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                                        <i class="fas fa-car mr-1"></i>Ver veículo {{ $veiculo->placa_formatada }}
                                    </a>
                                @endif

                                <button type="button" @click="cardAberto = cardAberto === {{ $card->id }} ? null : {{ $card->id }}"
                                        class="w-full text-xs py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                                    Atualizar status
                                </button>

                                <div x-show="cardAberto === {{ $card->id }}" x-cloak class="pt-2 border-t space-y-2">
                                    <form method="POST" action="{{ route('atribuicoes.progresso', $card) }}" class="space-y-2"
                                          x-data="{ status: '{{ $card->status === 'excedido' ? 'em_andamento' : $card->status }}', pedirPrazo: {{ $card->status === 'excedido' || $card->temReagendamentoPendente() ? 'true' : 'false' }} }">
                                        @csrf
                                        @method('PATCH')

                                        <select name="status" x-model="status" class="w-full text-xs border rounded px-2 py-1.5" required>
                                            <option value="pendente">Pendente</option>
                                            <option value="em_andamento">Em Andamento</option>
                                            <option value="paralisado">Paralisado</option>
                                            <option value="concluido">Concluído</option>
                                            @if($card->status === 'excedido')
                                                <option value="excedido" selected>Excedido no Prazo</option>
                                            @endif
                                        </select>

                                        <textarea name="observacao_mecanico" rows="2" placeholder="Observação (ex.: aguardando peça)"
                                                  class="w-full text-xs border rounded px-2 py-1.5">{{ $card->observacao_mecanico }}</textarea>

                                        @if(!$isAdmin)
                                            <label class="flex items-center gap-2 text-xs text-gray-700">
                                                <input type="checkbox" name="solicitar_reagendamento" value="1"
                                                       x-model="pedirPrazo"
                                                       @if($card->status === 'excedido') checked @endif>
                                                Solicitar prorrogação de prazo
                                            </label>

                                            <div x-show="pedirPrazo || status === 'excedido'" class="space-y-2 bg-rose-50 p-2 rounded">
                                                <textarea name="motivo_nao_conclusao" rows="2" placeholder="Justificativa obrigatória *"
                                                          class="w-full text-xs border rounded px-2 py-1.5"
                                                          :required="pedirPrazo || status === 'excedido'">{{ $card->motivo_nao_conclusao }}</textarea>
                                                <input type="datetime-local" name="novo_prazo"
                                                       value="{{ $card->novo_prazo?->format('Y-m-d\TH:i') }}"
                                                       class="w-full text-xs border rounded px-2 py-1.5"
                                                       :required="pedirPrazo || status === 'excedido'">
                                            </div>
                                        @endif

                                        <button type="submit" class="w-full text-xs py-1.5 rounded bg-orange-primary text-white font-medium">
                                            Salvar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-6">Nenhuma tarefa</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($isAdmin)
        <div x-show="modalNova" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="modalNova = false">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6" @click.outside="modalNova = false">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Nova atribuição</h3>
                    <button type="button" @click="modalNova = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form method="POST" action="{{ route('atribuicoes.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem de Serviço *</label>
                        <select name="ordem_servico_id" required class="w-full border rounded-lg px-3 py-2">
                            <option value="">Selecione...</option>
                            @foreach($ordens as $ordem)
                                <option value="{{ $ordem->id }}">
                                    {{ $ordem->numero_ordem }} — {{ $ordem->cliente->nome ?? 'Cliente' }}
                                    ({{ $ordem->veiculo->placa_formatada ?? 's/ placa' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mecânico *</label>
                        <select name="mecanico_id" required class="w-full border rounded-lg px-3 py-2">
                            <option value="">Selecione...</option>
                            @foreach($mecanicos as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serviço (opcional)</label>
                        <select name="servico_peca_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">—</option>
                            @foreach($servicos as $s)
                                <option value="{{ $s->id }}">{{ $s->descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                        <input type="text" name="titulo" required class="w-full border rounded-lg px-3 py-2" placeholder="Ex: Troca de correia">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prazo SLA (data e hora) *</label>
                        <input type="datetime-local" name="prazo" required class="w-full border rounded-lg px-3 py-2"
                               value="{{ now()->addHours(8)->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalNova = false" class="px-4 py-2 bg-gray-200 rounded-lg">Cancelar</button>
                        <button type="submit" class="px-4 py-2 btn-primary text-white rounded-lg">Atribuir</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
