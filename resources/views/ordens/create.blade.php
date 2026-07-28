@extends('layouts.app')

@section('title', 'Nova Ordem de Serviço - Sistema de Oficina')
@section('page-title', 'Nova Ordem de Serviço')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-primary to-orange-secondary px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    Nova Ordem de Serviço
                </h2>
                <a href="{{ route('ordens.index') }}" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6">
            <form method="POST" action="{{ route('ordens.store') }}" x-data="ordemForm()">
                @csrf

                <!-- Seleção de Cliente -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user text-orange-primary mr-2"></i>
                        Cliente
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione o Cliente <span class="text-red-500">*</span>
                            </label>
                            <select id="cliente_id" 
                                    name="cliente_id" 
                                    x-model="clienteId"
                                    @change="buscarVeiculos()"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('cliente_id') border-red-500 @enderror">
                                <option value="">Selecione o cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id', request('cliente_id')) == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome }} - {{ $cliente->cpf_formatado }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Seleção de Veículo -->
                <div class="mb-8" x-show="veiculos.length > 0" x-cloak>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-car text-orange-primary mr-2"></i>
                        Veículo
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="veiculo_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione o Veículo <span class="text-red-500">*</span>
                            </label>
                            <select id="veiculo_id" 
                                    name="veiculo_id" 
                                    :required="veiculos.length > 0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('veiculo_id') border-red-500 @enderror">
                                <option value="">Selecione o veículo</option>
                                <template x-for="veiculo in veiculos" :key="veiculo.id">
                                    <option :value="veiculo.id" x-text="`${veiculo.marca} ${veiculo.modelo} ${veiculo.cor} ${veiculo.ano} - ${veiculo.placa_formatada}`"></option>
                                </template>
                            </select>
                            @error('veiculo_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mb-8" x-show="clienteId && veiculos.length === 0" x-cloak>
                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 text-sm">
                        Este cliente não possui veículos cadastrados.
                        <a href="{{ route('veiculos.create') }}" class="font-semibold underline ml-1">Cadastrar veículo</a>
                        antes de abrir a O.S.
                    </div>
                </div>

                <!-- Dados da Ordem -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar text-orange-primary mr-2"></i>
                        Dados da Ordem
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Data de Entrada -->
                        <div>
                            <label for="data_entrada" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Entrada <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="data_entrada" 
                                   name="data_entrada" 
                                   value="{{ old('data_entrada', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('data_entrada') border-red-500 @enderror">
                            @error('data_entrada')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- KM de Entrada -->
                        <div>
                            <label for="km_entrada" class="block text-sm font-medium text-gray-700 mb-2">
                                KM de Entrada
                            </label>
                            <input type="number" 
                                   id="km_entrada" 
                                   name="km_entrada" 
                                   value="{{ old('km_entrada') }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('km_entrada') border-red-500 @enderror"
                                   placeholder="Ex: 85000">
                            @error('km_entrada')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observações -->
                        <div class="md:col-span-2">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                Observações Iniciais
                            </label>
                            <textarea id="observacoes" 
                                      name="observacoes" 
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('observacoes') border-red-500 @enderror"
                                      placeholder="Descreva o problema relatado pelo cliente ou observações importantes...">{{ old('observacoes') }}</textarea>
                            @error('observacoes')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('ordens.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Criar Ordem de Serviço
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function ordemForm() {
        return {
            clienteId: '{{ old('cliente_id', request('cliente_id')) }}',
            veiculos: [],

            init() {
                if (this.clienteId) {
                    this.buscarVeiculos();
                }
            },

            async buscarVeiculos() {
                if (!this.clienteId) {
                    this.veiculos = [];
                    return;
                }

                try {
                    const response = await fetch(`/api/clientes/${this.clienteId}/veiculos`);
                    this.veiculos = await response.json();
                } catch (error) {
                    console.error('Erro ao buscar veículos:', error);
                    this.veiculos = [];
                }
            }
        }
    }
</script>
@endsection