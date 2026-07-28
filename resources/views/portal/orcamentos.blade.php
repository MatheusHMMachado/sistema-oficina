@extends('layouts.portal-cliente')

@section('title', 'Orçamentos e Ordens')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Orçamentos e Serviços</h1>
        <p class="text-gray-600">Valores, peças e fotos das suas ordens de serviço.</p>
        <a href="{{ route('portal.dashboard') }}" class="text-sm font-medium mt-2 inline-block" style="color: var(--brand-primary)">← Voltar ao painel</a>
    </div>

    <div class="space-y-4">
        @forelse($ordens as $ordem)
            <div class="bg-white rounded-xl shadow p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-bold text-gray-900">OS {{ $ordem->numero_ordem }}</p>
                    <p class="text-sm text-gray-600">{{ $ordem->veiculo->placa_formatada ?? '' }} — {{ $ordem->etapa_cliente }}</p>
                    <p class="text-lg font-bold mt-1" style="color: var(--brand-primary)">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500">{{ $ordem->itens->count() }} item(ns) · {{ $ordem->checklist->sum(fn($c) => $c->fotos->count()) }} foto(s)</p>
                </div>
                <a href="{{ route('portal.ordem.detalhes', $ordem->id) }}"
                   class="px-5 py-2.5 btn-primary text-white rounded-lg text-sm font-semibold text-center">
                    Ver orçamento e fotos
                </a>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">
                Nenhuma ordem encontrada.
            </div>
        @endforelse
    </div>
</div>
@endsection
