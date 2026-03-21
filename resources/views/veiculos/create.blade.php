@extends('layouts.app')

@section('title', 'Novo Veículo - Sistema de Oficina')
@section('page-title', 'Novo Veículo')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-car mr-3"></i>
                    Cadastrar Novo Veículo
                </h2>
                <a href="{{ route('veiculos.index') }}" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6">
            <form method="POST" action="{{ route('veiculos.store') }}" x-data="veiculoForm()">
                @csrf

                <!-- Cliente -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Cliente
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <select id="cliente_id" 
                                    name="cliente_id" 
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cliente_id') border-red-500 @enderror">
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

                <!-- Dados do Veículo -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-car text-blue-500 mr-2"></i>
                        Dados do Veículo
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Marca -->
                        <div>
                            <label for="marca" class="block text-sm font-medium text-gray-700 mb-2">
                                Marca <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="marca" 
                                   name="marca" 
                                   value="{{ old('marca') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('marca') border-red-500 @enderror"
                                   placeholder="Ex: Volkswagen, Chevrolet, Ford">
                            @error('marca')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Modelo -->
                        <div>
                            <label for="modelo" class="block text-sm font-medium text-gray-700 mb-2">
                                Modelo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="modelo" 
                                   name="modelo" 
                                   value="{{ old('modelo') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('modelo') border-red-500 @enderror"
                                   placeholder="Ex: Gol, Onix, Ka">
                            @error('modelo')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cor -->
                        <div>
                            <label for="cor" class="block text-sm font-medium text-gray-700 mb-2">
                                Cor <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="cor" 
                                   name="cor" 
                                   value="{{ old('cor') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cor') border-red-500 @enderror"
                                   placeholder="Ex: Branco, Prata, Azul">
                            @error('cor')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ano -->
                        <div>
                            <label for="ano" class="block text-sm font-medium text-gray-700 mb-2">
                                Ano <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="ano" 
                                   name="ano" 
                                   value="{{ old('ano', date('Y')) }}"
                                   min="1900"
                                   max="{{ date('Y') + 1 }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ano') border-red-500 @enderror"
                                   placeholder="Ex: {{ date('Y') }}">
                            @error('ano')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Placa -->
                        <div>
                            <label for="placa" class="block text-sm font-medium text-gray-700 mb-2">
                                Placa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="placa" 
                                   name="placa" 
                                   value="{{ old('placa') }}"
                                   x-model="placa"
                                   @input="placa = maskPlaca($event.target.value)"
                                   maxlength="8"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('placa') border-red-500 @enderror"
                                   placeholder="ABC1234">
                            @error('placa')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- KM -->
                        <div>
                            <label for="km" class="block text-sm font-medium text-gray-700 mb-2">Quilometragem</label>
                            <input type="number" 
                                   id="km" 
                                   name="km" 
                                   value="{{ old('km', 0) }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('km') border-red-500 @enderror"
                                   placeholder="Ex: 50000">
                            @error('km')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Chassi -->
                        <div class="md:col-span-2">
                            <label for="chassi" class="block text-sm font-medium text-gray-700 mb-2">Chassi</label>
                            <input type="text" 
                                   id="chassi" 
                                   name="chassi" 
                                   value="{{ old('chassi') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('chassi') border-red-500 @enderror"
                                   placeholder="Digite o número do chassi">
                            @error('chassi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('veiculos.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Cadastrar Veículo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function veiculoForm() {
        return {
            placa: '{{ old('placa') }}',

            maskPlaca(value) {
                // Remove tudo que não é letra ou número
                let clean = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                
                // Formato antigo: ABC1234
                if (clean.length <= 7) {
                    return clean.replace(/^([A-Z]{0,3})([0-9]{0,4}).*/, '$1$2');
                }
                
                // Formato Mercosul: ABC1D23
                return clean.replace(/^([A-Z]{0,3})([0-9]{0,1})([A-Z]{0,1})([0-9]{0,2}).*/, '$1$2$3$4');
            }
        }
    }
</script>
@endsection