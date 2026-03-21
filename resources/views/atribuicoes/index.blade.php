@extends('layouts.app')

@section('title', 'Atribuições de Serviço - Sistema de Oficina')
@section('page-title', 'Atribuições de Serviço')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Controle de atividades da oficina</h1>
        <p class="text-gray-600">
            @if($isAdmin)
                Defina qual mecânico executará cada serviço, prazo e acompanhe o progresso.
            @else
                Registre seu progresso, conclusão, atrasos e novo prazo das atividades atribuídas a você.
            @endif
        </p>
    </div>

    @if($isAdmin)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-user-cog text-orange-primary mr-2"></i>
                Nova atribuição para mecânico
            </h2>
            <form method="POST" action="{{ route('atribuicoes.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordem de Serviço *</label>
                    <select name="ordem_servico_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">Selecione</option>
                        @foreach($ordens as $ordem)
                            <option value="{{ $ordem->id }}">
                                {{ $ordem->numero_ordem }} - {{ $ordem->cliente->nome ?? 'Sem cliente' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mecânico *</label>
                    <select name="mecanico_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">Selecione</option>
                        @foreach($mecanicos as $mecanico)
                            <option value="{{ $mecanico->id }}">{{ $mecanico->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serviço vinculado</label>
                    <select name="servico_peca_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">Opcional</option>
                        @foreach($servicos as $servico)
                            <option value="{{ $servico->id }}">{{ $servico->descricao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título da atividade *</label>
                    <input type="text" name="titulo" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ex: Troca de pastilha dianteira">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prazo *</label>
                    <input type="date" name="prazo" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Detalhes do que deve ser executado"></textarea>
                </div>
                <div class="lg:col-span-3">
                    <button type="submit" class="px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary">
                        <i class="fas fa-save mr-2"></i>Salvar atribuição
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Atividades atribuídas</h2>

        <div class="space-y-4">
            @forelse($atribuicoes as $atribuicao)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex flex-wrap justify-between gap-2 mb-2">
                        <h3 class="font-semibold text-gray-900">{{ $atribuicao->titulo }}</h3>
                        <span class="text-sm px-2 py-1 rounded-full
                            {{ $atribuicao->status === 'concluido' ? 'bg-green-100 text-green-700' :
                               ($atribuicao->status === 'nao_concluido' ? 'bg-red-100 text-red-700' :
                               ($atribuicao->status === 'em_andamento' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700')) }}">
                            {{ ucfirst(str_replace('_', ' ', $atribuicao->status)) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">
                        OS: <strong>{{ $atribuicao->ordemServico->numero_ordem ?? '-' }}</strong> |
                        Cliente: <strong>{{ $atribuicao->ordemServico->cliente->nome ?? '-' }}</strong> |
                        Mecânico: <strong>{{ $atribuicao->mecanico->name ?? '-' }}</strong> |
                        Prazo: <strong>{{ $atribuicao->prazo?->format('d/m/Y') }}</strong>
                    </p>
                    @if($atribuicao->descricao)
                        <p class="text-sm text-gray-700 mb-3">{{ $atribuicao->descricao }}</p>
                    @endif

                    <form method="POST" action="{{ route('atribuicoes.progresso', $atribuicao) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="pendente" {{ $atribuicao->status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="em_andamento" {{ $atribuicao->status === 'em_andamento' ? 'selected' : '' }}>Em andamento</option>
                                <option value="concluido" {{ $atribuicao->status === 'concluido' ? 'selected' : '' }}>Concluído</option>
                                <option value="nao_concluido" {{ $atribuicao->status === 'nao_concluido' ? 'selected' : '' }}>Não concluído</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Progresso (%)</label>
                            <input type="number" name="progresso" min="0" max="100" value="{{ $atribuicao->progresso }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Novo prazo (se não concluído)</label>
                            <input type="date" name="novo_prazo" value="{{ $atribuicao->novo_prazo?->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Atualizar
                            </button>
                        </div>
                        <div class="md:col-span-2 lg:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Observação do mecânico</label>
                            <textarea name="observacao_mecanico" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $atribuicao->observacao_mecanico }}</textarea>
                        </div>
                        <div class="md:col-span-2 lg:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Motivo de não conclusão</label>
                            <textarea name="motivo_nao_conclusao" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $atribuicao->motivo_nao_conclusao }}</textarea>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-gray-500 text-sm">Nenhuma atividade atribuída no momento.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $atribuicoes->links() }}
        </div>
    </div>
</div>
@endsection
