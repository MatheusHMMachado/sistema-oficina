<!-- Crie o arquivo: resources/views/veiculos/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Editar Veículo - Sistema de Oficina')
@section('page-title', 'Editar Veículo')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-car mr-3"></i>
                    Editar Veículo: {{ $veiculo->marca }} {{ $veiculo->modelo }}
                </h2>
                <a href="{{ route('veiculos.index') }}" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6">
            <form method="POST" action="{{ route('veiculos.update', $veiculo) }}" x-data="veiculoForm()">
                @csrf
                @method('PUT')

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
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id', $veiculo->cliente_id) == $cliente->id ? 'selected' : '' }}>
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
                                   value="{{ old('marca', $veiculo->marca) }}"
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
                                   value="{{ old('modelo', $veiculo->modelo) }}"
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
                                   value="{{ old('cor', $veiculo->cor) }}"
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
                                   value="{{ old('ano', $veiculo->ano) }}"
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
                                   value="{{ old('placa', $veiculo->placa) }}"
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
                                   value="{{ old('km', $veiculo->km) }}"
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
                                   value="{{ old('chassi', $veiculo->chassi) }}"
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
                    
                    <div class="flex space-x-3">
                        <button type="button"
                                @click="if(confirm('Tem certeza que deseja excluir este veículo?')) { document.getElementById('delete-form').submit(); }"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Excluir
                        </button>
                        
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>
                            Atualizar Veículo
                        </button>
                    </div>
                </div>
            </form>

            <!-- Form de Delete (Hidden) -->
            <form id="delete-form" method="POST" action="{{ route('veiculos.destroy', $veiculo) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <!-- Histórico de Ordens do Veículo -->
    @if($veiculo->ordensServico->count() > 0)
        <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-history text-indigo-500 mr-2"></i>
                    Histórico de Ordens de Serviço ({{ $veiculo->ordensServico->count() }})
                </h3>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($veiculo->ordensServico->take(5) as $ordem)
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('ordens.show', $ordem) }}" 
                                           class="text-indigo-600 hover:text-indigo-900" 
                                           title="Ver OS">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($veiculo->ordensServico->count() > 5)
                    <div class="mt-4">
                        <a href="{{ route('ordens.index') }}?search={{ $veiculo->placa }}" 
                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            Ver todas as {{ $veiculo->ordensServico->count() }} ordens <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
    function veiculoForm() {
        return {
            placa: '{{ old('placa', $veiculo->placa) }}',

            init() {
                // Aplicar máscara na placa se já tiver valor
                if (this.placa) {
                    this.placa = this.maskPlaca(this.placa);
                }
            },

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