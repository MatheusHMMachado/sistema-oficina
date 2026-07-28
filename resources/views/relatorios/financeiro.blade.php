@extends('layouts.app')

@section('title', 'Relatório Financeiro')
@section('page-title', 'Relatório Financeiro')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Relatório Financeiro</h1>
            <p class="text-gray-600">O.S. concluídas no período selecionado.</p>
        </div>
        <form method="GET" class="flex gap-2 items-end">
            <div>
                <label class="text-xs text-gray-500">De</label>
                <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">Até</label>
                <input type="date" name="data_fim" value="{{ $dataFim }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <button class="px-4 py-2 btn-primary text-white rounded-lg text-sm">Filtrar</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-xs uppercase text-gray-500">Faturamento</div>
            <div class="text-2xl font-bold mt-1">R$ {{ number_format($totalFaturamento, 2, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-xs uppercase text-gray-500">O.S. concluídas</div>
            <div class="text-2xl font-bold mt-1">{{ $totalOrdens }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-xs uppercase text-gray-500">Ticket médio</div>
            <div class="text-2xl font-bold mt-1">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b font-semibold">Faturamento por mês</div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-2 text-left">Mês</th>
                <th class="px-4 py-2 text-right">Total</th>
            </tr></thead>
            <tbody>
                @forelse($faturamentoPorMes as $row)
                    <tr class="border-t"><td class="px-4 py-2">{{ $row->mes }}</td>
                    <td class="px-4 py-2 text-right">R$ {{ number_format($row->total, 2, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">Sem dados no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b font-semibold">Top itens</div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-2 text-left">Item</th>
                <th class="px-4 py-2 text-right">Qtd</th>
                <th class="px-4 py-2 text-right">Faturado</th>
            </tr></thead>
            <tbody>
                @forelse($topItens as $item)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $item->servicoPeca->descricao ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">{{ $item->total_vendido }}</td>
                        <td class="px-4 py-2 text-right">R$ {{ number_format($item->total_faturado, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sem itens no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('relatorios.index') }}" class="text-sm" style="color: var(--brand-primary)">← Voltar</a>
</div>
@endsection
