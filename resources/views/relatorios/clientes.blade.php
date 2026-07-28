@extends('layouts.app')

@section('title', 'Relatório de Clientes')
@section('page-title', 'Relatório de Clientes')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
            <p class="text-gray-600">Ranking e frequência no período.</p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-xs uppercase text-gray-500">Total de clientes</div>
            <div class="text-2xl font-bold mt-1">{{ $totalClientes }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-xs uppercase text-gray-500">Novos no período</div>
            <div class="text-2xl font-bold mt-1">{{ $novosClientes }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold">Top por faturamento</div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left">Cliente</th>
                    <th class="px-4 py-2 text-right">O.S.</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr></thead>
                <tbody>
                    @forelse($topClientes as $row)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $row->cliente->nome ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $row->total_ordens }}</td>
                            <td class="px-4 py-2 text-right">R$ {{ number_format($row->total_faturado, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sem dados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold">Mais frequentes</div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left">Cliente</th>
                    <th class="px-4 py-2 text-right">Visitas</th>
                </tr></thead>
                <tbody>
                    @forelse($clientesFrequentes as $row)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $row->cliente->nome ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $row->total_visitas }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">Sem dados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('relatorios.index') }}" class="text-sm" style="color: var(--brand-primary)">← Voltar</a>
</div>
@endsection
