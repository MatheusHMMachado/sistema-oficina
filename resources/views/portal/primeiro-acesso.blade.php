@extends('layouts.portal-cliente')

@section('title', 'Criar senha de acesso - Portal do Cliente')

@section('content')
<div class="max-w-md mx-auto">
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
            <i class="fas fa-user-plus text-white text-3xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Criar senha de acesso</h2>
        <p class="text-gray-600 mt-2">Primeira vez? Informe CPF e e-mail cadastrados na oficina e defina sua senha.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8">
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                <span class="text-red-700 text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 font-medium text-sm mb-1">Corrija os erros abaixo:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/portal/primeiro-acesso') }}" class="space-y-5">
            @csrf

            <div>
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2 text-orange-primary"></i>CPF
                </label>
                <input type="text" id="cpf" name="cpf" required maxlength="14"
                       value="{{ old('cpf') }}"
                       placeholder="000.000.000-00"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">O mesmo CPF cadastrado na oficina</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-orange-primary"></i>E-mail
                </label>
                <input type="email" id="email" name="email" required
                       value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                       placeholder="seu@email.com">
                <p class="mt-1 text-xs text-gray-500">O mesmo e-mail cadastrado no seu cadastro de cliente</p>
            </div>

            <div>
                <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-orange-primary"></i>Nova senha
                </label>
                <input type="password" id="senha" name="senha" required minlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                       placeholder="Mínimo 6 caracteres">
            </div>

            <div>
                <label for="senha_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-orange-primary"></i>Confirmar senha
                </label>
                <input type="password" id="senha_confirmation" name="senha_confirmation" required minlength="6"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                       placeholder="Repita a senha">
            </div>

            <button type="submit" class="w-full py-3 bg-orange-primary text-white rounded-lg font-medium hover:bg-orange-secondary transition-colors">
                <i class="fas fa-check mr-2"></i>Criar senha e ativar acesso
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200 text-center">
            <a href="{{ route('portal.login') }}" class="text-orange-primary hover:text-orange-secondary text-sm font-medium">
                <i class="fas fa-arrow-left mr-1"></i>Voltar ao login
            </a>
        </div>
    </div>

    <p class="mt-6 text-center text-sm text-gray-500">
        Após criar a senha, você poderá acessar o portal com CPF ou e-mail e acompanhar o andamento dos seus veículos.
    </p>
</div>
@endsection
