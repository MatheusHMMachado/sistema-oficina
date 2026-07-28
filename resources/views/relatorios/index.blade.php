@extends('layouts.app')

@section('title', 'Relatórios')
@section('page-title', 'Relatórios')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Central de Relatórios</h1>
        <p class="text-gray-600">Acompanhe faturamento, serviços e clientes sem alterar os cadastros.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('relatorios.financeiro') }}" class="bg-white rounded-xl shadow p-6 hover:ring-2 transition" style="--tw-ring-color: var(--brand-primary)">
            <div class="text-sm text-gray-500 uppercase font-semibold">Financeiro</div>
            <div class="mt-2 font-bold text-gray-900">Faturamento e ticket médio</div>
        </a>
        <a href="{{ route('relatorios.servicos') }}" class="bg-white rounded-xl shadow p-6 hover:ring-2 transition" style="--tw-ring-color: var(--brand-primary)">
            <div class="text-sm text-gray-500 uppercase font-semibold">Serviços</div>
            <div class="mt-2 font-bold text-gray-900">Serviços e peças mais realizados</div>
        </a>
        <a href="{{ route('relatorios.clientes') }}" class="bg-white rounded-xl shadow p-6 hover:ring-2 transition" style="--tw-ring-color: var(--brand-primary)">
            <div class="text-sm text-gray-500 uppercase font-semibold">Clientes</div>
            <div class="mt-2 font-bold text-gray-900">Top clientes e frequência</div>
        </a>
    </div>
</div>
@endsection
