<!-- Crie o arquivo: resources/views/clientes/show.blade.php -->
@extends('layouts.app')

@section('title', $cliente->nome . ' - Sistema de Oficina')
@section('page-title', 'Visualizar Cliente')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-orange-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $cliente->nome }}</h1>
                    <p class="text-gray-600">Cliente desde {{ $cliente->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('clientes.edit', $cliente) }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                <a href="{{ route('ordens.create') }}?cliente_id={{ $cliente->id }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Nova OS
                </a>
                <a href="{{ route('clientes.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Informações do Cliente -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Dados Pessoais -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-id-card text-blue-500 mr-2"></i>
                Dados Pessoais
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Nome Completo</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $cliente->nome }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</label>
                        <p class="text-sm text-gray-900">{{ $cliente->cpf_formatado }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">RG</label>
                        <p class="text-sm text-gray-900">{{ $cliente->rg ?: 'Não informado' }}</p>
                    </div>
                </div>
                @if($cliente->endereco)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</label>
                        <p class="text-sm text-gray-900">{{ $cliente->endereco }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Dados de Contato -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-phone text-green-500 mr-2"></i>
                Dados de Contato
            </h3>
            <div class="space-y-4">
                @if($cliente->telefone)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</label>
                        <p class="text-sm text-gray-900 flex items-center">
                            <i class="fas fa-phone text-gray-400 mr-2"></i>
                            {{ $cliente->telefone }}
                        </p>
                    </div>
                @endif
                @if($cliente->celular)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Celular</label>
                        <p class="text-sm text-gray-900 flex items-center">
                            <i class="fas fa-mobile-alt text-gray-400 mr-2"></i>
                            {{ $cliente->celular }}
                        </p>
                    </div>
                @endif
                @if(!$cliente->telefone && !$cliente->celular)
                    <div class="text-center py-4">
                        <i class="fas fa-phone-slash text-gray-300 text-2xl mb-2"></i>
                        <p class="text-gray-500">Nenhum contato cadastrado</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Veículos do Cliente -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-car text-purple-500 mr-2"></i>
                Veículos Cadastrados ({{ $cliente->veiculos->count() }})
            </h3>
            <a href="{{ route('veiculos.create') }}?cliente_id={{ $cliente->id }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Adicionar Veículo
            </a>
        </div>

        @if($cliente->veiculos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cliente->veiculos as $veiculo)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $veiculo->placa_formatada }}
                            </span>
                            <div class="flex space-x-2">
                                <a href="{{ route('veiculos.edit', $veiculo) }}" 
                                   class="text-orange-600 hover:text-orange-800 text-sm" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('ordens.create') }}?veiculo_id={{ $veiculo->id }}" 
                                   class="text-green-600 hover:text-green-800 text-sm" 
                                   title="Nova OS">
                                    <i class="fas fa-plus-circle"></i>
                                </a>
                            </div>
                        </div>
                        <h4 class="font-semibold text-gray-900 text-lg">{{ $veiculo->marca }} {{ $veiculo->modelo }}</h4>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Cor:</span> {{ $veiculo->cor }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Ano:</span> {{ $veiculo->ano }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">KM:</span> {{ number_format($veiculo->km) }}
                            </p>
                            @if($veiculo->chassi)
                                <p class="text-xs text-gray-500">
                                    <span class="font-medium">Chassi:</span> {{ $veiculo->chassi }}
                                </p>
                            @endif
                        </div>
                        
                        <!-- Últimas Ordens -->
                        @if($veiculo->ordensServico->count() > 0)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-500 mb-2">Última OS:</p>
                                @php $ultimaOrdem = $veiculo->ordensServico->first() @endphp
                                <div class="text-xs">
                                    <span class="font-medium">{{ $ultimaOrdem->numero_ordem }}</span>
                                    <span class="text-gray-500">- {{ $ultimaOrdem->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-car text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum veículo cadastrado</h3>
                <p class="text-gray-500 mb-4">Este cliente ainda não possui veículos cadastrados.</p>
                <a href="{{ route('veiculos.create') }}?cliente_id={{ $cliente->id }}" 
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Cadastrar Primeiro Veículo
                </a>
            </div>
        @endif
    </div>

    <!-- Histórico de Ordens de Serviço -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history text-indigo-500 mr-2"></i>
                Histórico de Ordens de Serviço ({{ $cliente->ordensServico->count() }})
            </h3>
            <a href="{{ route('ordens.create') }}?cliente_id={{ $cliente->id }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Nova Ordem
            </a>
        </div>

        @if($cliente->ordensServico->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($cliente->ordensServico->take(10) as $ordem)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $ordem->numero_ordem }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ordem->veiculo->marca }} {{ $ordem->veiculo->modelo }}</div>
                                    <div class="text-sm text-gray-500">{{ $ordem->veiculo->placa_formatada }}</div>
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
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($cliente->ordensServico->count() > 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('ordens.index') }}?search={{ $cliente->nome }}" 
                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                        Ver todas as {{ $cliente->ordensServico->count() }} ordens <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ordem de serviço</h3>
                <p class="text-gray-500 mb-4">Este cliente ainda não possui ordens de serviço.</p>
                <a href="{{ route('ordens.create') }}?cliente_id={{ $cliente->id }}" 
                   class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeira Ordem
                </a>
            </div>
        @endif
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-chart-bar text-yellow-500 mr-2"></i>
            Estatísticas do Cliente
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $cliente->veiculos->count() }}</div>
                <div class="text-sm text-blue-600">Veículos</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $cliente->ordensServico->where('status', 'concluida')->count() }}</div>
                <div class="text-sm text-green-600">OS Concluídas</div>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{ $cliente->ordensServico->whereIn('status', ['aberta', 'em_andamento'])->count() }}</div>
                <div class="text-sm text-orange-600">OS Ativas</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">R$ {{ number_format($cliente->ordensServico->sum('valor_total'), 0, ',', '.') }}</div>
                <div class="text-sm text-purple-600">Total Gasto</div>
            </div>
        </div>
    </div>
</div>
@endsection