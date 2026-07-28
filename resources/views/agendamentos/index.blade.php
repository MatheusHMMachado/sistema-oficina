@extends('layouts.app')

@section('title', 'Agendamentos')
@section('page-title', 'Solicitações de Agendamento')

@section('content')
@php
    $payloadPendentes = $agendamentos->getCollection()
        ->where('status', 'pendente')
        ->map(function ($ag) {
            $data = optional($ag->data_agendada)->format('Y-m-d');
            $hora = $ag->hora ?: '18:00';
            $candidatos = [];
            if ($data) {
                $candidatos[] = $data . 'T' . substr($hora, 0, 5);
            }
            $candidatos[] = now()->addDay()->format('Y-m-d') . 'T18:00';
            $prazoDefault = $candidatos[0];
            foreach ($candidatos as $c) {
                if (strtotime($c) > time()) {
                    $prazoDefault = $c;
                    break;
                }
            }

            return [
                'id' => $ag->id,
                'nome' => $ag->nomeExibicao(),
                'veiculo' => $ag->veiculoExibicao(),
                'servico' => $ag->servico,
                'data_hora' => optional($ag->data_agendada)->format('d/m/Y') . ' às ' . $ag->hora,
                'observacoes' => $ag->observacoes,
                'tem_cliente' => (bool) $ag->cliente_id,
                'criar_cadastro' => ! $ag->cliente_id,
                'prazo_default' => $prazoDefault,
                'action' => route('agendamentos.status', $ag),
            ];
        })
        ->values();
@endphp

<div class="space-y-6" x-data="aprovacaoAgendamento(@js($payloadPendentes))">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Agendamentos do Portal</h1>
            <p class="text-gray-600">Ao aprovar, o sistema gera a O.S. e atribui um mecânico com base na carga da equipe.</p>
        </div>
        @if($pendentes > 0)
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-sm font-semibold">{{ $pendentes }} pendente(s)</span>
        @endif
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente / Contato</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serviço</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($agendamentos as $ag)
                    <tr class="{{ $ag->status === 'pendente' ? 'bg-amber-50/50' : '' }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 flex items-center gap-2 flex-wrap">
                                {{ $ag->nomeExibicao() }}
                                @if($ag->eh_visitante || ! $ag->cliente_id)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-violet-100 text-violet-800">Novo / Visitante</span>
                                @endif
                                @if($ag->ordemServico)
                                    <a href="{{ route('ordens.show', $ag->ordemServico) }}" class="text-[10px] px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 hover:underline">
                                        O.S. {{ $ag->ordemServico->numero_ordem }}
                                    </a>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $ag->veiculoExibicao() }}</div>
                            @if($ag->placa_veiculo || $ag->marca_veiculo)
                                <div class="text-[11px] text-gray-400 mt-0.5 space-y-0.5">
                                    @if($ag->chassi_veiculo)
                                        <div>Chassi: {{ $ag->chassi_veiculo }}</div>
                                    @endif
                                    @if($ag->km_veiculo)
                                        <div>KM informado: {{ number_format($ag->km_veiculo, 0, ',', '.') }}</div>
                                    @endif
                                </div>
                            @endif
                            @if($ag->cpf_contato)
                                <div class="text-xs text-gray-500">CPF: {{ $ag->cpf_contato }}</div>
                            @endif
                            @if($ag->telefone_contato)
                                <div class="text-xs text-gray-500">Tel: {{ $ag->telefone_contato }}</div>
                            @endif
                            @if($ag->email_contato)
                                <div class="text-xs text-gray-500">{{ $ag->email_contato }}</div>
                            @endif
                            @if($ag->observacoes)
                                <div class="text-xs text-gray-500 mt-1">{{ $ag->observacoes }}</div>
                            @endif
                            @if($ag->resposta_admin && $ag->status !== 'pendente')
                                <div class="text-xs text-emerald-700 mt-1">Resposta: {{ $ag->resposta_admin }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $ag->servico }}</td>
                        <td class="px-4 py-3 text-sm">{{ $ag->data_agendada->format('d/m/Y') }} às {{ $ag->hora }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $ag->status === 'pendente' ? 'bg-amber-100 text-amber-800' : ($ag->status === 'aprovado' ? 'bg-green-100 text-green-800' : ($ag->status === 'recusado' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700')) }}">
                                {{ \App\Models\Agendamento::STATUS[$ag->status] ?? $ag->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($ag->status === 'pendente')
                                <div class="flex flex-col gap-2">
                                    <button type="button"
                                            @click="abrirAprovar({{ $ag->id }})"
                                            class="text-xs px-3 py-1.5 bg-emerald-600 text-white rounded font-semibold hover:bg-emerald-700">
                                        Aprovar e gerar O.S.
                                    </button>
                                    <form method="POST" action="{{ route('agendamentos.status', $ag) }}" class="flex flex-col gap-1">
                                        @csrf
                                        <input type="text" name="resposta_admin" placeholder="Motivo da recusa (opcional)" class="text-xs border rounded px-2 py-1">
                                        <button name="status" value="recusado" class="text-xs px-2 py-1 bg-rose-600 text-white rounded">Recusar</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhum agendamento ainda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $agendamentos->links() }}

    {{-- Modal atribuição inteligente --}}
    <div x-show="aberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" @click="fechar()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col"
             @click.stop>
            <div class="px-6 py-4 border-b flex items-start justify-between gap-3" style="background: color-mix(in srgb, var(--brand-primary) 8%, white)">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Aprovar agendamento → O.S. + Mecânico</h2>
                    <p class="text-sm text-gray-600 mt-0.5" x-text="atual ? (atual.nome + ' · ' + atual.servico + ' · ' + atual.data_hora) : ''"></p>
                </div>
                <button type="button" @click="fechar()" class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>

            <form method="POST" :action="atual?.action" class="flex flex-col flex-1 overflow-hidden" @submit="enviando = true">
                @csrf
                <input type="hidden" name="status" value="aprovado">
                <input type="hidden" name="mecanico_id" :value="mecanicoId">
                <template x-if="atual?.criar_cadastro">
                    <input type="hidden" name="criar_cadastro" value="1">
                </template>

                <div class="p-6 overflow-y-auto space-y-5 flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm bg-gray-50 rounded-xl p-4 border">
                        <div>
                            <div class="text-[11px] uppercase text-gray-500 font-semibold">Cliente</div>
                            <div class="font-medium" x-text="atual?.nome"></div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase text-gray-500 font-semibold">Veículo</div>
                            <div class="font-medium" x-text="atual?.veiculo"></div>
                        </div>
                        <div class="sm:col-span-2" x-show="atual?.observacoes">
                            <div class="text-[11px] uppercase text-gray-500 font-semibold">Relato</div>
                            <div x-text="atual?.observacoes"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">Painel de ocupação da equipe</h3>
                            <button type="button" @click="carregarCarga()" class="text-xs font-medium" style="color: var(--brand-primary)" :disabled="carregando">
                                <span x-show="!carregando">Atualizar</span>
                                <span x-show="carregando">Carregando…</span>
                            </button>
                        </div>

                        <div x-show="erroCarga" class="text-sm text-red-600 mb-2" x-text="erroCarga"></div>

                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <template x-for="m in mecanicos" :key="m.id">
                                <label class="flex gap-3 p-3 rounded-xl border cursor-pointer transition"
                                       :class="mecanicoId == m.id ? 'ring-2 border-transparent' : 'hover:bg-gray-50'"
                                       :style="mecanicoId == m.id ? 'ring-color: var(--brand-primary); background: color-mix(in srgb, var(--brand-primary) 6%, white)' : ''">
                                    <input type="radio" class="mt-1" name="mecanico_radio" :value="m.id" x-model.number="mecanicoId">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-gray-900" x-text="m.nome"></span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-800': m.carga === 'livre',
                                                      'bg-amber-100 text-amber-800': m.carga === 'moderada',
                                                      'bg-rose-100 text-rose-800': m.carga === 'maxima'
                                                  }"
                                                  x-text="m.carga_label"></span>
                                            <span class="text-[11px] text-gray-500"
                                                  x-text="m.ativas_hoje + ' no dia · ' + m.ativas_total + ' ativa(s)'"></span>
                                        </div>
                                        <ul class="mt-1 space-y-0.5" x-show="m.tarefas.length">
                                            <template x-for="(t, i) in m.tarefas" :key="i">
                                                <li class="text-[11px] text-gray-600 truncate"
                                                    x-text="'• ' + (t.os || '') + ' ' + t.titulo + ' — prazo ' + (t.prazo || '—')"></li>
                                            </template>
                                        </ul>
                                        <p class="text-[11px] text-gray-400 mt-1" x-show="!m.tarefas.length">Sem tarefas ativas no momento.</p>
                                    </div>
                                </label>
                            </template>
                            <p class="text-sm text-gray-500" x-show="!carregando && mecanicos.length === 0">Nenhum mecânico ativo cadastrado.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prazo estimado (SLA) *</label>
                            <input type="datetime-local" name="prazo" x-model="prazo" required
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem ao cliente (portal)</label>
                            <input type="text" name="resposta_admin" x-model="resposta"
                                   placeholder="Opcional — confirmação do agendamento"
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    <p class="text-xs text-gray-500" x-show="atual?.criar_cadastro">
                        Visitante: ao confirmar, cliente e veículo serão cadastrados automaticamente.
                    </p>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="fechar()" class="px-4 py-2 text-sm text-gray-600 hover:underline">Cancelar</button>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl disabled:opacity-50"
                            style="background: var(--brand-primary)"
                            :disabled="!mecanicoId || !prazo || enviando">
                        <span x-show="!enviando">Confirmar atribuição e gerar O.S.</span>
                        <span x-show="enviando">Processando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function aprovacaoAgendamento(pendentes) {
    const mapa = {};
    (pendentes || []).forEach(p => { mapa[p.id] = p; });

    return {
        aberto: false,
        atual: null,
        mecanicos: [],
        mecanicoId: null,
        prazo: '',
        resposta: '',
        carregando: false,
        erroCarga: '',
        enviando: false,
        mapa,

        abrirAprovar(id) {
            this.atual = this.mapa[id] || null;
            if (!this.atual) return;
            this.mecanicoId = null;
            this.prazo = this.atual.prazo_default || '';
            this.resposta = '';
            this.enviando = false;
            this.aberto = true;
            this.carregarCarga();
        },

        fechar() {
            this.aberto = false;
            this.atual = null;
            this.enviando = false;
        },

        async carregarCarga() {
            this.carregando = true;
            this.erroCarga = '';
            try {
                const res = await fetch(@js(route('agendamentos.carga-mecanicos')), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Falha ao carregar mecânicos');
                const data = await res.json();
                this.mecanicos = data.mecanicos || [];
                if (!this.mecanicoId && this.mecanicos.length) {
                    const livre = this.mecanicos.find(m => m.carga === 'livre') || this.mecanicos[0];
                    this.mecanicoId = livre.id;
                }
            } catch (e) {
                this.erroCarga = 'Não foi possível carregar a ocupação da equipe.';
            } finally {
                this.carregando = false;
            }
        }
    };
}
</script>
@endpush
