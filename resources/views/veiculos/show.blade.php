<!-- Crie o arquivo: resources/views/veiculos/show.blade.php -->
@extends('layouts.app')

@section('title', $veiculo->marca . ' ' . $veiculo->modelo . ' - Sistema de Oficina')
@section('page-title', 'Visualizar Veículo')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-car text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $veiculo->marca }} {{ $veiculo->modelo }}</h1>
                    <p class="text-gray-600">{{ $veiculo->cor }} • {{ $veiculo->ano }} • {{ $veiculo->placa_formatada }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('veiculos.edit', $veiculo) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <a href="{{ route('ordens.create') }}?veiculo_id={{ $veiculo->id }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Nova OS
                </a>
                <a href="{{ route('veiculos.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Informações do Veículo e Cliente -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Dados do Veículo -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-car text-blue-500 mr-2"></i>
                Dados do Veículo
            </h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Marca</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $veiculo->marca }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $veiculo->modelo }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Cor</label>
                        <p class="text-sm text-gray-900">{{ $veiculo->cor }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Ano</label>
                        <p class="text-sm text-gray-900">{{ $veiculo->ano }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</label>
                        <p class="text-sm font-semibold text-blue-600">{{ $veiculo->placa_formatada }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Quilometragem</label>
                    <p class="text-sm text-gray-900">{{ number_format($veiculo->km) }} km</p>
                </div>
                @if($veiculo->chassi)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Chassi</label>
                        <p class="text-sm text-gray-900">{{ $veiculo->chassi }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Dados do Proprietário -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-user text-green-500 mr-2"></i>
                Proprietário
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $veiculo->cliente->nome }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</label>
                        <p class="text-sm text-gray-900">{{ $veiculo->cliente->cpf_formatado }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">RG</label>
                        <p class="text-sm text-gray-900">{{ $veiculo->cliente->rg ?: 'Não informado' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    @if($veiculo->cliente->telefone)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</label>
                            <p class="text-sm text-gray-900 flex items-center">
                                <i class="fas fa-phone text-gray-400 mr-2"></i>
                                {{ $veiculo->cliente->telefone }}
                            </p>
                        </div>
                    @endif
                    @if($veiculo->cliente->celular)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Celular</label>
                            <p class="text-sm text-gray-900 flex items-center">
                                <i class="fas fa-mobile-alt text-gray-400 mr-2"></i>
                                {{ $veiculo->cliente->celular }}
                            </p>
                        </div>
                    @endif
                </div>
                @if($veiculo->cliente->endereco)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</label>
                        <p class="text-sm text-gray-900">{{ $veiculo->cliente->endereco }}</p>
                    </div>
                @endif
                <div class="pt-2">
                    <a href="{{ route('clientes.show', $veiculo->cliente) }}" 
                       class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium hover:bg-green-200 transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Ver perfil completo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico de Ordens de Serviço -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history text-purple-500 mr-2"></i>
                Histórico de Ordens de Serviço ({{ $veiculo->ordensServico->count() }})
            </h3>
            <a href="{{ route('ordens.create') }}?veiculo_id={{ $veiculo->id }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Nova Ordem
            </a>
        </div>

        @if($veiculo->ordensServico->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Entrada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KM</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($veiculo->ordensServico as $ordem)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $ordem->numero_ordem }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($ordem->status === 'em_andamento' ? 'bg-orange-100 text-orange-800' : 
                                           ($ordem->status === 'concluida' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $ordem->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ordem->data_entrada->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($ordem->km_entrada ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('ordens.show', $ordem) }}" 
                                           class="text-indigo-600 hover:text-indigo-900" 
                                           title="Ver OS">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($ordem->status !== 'concluida')
                                            <a href="{{ route('ordens.edit', $ordem) }}" 
                                               class="text-orange-600 hover:text-orange-900" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('checklist.show', $ordem) }}" 
                                           class="text-purple-600 hover:text-purple-900" 
                                           title="Checklist">
                                            <i class="fas fa-search"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ordem de serviço</h3>
                <p class="text-gray-500 mb-4">Este veículo ainda não possui histórico de manutenção.</p>
                <a href="{{ route('ordens.create') }}?veiculo_id={{ $veiculo->id }}" 
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeira Ordem
                </a>
            </div>
        @endif
    </div>

    <!-- Estatísticas do Veículo -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-chart-bar text-yellow-500 mr-2"></i>
            Estatísticas do Veículo
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $veiculo->ordensServico->count() }}</div>
                <div class="text-sm text-blue-600">Ordens Totais</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $veiculo->ordensServico->where('status', 'concluida')->count() }}</div>
                <div class="text-sm text-green-600">OS Concluídas</div>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{ $veiculo->ordensServico->whereIn('status', ['aberta', 'em_andamento'])->count() }}</div>
                <div class="text-sm text-orange-600">OS Ativas</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">R$ {{ number_format($veiculo->ordensServico->sum('valor_total'), 0, ',', '.') }}</div>
                <div class="text-sm text-purple-600">Total Gasto</div>
            </div>
        </div>

        @if($veiculo->ordensServico->count() > 0)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Primeira OS:</span>
                        <span class="font-medium">{{ $veiculo->ordensServico->sortBy('created_at')->first()->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Última OS:</span>
                        <span class="font-medium">{{ $veiculo->ordensServico->sortByDesc('created_at')->first()->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Ticket Médio:</span>
                        <span class="font-medium">R$ {{ number_format($veiculo->ordensServico->avg('valor_total'), 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection