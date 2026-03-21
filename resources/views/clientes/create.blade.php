@extends('layouts.app')

@section('title', 'Novo Cliente - Sistema de Oficina')
@section('page-title', 'Novo Cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-primary to-orange-secondary px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-user-plus mr-3"></i>
                    Cadastrar Novo Cliente
                </h2>
                <a href="{{ route('clientes.index') }}" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6">
            <form method="POST" action="{{ route('clientes.store') }}" x-data="clienteForm()">
                @csrf

                <!-- Dados Pessoais -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user text-orange-primary mr-2"></i>
                        Dados Pessoais
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome -->
                        <div class="md:col-span-2">
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nome" 
                                   name="nome" 
                                   value="{{ old('nome') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('nome') border-red-500 @enderror"
                                   placeholder="Digite o nome completo do cliente">
                            @error('nome')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CPF -->
                        <div>
                            <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                                CPF <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="cpf" 
                                   name="cpf" 
                                   value="{{ old('cpf') }}"
                                   x-model="cpf"
                                   @input="cpf = maskCPF($event.target.value)"
                                   maxlength="14"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('cpf') border-red-500 @enderror"
                                   placeholder="000.000.000-00">
                            @error('cpf')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- RG -->
                        <div>
                            <label for="rg" class="block text-sm font-medium text-gray-700 mb-2">RG</label>
                            <input type="text" 
                                   id="rg" 
                                   name="rg" 
                                   value="{{ old('rg') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('rg') border-red-500 @enderror"
                                   placeholder="Digite o RG">
                            @error('rg')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Dados de Contato -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-phone text-orange-primary mr-2"></i>
                        Dados de Contato
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Telefone -->
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                            <input type="text" 
                                   id="telefone" 
                                   name="telefone" 
                                   value="{{ old('telefone') }}"
                                   x-model="telefone"
                                   @input="telefone = maskPhone($event.target.value)"
                                   maxlength="15"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('telefone') border-red-500 @enderror"
                                   placeholder="(00) 0000-0000">
                            @error('telefone')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Celular -->
                        <div>
                            <label for="celular" class="block text-sm font-medium text-gray-700 mb-2">Celular</label>
                            <input type="text" 
                                   id="celular" 
                                   name="celular" 
                                   value="{{ old('celular') }}"
                                   x-model="celular"
                                   @input="celular = maskPhone($event.target.value)"
                                   maxlength="15"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('celular') border-red-500 @enderror"
                                   placeholder="(00) 00000-0000">
                            @error('celular')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Endereço -->
                        <div class="md:col-span-2">
                            <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">Endereço Completo</label>
                            <textarea id="endereco" 
                                      name="endereco" 
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('endereco') border-red-500 @enderror"
                                      placeholder="Digite o endereço completo com rua, número, bairro, cidade e CEP">{{ old('endereco') }}</textarea>
                            @error('endereco')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informação sobre Portal de Consulta -->
                <div class="mb-8 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg p-6 border-2 border-orange-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-orange-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-globe text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-info-circle text-orange-primary mr-2"></i>
                                Portal de Consulta do Cliente
                            </h4>
                            <p class="text-gray-700 mb-3">
                                Após o cadastro, o cliente poderá consultar o status do seu veículo acessando o portal de consulta usando apenas o <strong>CPF</strong>.
                            </p>
                            <div class="bg-white rounded-lg p-4 mb-3">
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-link text-orange-primary mr-2"></i>
                                    <strong>Link do Portal:</strong>
                                </p>
                                <div class="flex items-center space-x-2">
                                    <code class="flex-1 px-3 py-2 bg-gray-100 rounded text-sm text-gray-800">
                                        {{ url('/portal/consulta') }}
                                    </code>
                                    <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ url('/portal/consulta') }}'); alert('Link copiado!');"
                                            class="px-3 py-2 bg-orange-primary text-white rounded hover:bg-orange-secondary transition-colors">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                                <span>O acesso é seguro e requer apenas o CPF do cliente</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('clientes.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Cadastrar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function clienteForm() {
        return {
            cpf: '{{ old('cpf') }}',
            telefone: '{{ old('telefone') }}',
            celular: '{{ old('celular') }}',

            maskCPF(value) {
                return value
                    .replace(/\D/g, '')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})/, '$1-$2')
                    .replace(/(-\d{2})\d+?$/, '$1');
            },

            maskPhone(value) {
                const cleanValue = value.replace(/\D/g, '');
                if (cleanValue.length <= 10) {
                    return cleanValue
                        .replace(/(\d{2})(\d)/, '($1) $2')
                        .replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    return cleanValue
                        .replace(/(\d{2})(\d)/, '($1) $2')
                        .replace(/(\d{5})(\d)/, '$1-$2');
                }
            }
        }
    }
</script>
@endsection