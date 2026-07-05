@extends('layouts.app')

@section('title', 'OS ' . $ordem->numero_ordem . ' - Sistema de Oficina')
@section('page-title', 'Ordem de Serviço')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-primary rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-clipboard-list text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Ordem de Serviço {{ $ordem->numero_ordem }}</h1>
                    <p class="text-gray-600">
                        @if($ordem->veiculo)
                            {{ $ordem->veiculo->descricao_completa }} - {{ $ordem->veiculo->placa_formatada }}
                        @else
                            Veículo não encontrado
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('ordens.edit', $ordem) }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <a href="{{ route('ordens.pdf', $ordem) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Gerar PDF
                </a>
                <a href="{{ route('ordens.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Feedback para o Cliente (ambiente separado: Portal do Cliente) -->
        @if($ordem->cliente)
            <div id="secao-feedback-cliente" class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl shadow-lg p-5 mb-6 border-2 border-orange-200 scroll-mt-24">
                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-comments text-orange-primary mr-2"></i>
                    Feedback para o Cliente
                </h4>
                <p class="text-sm text-gray-600 mb-4">
                    O cliente acompanha o andamento em <strong>outro ambiente</strong> (Portal do Cliente), apenas com CPF. Use o formulário abaixo para enviar atualizações que ele verá lá.
                </p>
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <a href="{{ url('/portal/consulta') }}?cpf={{ preg_replace('/[^0-9]/', '', $ordem->cliente->cpf) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors text-sm">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Abrir Portal do Cliente (ver como cliente)
                    </a>
                </div>
                <form method="POST" action="{{ route('ordens.update-status', $ordem) }}" class="space-y-3 p-4 bg-white rounded-lg border border-orange-100">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status (opcional)</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent text-sm">
                                <option value="aberta" {{ $ordem->status === 'aberta' ? 'selected' : '' }}>Aberta</option>
                                <option value="em_andamento" {{ $ordem->status === 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                <option value="concluida" {{ $ordem->status === 'concluida' ? 'selected' : '' }}>Concluída</option>
                                <option value="cancelada" {{ $ordem->status === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem para o cliente *</label>
                        <textarea name="mensagem" rows="2" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent text-sm"
                                  placeholder="Ex: Veículo em manutenção; previsão de conclusão amanhã."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações internas (opcional)</label>
                        <textarea name="observacoes" rows="1"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent text-sm"
                                  placeholder="Só para a equipe."></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors text-sm">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar feedback ao cliente
                    </button>
                </form>
                @if($ordem->statusAtualizacoes->count() > 0)
                    <div class="mt-4 pt-4 border-t border-orange-200">
                        <p class="text-sm font-medium text-gray-700 mb-2">Feedbacks já enviados (o cliente vê no portal):</p>
                        <ul class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($ordem->statusAtualizacoes->sortByDesc('data_atualizacao') as $at)
                                <li class="text-sm text-gray-600 bg-white rounded px-3 py-2 border border-gray-100">
                                    <span class="font-medium">{{ $at->data_atualizacao->format('d/m/Y H:i') }}</span>
                                    @if($at->status_anterior != $at->status_novo)
                                        — {{ ucfirst(str_replace('_', ' ', $at->status_novo)) }}
                                    @endif
                                    @if($at->mensagem)
                                        — {{ Str::limit($at->mensagem, 60) }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- Status -->
        <div class="mb-6">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' : 
                   ($ordem->status === 'em_andamento' ? 'bg-orange-100 text-orange-800' : 
                   ($ordem->status === 'concluida' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                <i class="fas fa-circle mr-2 text-xs"></i>
                {{ ucfirst(str_replace('_', ' ', $ordem->status)) }}
            </span>
        </div>

        <!-- Informações Básicas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 p-4 bg-gray-50 rounded-lg">
            <div>
                <label class="block text-sm font-medium text-gray-700">Data de Entrada</label>
                <p class="text-sm font-semibold text-gray-900">{{ $ordem->data_entrada->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Data de Saída</label>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $ordem->data_saida ? $ordem->data_saida->format('d/m/Y') : 'Não definida' }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">KM Entrada</label>
                <p class="text-sm font-semibold text-gray-900">{{ number_format($ordem->km_entrada ?? 0) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Valor Total</label>
                <p class="text-lg font-bold text-orange-primary">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Cliente e Veículo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cliente -->
        @if($ordem->cliente)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Cliente
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Nome</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $ordem->cliente->nome }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">CPF</label>
                            <p class="text-sm text-gray-900">{{ $ordem->cliente->cpf_formatado }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">RG</label>
                            <p class="text-sm text-gray-900">{{ $ordem->cliente->rg ?: 'Não informado' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Telefone</label>
                            <p class="text-sm text-gray-900">{{ $ordem->cliente->telefone ?: 'Não informado' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Celular</label>
                            <p class="text-sm text-gray-900">{{ $ordem->cliente->celular ?: 'Não informado' }}</p>
                        </div>
                    </div>
                    @if($ordem->cliente->endereco)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Endereço</label>
                            <p class="text-sm text-gray-900">{{ $ordem->cliente->endereco }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Cliente
                </h3>
                <p class="text-sm text-gray-500">Cliente não encontrado</p>
            </div>
        @endif

        <!-- Veículo -->
        @if($ordem->veiculo)
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-car text-green-500 mr-2"></i>
                    Veículo
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Veículo</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Cor</label>
                            <p class="text-sm text-gray-900">{{ $ordem->veiculo->cor }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Ano</label>
                            <p class="text-sm text-gray-900">{{ $ordem->veiculo->ano }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Placa</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $ordem->veiculo->placa_formatada }}</p>
                        </div>
                    </div>
                    @if($ordem->veiculo->chassi)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Chassi</label>
                            <p class="text-sm text-gray-900">{{ $ordem->veiculo->chassi }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Quilometragem</label>
                        <p class="text-sm text-gray-900">{{ number_format($ordem->veiculo->km ?? 0) }} km</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-car text-green-500 mr-2"></i>
                    Veículo
                </h3>
                <p class="text-sm text-gray-500">Veículo não encontrado</p>
            </div>
        @endif
    </div>

    <!-- Serviços e Peças -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-cogs text-purple-500 mr-2"></i>
                Serviços e Peças ({{ $ordem->itens->count() }})
            </h3>
            @if($ordem->status !== 'concluida')
                <a href="{{ route('ordens.edit', $ordem) }}" 
                   class="text-orange-primary hover:text-orange-secondary text-sm font-medium">
                    <i class="fas fa-plus mr-1"></i>
                    Adicionar Item
                </a>
            @endif
        </div>

        @if($ordem->itens->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Unit.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ordem->itens as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 {{ $item->servicoPeca->tipo == 'servico' ? 'bg-green-100' : 'bg-purple-100' }} rounded-full flex items-center justify-center mr-3">
                                            <i class="fas {{ $item->servicoPeca->tipo == 'servico' ? 'fa-wrench text-green-600' : 'fa-cog text-purple-600' }} text-xs"></i>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->servicoPeca->descricao }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->servicoPeca->tipo == 'servico' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $item->servicoPeca->tipo == 'servico' ? 'Serviço' : 'Peça' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->quantidade }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                TOTAL GERAL:
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-orange-primary">
                                R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum item adicionado</h3>
                <p class="text-gray-500 mb-4">Adicione serviços e peças a esta ordem de serviço.</p>
                <a href="{{ route('ordens.edit', $ordem) }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Adicionar Itens
                </a>
            </div>
        @endif
    </div>

    <!-- Observações -->
    @if($ordem->observacoes)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                Observações
            </h3>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-gray-700">{{ $ordem->observacoes }}</p>
            </div>
        </div>
    @endif

    <!-- Checklist -->
    @if($ordem->checklist->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-check text-purple-500 mr-2"></i>
                    Checklist Realizado ({{ $ordem->checklist->count() }} pontos)
                </h3>
                <a href="{{ route('checklist.show', $ordem) }}" 
                   class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    Ver Checklist Completo <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ordem->checklist->take(6) as $item)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900">{{ $pontosVeiculo[$item->ponto_veiculo] ?? $item->ponto_veiculo }}</span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $item->condicao === 'otimo' ? 'bg-green-100 text-green-800' : 
                                   ($item->condicao === 'bom' ? 'bg-blue-100 text-blue-800' : 
                                   ($item->condicao === 'regular' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($item->condicao === 'ruim' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'))) }}">
                                {{ ucfirst($item->condicao) }}
                            </span>
                        </div>
                        @if($item->observacao)
                            <p class="text-xs text-gray-600">{{ Str::limit($item->observacao, 50) }}</p>
                        @endif
                        @if($item->fotos->count() > 0)
                            <div class="flex items-center mt-2 text-xs text-gray-500">
                                <i class="fas fa-camera mr-1"></i>
                                {{ $item->fotos->count() }} foto(s)
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($ordem->checklist->count() > 6)
                <div class="mt-4 text-center">
                    <a href="{{ route('checklist.show', $ordem) }}" 
                       class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Ver todos os {{ $ordem->checklist->count() }} pontos do checklist <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection