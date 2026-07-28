@extends('layouts.app')

@section('title', 'Editar Cliente - Sistema de Oficina')
@section('page-title', 'Editar Cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-orange-primary to-orange-secondary px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-user-edit mr-3"></i>
                    Editar Cliente: {{ $cliente->nome }}
                </h2>
                <a href="{{ route('clientes.show', $cliente) }}" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('clientes.update', $cliente) }}" x-data="clienteForm()">
                @csrf
                @method('PUT')

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user text-orange-primary mr-2"></i>
                        Dados Pessoais
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="nome"
                                   name="nome"
                                   value="{{ old('nome', $cliente->nome) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('nome') border-red-500 @enderror">
                            @error('nome')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                                CPF <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="cpf"
                                   name="cpf"
                                   value="{{ old('cpf', $cliente->cpf_formatado) }}"
                                   x-model="cpf"
                                   @input="cpf = maskCPF($event.target.value)"
                                   maxlength="14"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('cpf') border-red-500 @enderror">
                            @error('cpf')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="rg" class="block text-sm font-medium text-gray-700 mb-2">RG</label>
                            <input type="text"
                                   id="rg"
                                   name="rg"
                                   value="{{ old('rg', $cliente->rg) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('rg') border-red-500 @enderror">
                            @error('rg')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-phone text-orange-primary mr-2"></i>
                        Dados de Contato
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                            <input type="text"
                                   id="telefone"
                                   name="telefone"
                                   value="{{ old('telefone', $cliente->telefone) }}"
                                   x-model="telefone"
                                   @input="telefone = maskPhone($event.target.value)"
                                   maxlength="15"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('telefone') border-red-500 @enderror">
                            @error('telefone')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="celular" class="block text-sm font-medium text-gray-700 mb-2">Celular</label>
                            <input type="text"
                                   id="celular"
                                   name="celular"
                                   value="{{ old('celular', $cliente->celular) }}"
                                   x-model="celular"
                                   @input="celular = maskPhone($event.target.value)"
                                   maxlength="15"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('celular') border-red-500 @enderror">
                            @error('celular')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $cliente->email) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">Endereço Completo</label>
                            <textarea id="endereco"
                                      name="endereco"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent @error('endereco') border-red-500 @enderror">{{ old('endereco', $cliente->endereco) }}</textarea>
                            @error('endereco')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('clientes.show', $cliente) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 btn-primary text-white rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function clienteForm() {
        return {
            cpf: @json(old('cpf', $cliente->cpf_formatado)),
            telefone: @json(old('telefone', $cliente->telefone)),
            celular: @json(old('celular', $cliente->celular)),

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
                }
                return cleanValue
                    .replace(/(\d{2})(\d)/, '($1) $2')
                    .replace(/(\d{5})(\d)/, '$1-$2');
            }
        }
    }
</script>
@endsection
