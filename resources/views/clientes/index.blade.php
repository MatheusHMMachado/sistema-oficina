@extends('layouts.app')

@section('title', 'Clientes - Sistema de Oficina')
@section('page-title', 'Clientes')

@section('content')
<div class="space-y-6">
    <!-- Header com Botão Novo -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
            <p class="text-gray-600">Gerencie todos os clientes da oficina</p>
        </div>
        <a href="{{ route('clientes.create') }}" 
           class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
            <i class="fas fa-plus mr-2"></i>
            Novo Cliente
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Buscar por nome, CPF ou telefone..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                @if(request()->hasAny(['search']))
                    <a href="{{ route('clientes.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Clientes -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($clientes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documentos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veículos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($clientes as $cliente)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-orange-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $cliente->nome }}</div>
                                            @if($cliente->endereco)
                                                <div class="text-sm text-gray-500">{{ Str::limit($cliente->endereco, 40) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $cliente->cpf_formatado ?? $cliente->cpf }}</div>
                                    @if($cliente->rg)
                                        <div class="text-sm text-gray-500">RG: {{ $cliente->rg }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($cliente->telefone)
                                        <div class="text-sm text-gray-900 flex items-center">
                                            <i class="fas fa-phone text-gray-400 mr-2"></i>
                                            {{ $cliente->telefone }}
                                        </div>
                                    @endif
                                    @if($cliente->celular)
                                        <div class="text-sm text-gray-900 flex items-center">
                                            <i class="fas fa-mobile-alt text-gray-400 mr-2"></i>
                                            {{ $cliente->celular }}
                                        </div>
                                    @endif
                                    @if(!$cliente->telefone && !$cliente->celular)
                                        <span class="text-sm text-gray-400">Sem contato</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($cliente->veiculos)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $cliente->veiculos->count() }} veículo(s)
                                            </span>
                                        @else
                                            <span class="text-gray-400">Nenhum</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('clientes.show', $cliente) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 transition-colors" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('clientes.edit', $cliente) }}" 
                                           class="text-orange-600 hover:text-orange-900 transition-colors" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('portal.home', ['cpf' => preg_replace('/[^0-9]/', '', $cliente->cpf)]) }}" 
                                           target="_blank"
                                           class="text-green-600 hover:text-green-900 transition-colors" 
                                           title="Ver Portal do Cliente">
                                            <i class="fas fa-globe"></i>
                                        </a>
                                        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Tem certeza que deseja excluir este cliente?')"
                                                    class="text-red-600 hover:text-red-900 transition-colors" 
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($clientes->hasPages())
                <div class="bg-white px-6 py-3 border-t border-gray-200">
                    {{ $clientes->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-3xl"></i>
                </div>
                @if(request()->hasAny(['search']))
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum cliente encontrado</h3>
                    <p class="text-gray-500 mb-6">Não encontramos clientes com os filtros aplicados.</p>
                    <a href="{{ route('clientes.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>
                        Limpar Filtros
                    </a>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum cliente cadastrado</h3>
                    <p class="text-gray-500 mb-6">Comece cadastrando o primeiro cliente da oficina.</p>
                    <a href="{{ route('clientes.create') }}" 
                       class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Cadastrar Primeiro Cliente
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection