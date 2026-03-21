@extends('layouts.portal-cliente')

@section('title', 'Detalhes da OS ' . $ordem->numero_ordem)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">OS: {{ $ordem->numero_ordem }}</h1>
                <p class="text-gray-600 mt-1">{{ $cliente->nome }}</p>
            </div>
            <a href="{{ route('portal.consulta-publica') }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Status Atual</h3>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-semibold
                    {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' :
                       ($ordem->status === 'em_andamento' ? 'bg-orange-100 text-orange-800' :
                       ($ordem->status === 'concluida' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                    @if($ordem->status === 'aberta')
                        <i class="fas fa-folder-open mr-2"></i> Aberta
                    @elseif($ordem->status === 'em_andamento')
                        <i class="fas fa-cog fa-spin mr-2"></i> Em Andamento
                    @elseif($ordem->status === 'concluida')
                        <i class="fas fa-check-circle mr-2"></i> Concluída
                    @else
                        <i class="fas fa-times-circle mr-2"></i> Cancelada
                    @endif
                </span>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Valor Total</p>
                <p class="text-2xl font-bold text-orange-primary">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    @if($ordem->veiculo)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-car text-orange-primary mr-2"></i>Informações do Veículo
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Veículo</label>
                    <p class="text-gray-900 font-semibold">{{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Placa</label>
                    <p class="text-gray-900 font-semibold">{{ $ordem->veiculo->placa_formatada }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Ano</label>
                    <p class="text-gray-900 font-semibold">{{ $ordem->veiculo->ano }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">KM Entrada</label>
                    <p class="text-gray-900 font-semibold">{{ number_format($ordem->km_entrada ?? 0) }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-history text-orange-primary mr-2"></i>Andamento do Serviço
        </h3>

        @if($ordem->statusAtualizacoes->count() > 0)
            <div class="space-y-6">
                @foreach($ordem->statusAtualizacoes as $atualizacao)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-info text-white"></i>
                        </div>
                        <div class="ml-4 flex-1 pb-6">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-base font-semibold text-gray-900">
                                    @if($atualizacao->status_anterior != $atualizacao->status_novo)
                                        <i class="fas fa-sync-alt text-orange-primary mr-2"></i>Status alterado:
                                        <span class="text-orange-primary">
                                            {{ ucfirst(str_replace('_', ' ', $atualizacao->status_anterior)) }} →
                                            {{ ucfirst(str_replace('_', ' ', $atualizacao->status_novo)) }}</span>
                                    @else
                                        <i class="fas fa-comment-dots text-purple-500 mr-2"></i>Atualização do Serviço
                                    @endif
                                </p>
                                <span class="text-sm text-gray-500">{{ $atualizacao->data_atualizacao->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($atualizacao->mensagem)
                                <div class="bg-orange-50 border-l-4 border-orange-primary p-4 rounded-r-lg mb-2">
                                    <p class="text-gray-800">{{ $atualizacao->mensagem }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-500">Ainda não há atualizações para esta ordem.</p>
            </div>
        @endif
    </div>

    @if($ordem->itens->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-cogs text-purple-500 mr-2"></i>Serviços e Peças
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ordem->itens as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->servicoPeca->descricao ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->quantidade }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">TOTAL:</td>
                            <td class="px-4 py-3 text-lg font-bold text-orange-primary">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    @if($ordem->observacoes)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>Observações
            </h3>
            <p class="text-gray-700 bg-yellow-50 p-4 rounded-lg">{{ $ordem->observacoes }}</p>
        </div>
    @endif
</div>
@endsection
