@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Oficina')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Clientes -->
        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-orange-primary group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Clientes</p>
                        <p class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">{{ number_format($dados['total_clientes']) }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 font-semibold">Ativos</span>
                </p>
            </div>
        </div>

        <!-- Total Veículos -->
        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-blue-500 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-car text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Veículos</p>
                        <p class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">{{ number_format($dados['total_veiculos']) }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-car-side text-blue-500 mr-1"></i>
                    <span class="text-blue-600 font-semibold">Cadastrados</span>
                </p>
            </div>
        </div>

        <!-- Ordens Abertas -->
        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-yellow-500 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-amber-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Ordens Abertas</p>
                        <p class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">{{ number_format($dados['ordens_abertas']) }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-hourglass-half text-yellow-500 mr-1"></i>
                    <span class="text-yellow-600 font-semibold">Em espera</span>
                </p>
            </div>
        </div>

        <!-- Faturamento do Mês -->
        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-green-500 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-dollar-sign text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Faturamento Mês</p>
                        <p class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">R$ {{ number_format($dados['faturamento_mes'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-chart-line text-green-500 mr-1"></i>
                    <span class="text-green-600 font-semibold">Este mês</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="card-modern rounded-2xl shadow-xl p-8">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-xl flex items-center justify-center mr-3 shadow-lg">
                    <i class="fas fa-bolt text-white"></i>
                </div>
                Ações Rápidas
            </h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('clientes.create') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-2xl border-2 border-blue-200 hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-2 shadow-lg hover:shadow-2xl">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-900 mb-1">Novo Cliente</p>
                <p class="text-xs text-gray-600 text-center">Cadastrar cliente</p>
            </a>

            <a href="{{ route('veiculos.create') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-green-50 to-emerald-100 hover:from-green-100 hover:to-emerald-200 rounded-2xl border-2 border-green-200 hover:border-green-300 transition-all duration-300 transform hover:-translate-y-2 shadow-lg hover:shadow-2xl">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-car text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-900 mb-1">Novo Veículo</p>
                <p class="text-xs text-gray-600 text-center">Cadastrar veículo</p>
            </a>

            <a href="{{ route('ordens.create') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-orange-50 to-amber-100 hover:from-orange-100 hover:to-amber-200 rounded-2xl border-2 border-orange-200 hover:border-orange-300 transition-all duration-300 transform hover:-translate-y-2 shadow-lg hover:shadow-2xl">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-plus text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-900 mb-1">Nova OS</p>
                <p class="text-xs text-gray-600 text-center">Ordem de serviço</p>
            </a>

            <a href="{{ route('servicos-pecas.create') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-violet-100 hover:from-purple-100 hover:to-violet-200 rounded-2xl border-2 border-purple-200 hover:border-purple-300 transition-all duration-300 transform hover:-translate-y-2 shadow-lg hover:shadow-2xl">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-cogs text-white text-2xl"></i>
                </div>
                <p class="font-bold text-gray-900 mb-1">Novo Item</p>
                <p class="text-xs text-gray-600 text-center">Serviço ou peça</p>
            </a>
        </div>
    </div>

    <!-- Ordens Recentes -->
    @if($dados['ordens_recentes']->count() > 0)
        <div class="card-modern rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list text-orange-primary mr-2"></i>
                    Ordens de Serviço Recentes
                </h3>
                <a href="{{ route('ordens.index') }}" class="text-orange-primary hover:text-orange-secondary text-sm font-medium">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($dados['ordens_recentes'] as $ordem)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $ordem->numero_ordem }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $ordem->cliente->nome }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $ordem->veiculo->descricao_completa }}</span>
                                    <br>
                                    <span class="text-xs text-gray-500">{{ $ordem->veiculo->placa_formatada }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($ordem->status === 'em_andamento' ? 'bg-orange-100 text-orange-800' : 
                                           ($ordem->status === 'concluida' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $ordem->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">
                                        R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Estado Vazio -->
        <div class="card-modern rounded-2xl shadow-xl p-12 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Nenhuma ordem de serviço ainda</h3>
            <p class="text-gray-600 mb-8 text-lg">Comece criando a primeira ordem de serviço da sua oficina.</p>
            <a href="{{ route('ordens.create') }}" class="inline-flex items-center px-8 py-4 btn-primary text-white rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl transition-all duration-300">
                <i class="fas fa-plus mr-3"></i>
                Criar Primeira OS
            </a>
        </div>
    @endif
</div>
@endsection