@extends('layouts.app')

@section('title', 'Relatório de Serviços')
@section('page-title', 'Relatório de Serviços')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Serviços e Peças</h1>
            <p class="text-gray-600">Desempenho operacional no período.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold">Serviços mais realizados</div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left">Serviço</th>
                    <th class="px-4 py-2 text-right">Qtd</th>
                    <th class="px-4 py-2 text-right">Faturado</th>
                </tr></thead>
                <tbody>
                    @forelse($servicosMaisRealizados as $row)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $row->servicoPeca->descricao ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $row->total }}</td>
                            <td class="px-4 py-2 text-right">R$ {{ number_format($row->faturado, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sem dados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold">Peças mais vendidas</div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left">Peça</th>
                    <th class="px-4 py-2 text-right">Qtd</th>
                    <th class="px-4 py-2 text-right">Faturado</th>
                </tr></thead>
                <tbody>
                    @forelse($pecasMaisVendidas as $row)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $row->servicoPeca->descricao ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $row->total }}</td>
                            <td class="px-4 py-2 text-right">R$ {{ number_format($row->faturado, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sem dados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-4 py-3 border-b font-semibold">Status das O.S. no período (por criação)</div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-right">Total</th>
            </tr></thead>
            <tbody>
                @forelse($statusOrdens as $row)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</td>
                        <td class="px-4 py-2 text-right">{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">Sem dados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('relatorios.index') }}" class="text-sm" style="color: var(--brand-primary)">← Voltar</a>
</div>
@endsection
