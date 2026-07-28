<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório Financeiro {{ $mesRef }}</title>
    @php
        $cfg = $brand ?? \App\Models\ConfiguracaoSistema::atual();
        $primary = $cfg->cor_primaria ?: '#FF6B35';
        $soft = $cfg->corClara($primary, 0.90);
        $softBorder = $cfg->corClara($primary, 0.70);
        $softText = $cfg->corEscura($primary, 0.45);
        $accentText = $cfg->corEscura($primary, 0.55);
        $empresa = $cfg->nome_empresa ?: 'Sistema Oficina';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.4; }
        .header { background: {{ $primary }}; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; margin-bottom: 4px; }
        .header p { font-size: 11px; opacity: 0.95; }
        .kpis { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .kpis td { width: 33.33%; padding: 10px; border: 1px solid #e5e7eb; background: #f9fafb; vertical-align: top; }
        .kpis .label { font-size: 9px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .kpis .value { font-size: 14px; font-weight: bold; }
        .green { color: #15803d; }
        .red { color: #b91c1c; }
        .brand { color: {{ $accentText }}; }
        .section { font-size: 12px; font-weight: bold; color: {{ $primary }}; border-bottom: 2px solid {{ $primary }}; padding-bottom: 3px; margin: 14px 0 8px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th { background: {{ $soft }}; color: {{ $softText }}; text-align: left; padding: 6px 8px; border: 1px solid {{ $softBorder }}; font-size: 9px; text-transform: uppercase; }
        table.data td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .right { text-align: right; }
        .center { text-align: center; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #6b7280; text-align: center; }
        .empty { text-align: center; color: #6b7280; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Financeiro Mensal</h1>
        <p>{{ $empresa }} — {{ ucfirst($mesLabel) }} ({{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }})</p>
    </div>

    <table class="kpis">
        <tr>
            <td>
                <div class="label">Faturamento do mês (OS pagas)</div>
                <div class="value green">R$ {{ number_format($faturamento, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Gastos / Despesas do mês</div>
                <div class="value red">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Saldo líquido do mês</div>
                <div class="value {{ $liquido >= 0 ? 'brand' : 'red' }}">R$ {{ number_format($liquido, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section">Despesas por categoria</div>
    <table class="data">
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($despesasPorCategoria as $cat => $total)
                <tr>
                    <td>{{ $categorias[$cat] ?? $cat }}</td>
                    <td class="right">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="empty">Nenhuma despesa neste mês.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Detalhe das despesas</div>
    <table class="data">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($despesasMes as $despesa)
                <tr>
                    <td>{{ optional($despesa->data_despesa)->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $despesa->descricao }}</td>
                    <td>{{ $despesa->categoria_label }}</td>
                    <td class="right">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Sem despesas registradas no período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Faturamento — Ordens pagas no mês</div>
    <table class="data">
        <thead>
            <tr>
                <th>Nº OS</th>
                <th>Cliente</th>
                <th class="center">Status</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordensPagas as $ordem)
                <tr>
                    <td>{{ $ordem->numero_ordem }}</td>
                    <td>{{ $ordem->cliente->nome ?? '—' }}</td>
                    <td class="center">{{ ucfirst(str_replace('_', ' ', $ordem->status)) }}</td>
                    <td class="right">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Nenhuma OS paga encontrada neste mês.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ $geradoEm->format('d/m/Y H:i') }} — {{ $empresa }}
    </div>
</body>
</html>
