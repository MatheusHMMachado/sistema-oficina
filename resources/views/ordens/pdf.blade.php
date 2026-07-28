<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>OS {{ $ordem->numero_ordem }}</title>
    @php
        $cfg = $brand ?? \App\Models\ConfiguracaoSistema::atual();
        $primary = $cfg->cor_primaria ?: '#FF6B35';
        $secondary = $cfg->cor_secundaria ?: '#FF8C42';
        $soft = $cfg->corClara($primary, 0.90);
        $softBorder = $cfg->corClara($primary, 0.70);
        $softText = $cfg->corEscura($primary, 0.45);
        $strongText = $cfg->corEscura($primary, 0.55);
        $empresa = $cfg->nome_empresa ?: 'Sistema Oficina';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.45;
        }
        .header {
            background: {{ $primary }};
            color: #fff;
            padding: 18px 22px;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 20px; margin-bottom: 4px; }
        .header p { font-size: 11px; opacity: 0.95; }
        .meta {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .meta td {
            vertical-align: top;
            width: 50%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .meta strong {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: {{ $primary }};
            border-bottom: 2px solid {{ $primary }};
            padding-bottom: 4px;
            margin: 18px 0 10px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.items th {
            background: {{ $soft }};
            color: {{ $softText }};
            text-align: left;
            padding: 8px 10px;
            border: 1px solid {{ $softBorder }};
            font-size: 11px;
            text-transform: uppercase;
        }
        table.items td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }
        table.items .right { text-align: right; }
        table.items .center { text-align: center; }
        .total-box {
            width: 280px;
            margin-left: auto;
            border: 1px solid {{ $softBorder }};
            background: {{ $soft }};
            padding: 12px 14px;
        }
        .total-box .label {
            font-size: 11px;
            color: {{ $softText }};
            text-transform: uppercase;
        }
        .total-box .value {
            font-size: 18px;
            font-weight: bold;
            color: {{ $strongText }};
            margin-top: 4px;
        }
        .obs {
            margin-top: 16px;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            background: {{ $primary }};
            color: #ffffff;
        }
        .assinaturas {
            margin-top: 40px;
            width: 100%;
        }
        .assinaturas td {
            width: 50%;
            text-align: center;
            padding-top: 30px;
        }
        .assinaturas .linha {
            border-top: 1px solid #9ca3af;
            width: 80%;
            margin: 0 auto 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $empresa }} — Ordem de Serviço</h1>
        <p>Nota / comprovante do serviço prestado</p>
    </div>

    <table class="meta">
        <tr>
            <td>
                <strong>Número da OS</strong>
                {{ $ordem->numero_ordem }}
                &nbsp; <span class="badge">{{ ucfirst(str_replace('_', ' ', $ordem->status)) }}</span>
            </td>
            <td>
                <strong>Data de entrada</strong>
                {{ optional($ordem->data_entrada)->format('d/m/Y') ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Cliente</strong>
                {{ $ordem->cliente->nome ?? '—' }}<br>
                CPF: {{ $ordem->cliente->cpf ?? '—' }}<br>
                Tel: {{ $ordem->cliente->celular ?? $ordem->cliente->telefone ?? '—' }}
            </td>
            <td>
                <strong>Veículo</strong>
                @if($ordem->veiculo)
                    {{ $ordem->veiculo->marca ?? '' }} {{ $ordem->veiculo->modelo ?? '' }}
                    @if(!empty($ordem->veiculo->ano)) ({{ $ordem->veiculo->ano }}) @endif<br>
                    Placa: {{ $ordem->veiculo->placa ?? '—' }}<br>
                    KM entrada: {{ $ordem->km_entrada ?? '—' }}
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <strong>Data de saída</strong>
                {{ optional($ordem->data_saida)->format('d/m/Y') ?? '—' }}
            </td>
            <td>
                <strong>Pagamento</strong>
                {{ ($ordem->status_pagamento ?? 'pendente') === 'pago' ? 'Pago' : 'Pendente' }}
            </td>
        </tr>
    </table>

    <div class="section-title">Itens do serviço</div>

    <table class="items">
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="center">Tipo</th>
                <th class="center">Qtd</th>
                <th class="right">Unitário</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordem->itens as $item)
                <tr>
                    <td>{{ $item->servicoPeca->descricao ?? 'Item' }}</td>
                    <td class="center">{{ ucfirst($item->servicoPeca->tipo ?? '—') }}</td>
                    <td class="center">{{ $item->quantidade }}</td>
                    <td class="right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">Nenhum item lançado nesta ordem.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-box">
        <div class="label">Valor total</div>
        <div class="value">R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}</div>
    </div>

    @if($ordem->observacoes)
        <div class="obs">
            <strong>Observações:</strong><br>
            {{ $ordem->observacoes }}
        </div>
    @endif

    <table class="assinaturas">
        <tr>
            <td>
                <div class="linha"></div>
                Assinatura do cliente
            </td>
            <td>
                <div class="linha"></div>
                Assinatura da oficina
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento gerado em {{ now()->format('d/m/Y H:i') }} — {{ $empresa }}
    </div>
</body>
</html>
