@extends('layouts.portal-cliente')

@section('title', 'Consulta de Status - Portal do Cliente')

@section('content')
<div class="flex items-center justify-center py-12">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-full flex items-center justify-center mb-4 shadow-lg">
                <i class="fas fa-car text-white text-3xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Acompanhe seu Veículo</h2>
            <p class="mt-2 text-gray-600">Digite seu CPF para ver o andamento do serviço</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('portal.consulta-publica') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-2 text-orange-primary"></i>CPF
                    </label>
                    <input type="text" id="cpf" name="cpf" required maxlength="14"
                           placeholder="000.000.000-00"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent text-lg"
                           value="{{ old('cpf') }}" autofocus>
                    <p class="mt-2 text-sm text-gray-500">Digite apenas os números do seu CPF</p>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-orange-primary to-orange-secondary text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-search mr-2"></i>Consultar andamento
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>Seus dados estão seguros. Ambiente exclusivo do cliente.
            </div>
        </div>
    </div>
</div>
@endsection
