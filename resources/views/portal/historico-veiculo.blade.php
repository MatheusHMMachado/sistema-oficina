@extends('layouts.portal-cliente')

@section('title', 'Histórico do Veículo')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900">Histórico — {{ $veiculo->placa_formatada }}</h1>
        <p class="text-gray-600">{{ $veiculo->marca }} {{ $veiculo->modelo }} · {{ $veiculo->ano }} · {{ $veiculo->cor }}</p>
        <a href="{{ route('portal.dashboard') }}" class="inline-block mt-3 text-sm font-medium" style="color: var(--brand-primary)">← Voltar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        @forelse($historico as $ordem)
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="font-semibold text-gray-900">OS {{ $ordem->numero_ordem }}</p>
                    <p class="text-sm text-gray-500">Entrada: {{ optional($ordem->data_entrada)->format('d/m/Y') }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        Status: <span class="font-medium">{{ $ordem->etapa_cliente }}</span>
                    </p>
                    <p class="text-sm font-medium text-gray-900 mt-1">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('portal.ordem.detalhes', $ordem->id) }}"
                       class="px-4 py-2 rounded-lg text-white text-sm btn-primary">Ver detalhes</a>
                    @if($ordem->status === 'concluida' && !($ordem->avaliacaoNps ?? null))
                        <a href="{{ route('portal.ordem.detalhes', $ordem->id) }}#avaliacao"
                           class="px-4 py-2 rounded-lg border text-sm" style="border-color: var(--brand-primary); color: var(--brand-primary)">Avaliar serviço</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Nenhuma manutenção registrada para este veículo.</div>
        @endforelse
    </div>

    {{ $historico->links() }}
</div>
@endsection
