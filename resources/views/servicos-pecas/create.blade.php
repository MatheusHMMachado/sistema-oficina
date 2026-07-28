@extends('layouts.app')

@section('title', 'Novo Item - Sistema de Oficina')
@section('page-title', 'Novo Serviço/Peça')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-cogs mr-3"></i>
                    Cadastrar Novo Item
                </h2>
                <a href="{{ route('servicos-pecas.index') }}" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6">
            <form method="POST" action="{{ route('servicos-pecas.store') }}" x-data="itemForm()">
                @csrf

                <!-- Tipo do Item -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-tag text-purple-500 mr-2"></i>
                        Tipo do Item
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" 
                               :class="tipo === 'servico' ? 'border-purple-500 bg-purple-50' : 'border-gray-300'">
                            <input type="radio" 
                                   name="tipo" 
                                   value="servico" 
                                   x-model="tipo"
                                   required
                                   class="sr-only">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-wrench text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Serviço</div>
                                    <div class="text-sm text-gray-500">Mão de obra, alinhamento, balanceamento</div>
                                </div>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="tipo === 'peca' ? 'border-purple-500 bg-purple-50' : 'border-gray-300'">
                            <input type="radio" 
                                   name="tipo" 
                                   value="peca" 
                                   x-model="tipo"
                                   required
                                   class="sr-only">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-cog text-purple-600 text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Peça</div>
                                    <div class="text-sm text-gray-500">Filtros, óleo, pastilhas, pneus</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('tipo')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dados do Item -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                        Dados do Item
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Descrição -->
                        <div class="md:col-span-2">
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="descricao" 
                                   name="descricao" 
                                   value="{{ old('descricao') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('descricao') border-red-500 @enderror"
                                   placeholder="Ex: Troca de óleo, Filtro de ar, Balanceamento">
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor Unitário -->
                        <div>
                            <label for="valor_unitario" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Unitário <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input type="number" 
                                       id="valor_unitario" 
                                       name="valor_unitario" 
                                       value="{{ old('valor_unitario') }}"
                                       step="0.01"
                                       min="0"
                                       required
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('valor_unitario') border-red-500 @enderror"
                                       placeholder="0,00">
                            </div>
                            @error('valor_unitario')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="tipo === 'peca'" x-cloak class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade em Estoque</label>
                                <input type="number" name="quantidade_estoque" min="0" value="{{ old('quantidade_estoque', 0) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estoque Mínimo (ponto de pedido)</label>
                                <input type="number" name="estoque_minimo" min="0" value="{{ old('estoque_minimo', 0) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center">
                            <label for="ativo" class="flex items-center cursor-pointer">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" 
                                       id="ativo" 
                                       name="ativo" 
                                       value="1"
                                       {{ old('ativo', '1') == '1' ? 'checked' : '' }}
                                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Item ativo</span>
                            </label>
                            <div class="ml-2">
                                <i class="fas fa-info-circle text-gray-400" title="Itens ativos aparecem na lista de seleção das ordens de serviço"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('servicos-pecas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Cadastrar Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function itemForm() {
        return {
            tipo: '{{ old('tipo', 'servico') }}'
        }
    }
</script>
@endsection