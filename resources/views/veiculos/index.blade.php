@extends('layouts.app')

@section('title', 'Veículos - Sistema de Oficina')
@section('page-title', 'Veículos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Veículos</h1>
            <p class="text-gray-600">Gerencie todos os veículos da oficina</p>
        </div>
        <a href="{{ route('veiculos.create') }}" 
           class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
            <i class="fas fa-plus mr-2"></i>
            Novo Veículo
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('veiculos.index') }}" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Buscar por placa, marca, modelo ou cliente..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                @if(request('search'))
                    <a href="{{ route('veiculos.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Veículos -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($veiculos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ano/KM</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($veiculos as $veiculo)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-car text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $veiculo->marca }} {{ $veiculo->modelo }}</div>
                                            <div class="text-sm text-gray-500">{{ $veiculo->cor }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $veiculo->cliente->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $veiculo->cliente->telefone ?: $veiculo->cliente->celular }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $veiculo->placa_formatada }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $veiculo->ano }}</div>
                                    <div class="text-gray-500">{{ number_format($veiculo->km) }} km</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('veiculos.show', $veiculo) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 transition-colors" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('veiculos.edit', $veiculo) }}" 
                                           class="text-orange-600 hover:text-orange-900 transition-colors" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('ordens.create') }}?veiculo_id={{ $veiculo->id }}" 
                                           class="text-green-600 hover:text-green-900 transition-colors" 
                                           title="Nova OS">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($veiculos->hasPages())
                <div class="bg-white px-6 py-3 border-t border-gray-200">
                    {{ $veiculos->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-car text-gray-400 text-3xl"></i>
                </div>
                @if(request('search'))
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum veículo encontrado</h3>
                    <p class="text-gray-500 mb-6">Não encontramos veículos para "{{ request('search') }}".</p>
                    <a href="{{ route('veiculos.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>
                        Limpar Busca
                    </a>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum veículo cadastrado</h3>
                    <p class="text-gray-500 mb-6">Comece cadastrando o primeiro veículo da oficina.</p>
                    <a href="{{ route('veiculos.create') }}" 
                       class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Cadastrar Primeiro Veículo
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection