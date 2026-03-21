@extends('layouts.app')

@section('title', 'Checklist - Sistema de Oficina')
@section('page-title', 'Checklist de Veículos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Checklist de Veículos</h1>
            <p class="text-gray-600">Realize inspeção visual e documente o estado dos veículos</p>
        </div>
    </div>

    <!-- Ordens Disponíveis -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($ordens->count() > 0)
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-clipboard-check text-purple-600 mr-2"></i>
                    Ordens Disponíveis para Checklist
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($ordens as $ordem)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
                            <!-- Header do Card -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ordem->status === 'aberta' ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $ordem->status)) }}
                                </span>
                                <span class="text-sm font-medium text-gray-600">OS: {{ $ordem->numero_ordem }}</span>
                            </div>

                            <!-- Dados do Cliente -->
                            <div class="mb-4">
                                <h4 class="font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-user text-gray-400 mr-2"></i>
                                    {{ $ordem->cliente->nome }}
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $ordem->cliente->telefone ?: $ordem->cliente->celular }}</p>
                            </div>

                            <!-- Dados do Veículo -->
                            <div class="mb-4">
                                <h4 class="font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-car text-gray-400 mr-2"></i>
                                    {{ $ordem->veiculo->descricao_completa }}
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Placa: {{ $ordem->veiculo->placa_formatada }} | 
                                    KM: {{ number_format($ordem->km_entrada ?? $ordem->veiculo->km) }}
                                </p>
                            </div>

                            <!-- Entrada -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                    Entrada: {{ $ordem->data_entrada->format('d/m/Y') }}
                                </p>
                            </div>

                            <!-- Status do Checklist -->
                            <div class="mb-4">
                                @if($ordem->checklist->count() > 0)
                                    <div class="flex items-center text-green-600">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span class="text-sm">{{ $ordem->checklist->count() }} ponto(s) inspecionado(s)</span>
                                    </div>
                                @else
                                    <div class="flex items-center text-gray-500">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span class="text-sm">Checklist pendente</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Observações -->
                            @if($ordem->observacoes)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-sticky-note text-gray-400 mr-2"></i>
                                        {{ Str::limit($ordem->observacoes, 60) }}
                                    </p>
                                </div>
                            @endif

                            <!-- Botão de Ação -->
                            <div class="flex justify-center">
                                <a href="{{ route('checklist.show', $ordem) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 w-full justify-center">
                                    <i class="fas fa-search mr-2"></i>
                                    {{ $ordem->checklist->count() > 0 ? 'Ver/Editar Checklist' : 'Iniciar Checklist' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-check text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma ordem disponível</h3>
                <p class="text-gray-500 mb-6">Não há ordens de serviço abertas ou em andamento para fazer checklist.</p>
                <a href="{{ route('ordens.create') }}" 
                   class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Nova OS
                </a>
            </div>
        @endif
    </div>
</div>
@endsection