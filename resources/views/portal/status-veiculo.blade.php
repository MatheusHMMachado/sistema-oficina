@extends('layouts.portal-cliente')

@section('title', 'Andamento do Veículo - ' . $cliente->nome)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-car text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $cliente->nome }}</h1>
                    <p class="text-gray-600">CPF: {{ $cliente->cpf_formatado }}</p>
                </div>
            </div>
            <a href="{{ route('portal.home') }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Voltar ao Portal
            </a>
        </div>
    </div>

    @if($ordens->count() > 0)
        <div class="space-y-4">
            @foreach($ordens as $ordem)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="bg-gradient-to-r from-orange-primary to-orange-secondary p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white">OS: {{ $ordem->numero_ordem }}</h3>
                                <p class="text-orange-100 text-sm mt-1">
                                    <i class="fas fa-calendar mr-1"></i>Entrada: {{ $ordem->data_entrada->format('d/m/Y') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' :
                                   ($ordem->status === 'em_andamento' ? 'bg-orange-100 text-orange-800' :
                                   ($ordem->status === 'concluida' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                @if($ordem->status === 'aberta')
                                    <i class="fas fa-folder-open mr-1"></i> Aberta
                                @elseif($ordem->status === 'em_andamento')
                                    <i class="fas fa-cog fa-spin mr-1"></i> Em Andamento
                                @elseif($ordem->status === 'concluida')
                                    <i class="fas fa-check-circle mr-1"></i> Concluída
                                @else
                                    <i class="fas fa-times-circle mr-1"></i> Cancelada
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Veículo</label>
                                <p class="text-gray-900 font-semibold">
                                    @if($ordem->veiculo)
                                        {{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }} - {{ $ordem->veiculo->placa_formatada }}
                                    @else
                                        Veículo não encontrado
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Valor Total</label>
                                <p class="text-orange-primary font-bold text-xl">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($ordem->statusAtualizacoes->count() > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-history mr-2 text-orange-primary"></i>Andamento do Serviço
                                </h4>
                                <div class="space-y-4">
                                    @foreach($ordem->statusAtualizacoes as $atualizacao)
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-10 h-10 bg-orange-primary rounded-full flex items-center justify-center">
                                                <i class="fas fa-info text-white text-sm"></i>
                                            </div>
                                            <div class="ml-4 flex-1 pb-4">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        @if($atualizacao->status_anterior != $atualizacao->status_novo)
                                                            Status: <span class="text-orange-primary">
                                                                {{ ucfirst(str_replace('_', ' ', $atualizacao->status_anterior)) }} →
                                                                {{ ucfirst(str_replace('_', ' ', $atualizacao->status_novo)) }}</span>
                                                        @else
                                                            Atualização
                                                        @endif
                                                    </p>
                                                    <span class="text-xs text-gray-500">{{ $atualizacao->data_atualizacao->format('d/m/Y H:i') }}</span>
                                                </div>
                                                @if($atualizacao->mensagem)
                                                    <p class="mt-2 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $atualizacao->mensagem }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="mt-6 pt-6 border-t border-gray-200 text-center py-8">
                                <i class="fas fa-info-circle text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-500">Ainda não há atualizações para esta ordem.</p>
                            </div>
                        @endif

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="{{ route('portal.ordem.publica', ['id' => $ordem->id, 'cpf' => preg_replace('/[^0-9]/', '', $cliente->cpf)]) }}"
                               class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors">
                                <i class="fas fa-eye mr-2"></i>Ver Detalhes Completos
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhuma ordem encontrada</h3>
            <p class="text-gray-600">Não há ordens de serviço para este CPF.</p>
        </div>
    @endif
</div>
@endsection
