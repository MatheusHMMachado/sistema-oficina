@extends('layouts.app')

@section('title', 'Controle Financeiro - Sistema de Oficina')
@section('page-title', 'Controle Financeiro')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Controle Financeiro</h1>
            <p class="text-gray-600">
                Competência do mês
                <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $mesRelatorio)->format('m/Y') }}</strong>
                — histórico antigo permanece salvo; o painel mostra só o período selecionado.
            </p>
        </div>
        <div class="flex flex-wrap items-end gap-2">
            <form method="GET" action="{{ route('financeiro.index') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Competência (mês)</label>
                    <input type="month" name="mes" value="{{ $mesRelatorio }}"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                           onchange="this.form.submit()">
                </div>
            </form>
            <form method="GET" action="{{ route('financeiro.relatorio-mensal') }}" class="flex items-end">
                <input type="hidden" name="mes" value="{{ $mesRelatorio }}">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-lg font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Gerar relatório PDF
                </button>
            </form>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-green-500">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-coins text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Valor Bruto Total</p>
                    <p class="text-2xl font-bold text-green-700">R$ {{ number_format($valorBruto, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Serviços pagos arrecadados</p>
                </div>
            </div>
        </div>

        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-orange-primary">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-balance-scale text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Valor Líquido Real</p>
                    <p class="text-2xl font-bold {{ $valorLiquido >= 0 ? 'text-orange-primary' : 'text-red-600' }}">
                        R$ {{ number_format($valorLiquido, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Bruto − Despesas</p>
                </div>
            </div>
        </div>

        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-red-500">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-arrow-down text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total de Despesas</p>
                    <p class="text-2xl font-bold text-red-600">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Saídas registradas</p>
                </div>
            </div>
        </div>

        <div class="card-modern rounded-2xl shadow-xl p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-clipboard-check text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Serviços Prestados</p>
                        <div class="flex items-center space-x-4 mt-1">
                            <div>
                                <p class="text-lg font-bold text-green-600">{{ $servicosPagos }}</p>
                                <p class="text-xs text-gray-500">Pagos</p>
                            </div>
                            <div class="w-px h-8 bg-gray-200"></div>
                            <div>
                                <p class="text-lg font-bold text-amber-600">{{ $servicosPendentes }}</p>
                                <p class="text-xs text-gray-500">Pendentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráfico --}}
    <div class="card-modern rounded-2xl shadow-xl p-6">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-orange-primary to-orange-secondary rounded-xl flex items-center justify-center mr-3 shadow-lg">
                <i class="fas fa-chart-bar text-white"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900">Gestão Financeira</h3>
                <p class="text-sm text-gray-500">Comparativo entre receita bruta e gastos totais</p>
            </div>
        </div>
        <div class="relative" style="height: 280px;">
            <canvas id="graficoFinanceiro"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Formulário de despesas --}}
        <div class="xl:col-span-1 card-modern rounded-2xl shadow-xl p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                    <i class="fas fa-file-invoice-dollar text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Nova Despesa</h3>
                    <p class="text-sm text-gray-500">Contas a pagar / saídas</p>
                </div>
            </div>

            <form method="POST" action="{{ route('financeiro.despesas.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do gasto</label>
                    <input type="text" name="descricao" value="{{ old('descricao') }}" required
                           placeholder="Ex: Conta de energia elétrica"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                    <input type="number" name="valor" value="{{ old('valor') }}" step="0.01" min="0.01" required
                           placeholder="0,00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria do Gasto</label>
                    <select name="categoria" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                        <option value="">Selecione...</option>
                        @foreach($categorias as $key => $label)
                            <option value="{{ $key }}" {{ old('categoria') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data da despesa</label>
                    <input type="date" name="data_despesa" value="{{ old('data_despesa', date('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Registrar Despesa
                </button>
            </form>
        </div>

        {{-- Extrato de despesas --}}
        <div class="xl:col-span-2 card-modern rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-700 to-gray-900 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-receipt text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Extrato de Despesas</h3>
                        <p class="text-sm text-gray-500">Histórico de descontos no caixa</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Categoria</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($despesas as $despesa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ optional($despesa->data_despesa)->format('d/m/Y') ?? $despesa->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $despesa->descricao }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700">
                                        {{ $despesa->categoria_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 text-right">
                                    − R$ {{ number_format($despesa->valor, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form method="POST" action="{{ route('financeiro.despesas.destroy', $despesa) }}"
                                          onsubmit="return confirm('Remover esta despesa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                                    <p>Nenhuma despesa registrada ainda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($despesas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $despesas->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Tabela de serviços / entradas --}}
    <div class="card-modern rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-arrow-up text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Controle de Serviços (Entradas)</h3>
                        <p class="text-sm text-gray-500">Ordens de serviço e status de pagamento</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID / Nº OS</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status OS</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor Total</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Pagamento</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Alterar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($ordens as $ordem)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('ordens.show', $ordem) }}" class="text-sm font-semibold text-orange-primary hover:underline">
                                    #{{ $ordem->numero_ordem }}
                                </a>
                                <p class="text-xs text-gray-400">ID {{ $ordem->id }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $ordem->cliente->nome ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusCores = [
                                        'aberta' => 'bg-yellow-100 text-yellow-800',
                                        'em_andamento' => 'bg-blue-100 text-blue-800',
                                        'concluida' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusCores[$ordem->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $ordem->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($ordem->status_pagamento === 'pago')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Pago
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                        <i class="fas fa-clock mr-1"></i> Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form method="POST" action="{{ route('financeiro.pagamento', $ordem) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($ordem->status_pagamento === 'pago')
                                        <input type="hidden" name="status_pagamento" value="pendente">
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition-colors">
                                            Marcar Pendente
                                        </button>
                                    @else
                                        <input type="hidden" name="status_pagamento" value="pago">
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg border border-green-300 text-green-700 hover:bg-green-50 transition-colors">
                                            Marcar Pago
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Nenhuma ordem de serviço encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ordens->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $ordens->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('graficoFinanceiro');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Valor Bruto (Receita)', 'Gastos Totais (Despesas)'],
                datasets: [{
                    label: 'R$',
                    data: [{{ $valorBruto }}, {{ $totalDespesas }}],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(239, 68, 68, 0.85)'
                    ],
                    borderColor: [
                        'rgb(22, 163, 74)',
                        'rgb(220, 38, 38)'
                    ],
                    borderWidth: 1,
                    borderRadius: 12,
                    maxBarThickness: 120
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.raw || 0;
                                return 'R$ ' + Number(value).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'R$ ' + Number(value).toLocaleString('pt-BR');
                            }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
