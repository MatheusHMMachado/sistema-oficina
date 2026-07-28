@extends('layouts.portal-cliente')

@section('title', 'Portal do Cliente')

@section('content')
@php
    $abaInicial = request('entrar') || request('aba') === 'conta' ? 'conta' : 'consulta';
@endphp

<div class="max-w-lg mx-auto" x-data="{ aba: '{{ $abaInicial }}' }">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Portal do Cliente</h1>
        <p class="text-gray-600 mt-2">Um único acesso: consulte sua O.S., fotos, orçamento e agende serviços.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="flex border-b">
            <button type="button" @click="aba = 'consulta'"
                    class="flex-1 py-3 text-sm font-semibold transition-colors"
                    :class="aba === 'consulta' ? 'text-white' : 'text-gray-500 hover:bg-gray-50'"
                    :style="aba === 'consulta' ? 'background: var(--brand-primary)' : ''">
                <i class="fas fa-search mr-1"></i> Consultar O.S.
            </button>
            <button type="button" @click="aba = 'conta'"
                    class="flex-1 py-3 text-sm font-semibold transition-colors"
                    :class="aba === 'conta' ? 'text-white' : 'text-gray-500 hover:bg-gray-50'"
                    :style="aba === 'conta' ? 'background: var(--brand-primary)' : ''">
                <i class="fas fa-user mr-1"></i> Minha Conta
            </button>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            {{-- Consulta rápida por CPF (mesmo conteúdo de fotos/orçamento da O.S.) --}}
            <div x-show="aba === 'consulta'" x-cloak>
                <p class="text-sm text-gray-600 mb-4">Informe o CPF e a placa de um veículo cadastrado para consultar status, fotos e valores.</p>
                <form method="POST" action="{{ route('portal.consulta-publica') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                        <input type="text" name="cpf" value="{{ old('cpf', request('cpf')) }}" required
                               placeholder="000.000.000-00"
                               class="w-full px-4 py-3 border rounded-xl focus:ring-2"
                               style="--tw-ring-color: var(--brand-primary)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Placa do veículo</label>
                        <input type="text" name="placa" value="{{ old('placa') }}" required
                               placeholder="ABC1D23"
                               maxlength="8"
                               class="w-full px-4 py-3 border rounded-xl focus:ring-2 uppercase"
                               style="--tw-ring-color: var(--brand-primary)">
                    </div>
                    <button type="submit" class="w-full py-3 btn-primary text-white rounded-xl font-semibold">
                        Ver minhas ordens
                    </button>
                </form>
                <div class="mt-5 p-4 rounded-xl border border-dashed text-center" style="border-color: color-mix(in srgb, var(--brand-primary) 40%, transparent)">
                    <p class="text-sm font-semibold text-gray-800 mb-1">Quer agendar um serviço?</p>
                    <p class="text-xs text-gray-500 mb-3">Não precisa ter cadastro. Envie a solicitação e a oficina confirma.</p>
                    <a href="{{ route('portal.agendar') }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-white text-sm font-semibold btn-primary">
                        <i class="fas fa-calendar-check"></i> Agendar sem cadastro
                    </a>
                    <p class="text-xs text-gray-500 mt-3">
                        Já tem conta?
                        <button type="button" @click="aba = 'conta'" class="font-semibold underline" style="color: var(--brand-primary)">Entre aqui</button>
                        para usar seus veículos cadastrados.
                    </p>
                </div>
            </div>

            {{-- Login conta --}}
            <div x-show="aba === 'conta'" x-cloak>
                <p class="text-sm text-gray-600 mb-4">Entre com CPF ou e-mail e a senha de acesso do portal.</p>

                <div class="flex gap-2 mb-4 text-sm" x-data="{ tipo: 'cpf' }">
                    <button type="button" @click="tipo='cpf'; document.getElementById('form-login-cpf').classList.remove('hidden'); document.getElementById('form-login-email').classList.add('hidden')"
                            class="flex-1 py-2 rounded-lg border font-medium" id="btn-tipo-cpf">CPF</button>
                    <button type="button" @click="tipo='email'; document.getElementById('form-login-email').classList.remove('hidden'); document.getElementById('form-login-cpf').classList.add('hidden')"
                            class="flex-1 py-2 rounded-lg border font-medium text-gray-500" id="btn-tipo-email">E-mail</button>
                </div>

                <form id="form-login-cpf" method="POST" action="{{ route('portal.login') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="login_type" value="cpf">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                        <input type="text" name="cpf" value="{{ old('cpf') }}" required placeholder="000.000.000-00"
                               class="w-full px-4 py-3 border rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <input type="password" name="senha" required class="w-full px-4 py-3 border rounded-xl" placeholder="••••••••">
                    </div>
                    <button type="submit" class="w-full py-3 btn-primary text-white rounded-xl font-semibold">Entrar</button>
                </form>

                <form id="form-login-email" method="POST" action="{{ route('portal.login') }}" class="space-y-4 hidden">
                    @csrf
                    <input type="hidden" name="login_type" value="email">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="seu@email.com"
                               class="w-full px-4 py-3 border rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <input type="password" name="senha" required class="w-full px-4 py-3 border rounded-xl" placeholder="••••••••">
                    </div>
                    <button type="submit" class="w-full py-3 btn-primary text-white rounded-xl font-semibold">Entrar</button>
                </form>

                <div class="mt-5 pt-4 border-t text-center text-sm text-gray-600">
                    Primeira vez?
                    <a href="{{ route('portal.primeiro-acesso') }}" class="font-semibold" style="color: var(--brand-primary)">Criar senha de acesso</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
