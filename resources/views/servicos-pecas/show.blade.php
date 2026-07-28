@extends('layouts.app')

@section('title', $servicoPeca->descricao)
@section('page-title', 'Detalhe do Item')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $servicoPeca->descricao }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ ucfirst($servicoPeca->tipo) }} · {{ $servicoPeca->ativo ? 'Ativo' : 'Inativo' }}</p>
            </div>
            <a href="{{ route('servicos-pecas.edit', $servicoPeca) }}" class="px-4 py-2 btn-primary text-white rounded-lg text-sm">Editar</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <div class="p-4 rounded-xl bg-gray-50">
                <div class="text-xs uppercase text-gray-500">Valor unitário</div>
                <div class="font-bold text-lg">R$ {{ number_format($servicoPeca->valor_unitario, 2, ',', '.') }}</div>
            </div>
            @if($servicoPeca->tipo === 'peca')
                <div class="p-4 rounded-xl bg-gray-50">
                    <div class="text-xs uppercase text-gray-500">Estoque</div>
                    <div class="font-bold text-lg">{{ $servicoPeca->quantidade_estoque }}</div>
                </div>
                <div class="p-4 rounded-xl bg-gray-50">
                    <div class="text-xs uppercase text-gray-500">Estoque mínimo</div>
                    <div class="font-bold text-lg">{{ $servicoPeca->estoque_minimo }}</div>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('servicos-pecas.index') }}" class="text-sm" style="color: var(--brand-primary)">← Voltar à lista</a>
</div>
@endsection
