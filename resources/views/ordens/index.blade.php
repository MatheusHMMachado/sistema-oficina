@extends('layouts.app')

@section('title', 'Ordens de Serviço - Sistema de Oficina')
@section('page-title', 'Ordens de Serviço')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ordens de Serviço</h1>
            <p class="text-gray-600">Gerencie todas as ordens de serviço da oficina</p>
        </div>
        <a href="{{ route('ordens.create') }}" 
           class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
            <i class="fas fa-plus mr-2"></i>
            Nova OS
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('ordens.index') }}" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Buscar por número, cliente ou placa..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>
            </div>
            <div>
                <select name="status" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                    <option value="">Todos os status</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('ordens.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-folder-open text-white text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-600">Abertas</p>
                    <p class="text-lg font-semibold text-yellow-900">{{ $statusCounts['aberta'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-orange-primary rounded-full flex items-center justify-center">
                    <i class="fas fa-cog text-white text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-orange-600">Em Andamento</p>
                    <p class="text-lg font-semibold text-orange-900">{{ $statusCounts['em_andamento'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-white text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-600">Concluídas</p>
                    <p class="text-lg font-semibold text-green-900">{{ $statusCounts['concluida'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-white text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-600">Canceladas</p>
                    <p class="text-lg font-semibold text-red-900">{{ $statusCounts['cancelada'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Ordens -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($ordens->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ordens as $ordem)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $ordem->numero_ordem }}</div>
                                    <div class="text-sm text-gray-500">{{ $ordem->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ordem->cliente)
                                        <div class="text-sm text-gray-900">{{ $ordem->cliente->nome }}</div>
                                        <div class="text-sm text-gray-500">{{ $ordem->cliente->telefone ?: $ordem->cliente->celular ?: 'Sem contato' }}</div>
                                    @else
                                        <div class="text-sm text-gray-500">Cliente não encontrado</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ordem->veiculo)
                                        <div class="text-sm text-gray-900">{{ $ordem->veiculo->descricao_completa }}</div>
                                        <div class="text-sm text-gray-500">{{ $ordem->veiculo->placa_formatada }}</div>
                                    @else
                                        <div class="text-sm text-gray-500">Veículo não encontrado</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ordem->status === 'aberta')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Aberta
                                        </span>
                                    @elseif($ordem->status === 'em_andamento')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                            Em Andamento
                                        </span>
                                    @elseif($ordem->status === 'concluida')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Concluída
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Cancelada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ordem->data_entrada->format('d/m/Y') }}</div>
                                    @if($ordem->data_saida)
                                        <div class="text-sm text-gray-500">Saída: {{ $ordem->data_saida->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('ordens.show', $ordem) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 transition-colors" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('ordens.edit', $ordem) }}" 
                                           class="text-orange-600 hover:text-orange-900 transition-colors" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('checklist.show', $ordem) }}" 
                                           class="text-purple-600 hover:text-purple-900 transition-colors" 
                                           title="Checklist">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        <button onclick="gerarPDF({{ $ordem->id }})" 
                                                class="text-red-600 hover:text-red-900 transition-colors" 
                                                title="PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($ordens->hasPages())
                <div class="bg-white px-6 py-3 border-t border-gray-200">
                    {{ $ordens->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-gray-400 text-3xl"></i>
                </div>
                @if(request()->hasAny(['search', 'status']))
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ordem encontrada</h3>
                    <p class="text-gray-500 mb-6">Não encontramos ordens com os filtros aplicados.</p>
                    <a href="{{ route('ordens.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>
                        Limpar Filtros
                    </a>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ordem de serviço</h3>
                    <p class="text-gray-500 mb-6">Comece criando a primeira ordem de serviço.</p>
                    <a href="{{ route('ordens.create') }}" 
                       class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Primeira OS
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    function gerarPDF(ordemId) {
        // Abrir PDF em nova aba
        window.open(`/ordens/${ordemId}/pdf`, '_blank');
    }
</script>
@endsection