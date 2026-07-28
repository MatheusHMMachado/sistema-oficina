<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CopilotoService
{
    public function __construct(
        private readonly CopilotoTools $tools
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) config('ai.enabled', true);
    }

    public function hasLlm(): bool
    {
        return filled(config('ai.api_key'));
    }

    public function mode(): string
    {
        if (! $this->isEnabled()) {
            return 'disabled';
        }

        return $this->hasLlm() ? 'llm' : 'local';
    }

    public function chat(string $message, array $history = []): array
    {
        if (! $this->isEnabled()) {
            return [
                'reply' => 'O Copiloto está desativado. Defina AI_COPILOTO_ENABLED=true no .env.',
                'mode' => 'disabled',
                'actions' => [],
            ];
        }

        $message = trim($message);
        if ($message === '') {
            return [
                'reply' => 'Envie uma mensagem para eu ajudar.',
                'mode' => $this->mode(),
                'actions' => [],
            ];
        }

        if ($this->hasLlm()) {
            try {
                return $this->chatWithLlm($message, $history);
            } catch (\Throwable $e) {
                Log::warning('Copiloto LLM falhou, usando motor local.', [
                    'error' => $e->getMessage(),
                ]);

                $local = $this->chatLocal($message);
                $local['reply'] = "⚠️ A API de IA falhou ({$e->getMessage()}). Usei o motor local:\n\n" . $local['reply'];
                $local['mode'] = 'local_fallback';

                return $local;
            }
        }

        return $this->chatLocal($message);
    }

    private function chatWithLlm(string $message, array $history): array
    {
        $system = <<<'PROMPT'
Você é o Copiloto do Sistema Oficina, um assistente virtual em português do Brasil.
Seu papel: treinar usuários (tutoriais), tirar dúvidas e executar cadastros/consultas.

Quando o usuário pedir ajuda, tutorial, "como fazer" ou dúvidas, USE a ferramenta ajuda_sistema ou listar_tutoriais.
Tutoriais disponíveis: geral, clientes, veiculos, servicos_pecas, ordens, checklist, financeiro, portal, atribuicoes, funcionarios, dashboard, copiloto, pdf, duvidas.

Cadastros que você pode executar via tools:
- cadastrar_cliente (nome + CPF)
- cadastrar_veiculo (cliente por CPF/ID + marca, modelo, placa)
- cadastrar_servico_peca (admin: tipo servico/peca + descrição + valor)
- abrir_ordem_servico (cliente + placa/veículo)
- cadastrar_despesa (admin)

Regras:
- Seja objetivo, didático e amigável.
- Não invente dados: use as tools.
- Se faltarem dados obrigatórios, peça no chat de forma clara com um exemplo.
- Formate valores em R$ e datas em dd/mm/aaaa.
- Após cadastrar, confirme e informe ID/link quando existir.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $system],
        ];

        foreach (array_slice($history, -config('ai.max_history', 20)) as $item) {
            if (! empty($item['role']) && ! empty($item['content'])) {
                $messages[] = [
                    'role' => $item['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => (string) $item['content'],
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $actions = [];
        $maxRounds = (int) config('ai.max_tool_rounds', 5);

        for ($round = 0; $round < $maxRounds; $round++) {
            $response = $this->requestChat($messages, $this->tools->definitions());
            $choice = $response['choices'][0]['message'] ?? [];

            $toolCalls = $choice['tool_calls'] ?? [];
            if (empty($toolCalls)) {
                $reply = trim((string) ($choice['content'] ?? 'Pronto. Como mais posso ajudar?'));

                return [
                    'reply' => $reply,
                    'mode' => 'llm',
                    'actions' => $actions,
                ];
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $choice['content'] ?? null,
                'tool_calls' => $toolCalls,
            ];

            foreach ($toolCalls as $call) {
                $name = $call['function']['name'] ?? '';
                $argsJson = $call['function']['arguments'] ?? '{}';
                $args = json_decode($argsJson, true);
                if (! is_array($args)) {
                    $args = [];
                }

                $result = $this->tools->execute($name, $args);
                $actions[] = [
                    'tool' => $name,
                    'ok' => (bool) ($result['ok'] ?? false),
                ];

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? Str::uuid()->toString(),
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        return [
            'reply' => 'Concluí as consultas/ações. Se precisar de mais detalhes, pergunte.',
            'mode' => 'llm',
            'actions' => $actions,
        ];
    }

    private function requestChat(array $messages, array $tools): array
    {
        $payload = [
            'model' => config('ai.model'),
            'messages' => $messages,
            'temperature' => 0.2,
        ];

        if (! empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $response = Http::withToken(config('ai.api_key'))
            ->baseUrl(config('ai.base_url'))
            ->timeout((int) config('ai.timeout', 60))
            ->acceptJson()
            ->post('/chat/completions', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Erro na API de IA: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Motor local sem LLM: interpreta comandos comuns em português e chama as mesmas tools.
     */
    private function chatLocal(string $message): array
    {
        $text = mb_strtolower($message);
        $actions = [];

        // Lista de tutoriais
        if (preg_match('/\b(lista\s+de\s+tutoriais|tutoriais\s+dispon[ií]veis|quais\s+tutoriais|menu\s+de\s+ajuda)\b/u', $text)) {
            $result = $this->tools->execute('listar_tutoriais', []);
            $actions[] = ['tool' => 'listar_tutoriais', 'ok' => true];
            $lines = collect($result['tutoriais'])->map(fn ($t) => "• {$t['titulo']} → diga \"{$t['comando']}\"")->implode("\n");

            return [
                'reply' => "📚 Tutoriais disponíveis\n\n{$lines}\n\nTambém: \"dúvidas frequentes\" ou \"como cadastrar cliente\".",
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Ajuda / tutorial / como fazer / dúvidas
        if (preg_match('/\b(ajuda|como\s+(usar|fazer|cadastrar|criar)|o\s+que\s+(é|e)|tutorial|treinamento|d[uú]vidas?|faq|passo\s+a\s+passo)\b/u', $text)) {
            $topico = 'geral';
            if (preg_match('/d[uú]vidas?\s+frequentes|faq|problemas?\s+comuns/u', $text)) {
                $topico = 'duvidas';
            } else {
                foreach ([
                    'servicos_pecas', 'pecas', 'peças', 'peca', 'peça', 'servico', 'serviço', 'catalogo',
                    'financeiro', 'despesa', 'clientes', 'cliente', 'veiculos', 'veiculo', 'veículo',
                    'ordens', 'ordem', 'checklist', 'portal', 'dashboard', 'funcionario', 'atribuic',
                    'copiloto', 'pdf', 'nota',
                ] as $needle) {
                    if (str_contains($text, $needle)) {
                        $topico = $needle;
                        break;
                    }
                }
            }

            $result = $this->tools->execute('ajuda_sistema', ['topico' => $topico]);
            $actions[] = ['tool' => 'ajuda_sistema', 'ok' => true];

            return [
                'reply' => $result['guia'],
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Resumo financeiro
        if (preg_match('/\b(financeiro|faturamento|despesas?|valor\s+bruto|l[ií]quido)\b/u', $text)
            && preg_match('/\b(resumo|quanto|mostrar|exibir|consultar|relat[oó]rio)\b/u', $text)
        ) {
            $result = $this->tools->execute('resumo_financeiro', []);
            $actions[] = ['tool' => 'resumo_financeiro', 'ok' => (bool) ($result['ok'] ?? false)];
            if (! ($result['ok'] ?? false)) {
                return ['reply' => $result['erro'] ?? 'Não foi possível obter o financeiro.', 'mode' => 'local', 'actions' => $actions];
            }

            return [
                'reply' => sprintf(
                    "📊 Resumo financeiro\n• Valor bruto (pagos): R$ %s\n• Despesas: R$ %s\n• Líquido: R$ %s\n• Serviços pagos: %d | Pendentes: %d",
                    number_format($result['valor_bruto'], 2, ',', '.'),
                    number_format($result['total_despesas'], 2, ',', '.'),
                    number_format($result['valor_liquido'], 2, ',', '.'),
                    $result['servicos_pagos'],
                    $result['servicos_pendentes']
                ),
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Resumo geral
        if (preg_match('/\b(resumo|indicadores|dashboard|quantos?\s+clientes|situa[cç][aã]o)\b/u', $text)) {
            $result = $this->tools->execute('resumo_oficina', []);
            $actions[] = ['tool' => 'resumo_oficina', 'ok' => true];

            return [
                'reply' => sprintf(
                    "📈 Resumo da oficina\n• Clientes: %d\n• Veículos: %d\n• OS abertas: %d | Em andamento: %d | Concluídas: %d\n• Faturamento do mês (concluídas): R$ %s",
                    $result['total_clientes'],
                    $result['total_veiculos'],
                    $result['ordens_abertas'],
                    $result['ordens_em_andamento'],
                    $result['ordens_concluidas'],
                    number_format($result['faturamento_mes'], 2, ',', '.')
                ),
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Cadastrar peça ou serviço
        if (preg_match('/\b(cadastrar|criar|adicionar)\s+(pe[cç]a|servi[cç]o)\b/u', $text, $tipoMatch)) {
            $tipo = str_contains(mb_strtolower($tipoMatch[2]), 'pe') ? 'peca' : 'servico';
            if (! preg_match('/(\d+[.,]\d{2}|\d+)/u', $message, $valorMatch)) {
                return [
                    'reply' => "Para cadastrar, use:\n\"Cadastrar peça Filtro de óleo valor 45,90\"\n\"Cadastrar serviço Alinhamento valor 120\"",
                    'mode' => 'local',
                    'actions' => [],
                ];
            }
            $valor = (float) str_replace(',', '.', $valorMatch[1]);
            $descricao = trim(preg_replace('/\b(cadastrar|criar|adicionar)\s+(pe[cç]a|servi[cç]o)\b/iu', '', $message));
            $descricao = trim(preg_replace('/\bvalor\b[:\s]*/iu', '', $descricao));
            $descricao = trim(preg_replace('/' . preg_quote($valorMatch[1], '/') . '/', '', $descricao, 1));
            $descricao = trim($descricao, " \t\n\r\0\x0B-:,");

            if ($descricao === '') {
                return ['reply' => 'Informe a descrição. Ex.: "Cadastrar peça Pastilha de freio valor 89,90"', 'mode' => 'local', 'actions' => []];
            }

            $result = $this->tools->execute('cadastrar_servico_peca', [
                'descricao' => $descricao,
                'valor_unitario' => $valor,
                'tipo' => $tipo,
            ]);
            $actions[] = ['tool' => 'cadastrar_servico_peca', 'ok' => (bool) ($result['ok'] ?? false)];
            if (! ($result['ok'] ?? false)) {
                return ['reply' => '❌ ' . ($result['erro'] ?? 'Falha ao cadastrar.'), 'mode' => 'local', 'actions' => $actions];
            }

            return [
                'reply' => "✅ {$result['mensagem']}\n• ID: {$result['item']['id']}\n• {$result['item']['descricao']}\n• R$ " . number_format($result['item']['valor_unitario'], 2, ',', '.') . "\n• {$result['item']['url']}",
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Abrir OS
        if (preg_match('/\b(abrir|criar|nova)\s+(os|ordem)\b/u', $text)) {
            if (! preg_match('/(\d{3}\.?\d{3}\.?\d{3}-?\d{2}|\d{11})/u', $message, $cpfMatch)
                || ! preg_match('/\bplaca\s*[:\s]*([A-Za-z0-9\-]{5,10})\b/iu', $message, $placaMatch)
            ) {
                return [
                    'reply' => "Para abrir uma OS, informe CPF e placa:\n\"Abrir OS CPF 529.982.247-25 placa ABC1D23 km 50000\"",
                    'mode' => 'local',
                    'actions' => [],
                ];
            }
            $km = null;
            if (preg_match('/\bkm\s*[:\s]*(\d+)/iu', $message, $kmMatch)) {
                $km = (int) $kmMatch[1];
            }
            $result = $this->tools->execute('abrir_ordem_servico', [
                'cpf_cliente' => $cpfMatch[1],
                'placa' => $placaMatch[1],
                'km_entrada' => $km,
            ]);
            $actions[] = ['tool' => 'abrir_ordem_servico', 'ok' => (bool) ($result['ok'] ?? false)];
            if (! ($result['ok'] ?? false)) {
                return ['reply' => '❌ ' . ($result['erro'] ?? 'Falha ao abrir OS.'), 'mode' => 'local', 'actions' => $actions];
            }

            return [
                'reply' => "✅ OS aberta!\n• Nº {$result['ordem']['numero_ordem']}\n• Cliente: {$result['ordem']['cliente']}\n• Placa: {$result['ordem']['placa']}\n• Continuar: {$result['ordem']['url']}",
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Cadastrar veículo
        if (preg_match('/\b(cadastrar|criar|adicionar)\s+ve[ií]culo\b/u', $text)) {
            if (! preg_match('/(\d{3}\.?\d{3}\.?\d{3}-?\d{2}|\d{11})/u', $message, $cpfMatch)
                || ! preg_match('/\bmarca\s+([A-Za-z0-9çãõáéíóúâêô ]+?)\s+modelo\b/iu', $message, $marcaMatch)
                || ! preg_match('/\bmodelo\s+([A-Za-z0-9çãõáéíóúâêô ]+?)\s+placa\b/iu', $message, $modeloMatch)
                || ! preg_match('/\bplaca\s*[:\s]*([A-Za-z0-9\-]{5,10})\b/iu', $message, $placaMatch)
            ) {
                return [
                    'reply' => "Para cadastrar veículo:\n\"Cadastrar veiculo CPF 529.982.247-25 marca Fiat modelo Uno placa ABC1D23 cor Prata ano 2018\"\n(O cliente precisa existir.)",
                    'mode' => 'local',
                    'actions' => [],
                ];
            }
            $cor = 'Não informada';
            $ano = (int) date('Y');
            $km = null;
            if (preg_match('/\bcor\s+([A-Za-zçãõáéíóúâêô ]+?)(?:\s+ano|\s+km|$)/iu', $message, $corMatch)) {
                $cor = trim($corMatch[1]);
            }
            if (preg_match('/\bano\s+(\d{4})\b/iu', $message, $anoMatch)) {
                $ano = (int) $anoMatch[1];
            }
            if (preg_match('/\bkm\s*[:\s]*(\d+)/iu', $message, $kmMatch)) {
                $km = (int) $kmMatch[1];
            }

            $result = $this->tools->execute('cadastrar_veiculo', [
                'cpf_cliente' => $cpfMatch[1],
                'marca' => trim($marcaMatch[1]),
                'modelo' => trim($modeloMatch[1]),
                'placa' => $placaMatch[1],
                'cor' => $cor,
                'ano' => $ano,
                'km' => $km,
            ]);
            $actions[] = ['tool' => 'cadastrar_veiculo', 'ok' => (bool) ($result['ok'] ?? false)];
            if (! ($result['ok'] ?? false)) {
                return ['reply' => '❌ ' . ($result['erro'] ?? 'Falha ao cadastrar veículo.'), 'mode' => 'local', 'actions' => $actions];
            }

            return [
                'reply' => "✅ Veículo cadastrado!\n• {$result['veiculo']['marca']} {$result['veiculo']['modelo']} — {$result['veiculo']['placa']}\n• Cliente: {$result['veiculo']['cliente']}\n• {$result['veiculo']['url']}",
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Listar peças/serviços
        if (preg_match('/\b(listar|mostrar)\s+(pe[cç]as?|servi[cç]os?)\b/u', $text, $m)) {
            $tipo = str_contains(mb_strtolower($m[2]), 'pe') ? 'peca' : 'servico';
            $result = $this->tools->execute('listar_servicos_pecas', ['tipo' => $tipo, 'limite' => 12]);
            $actions[] = ['tool' => 'listar_servicos_pecas', 'ok' => true];
            if (($result['total'] ?? 0) === 0) {
                return ['reply' => 'Nenhum item encontrado nesse tipo.', 'mode' => 'local', 'actions' => $actions];
            }
            $lines = collect($result['itens'])->map(fn ($i) => '• #' . $i['id'] . ' ' . $i['descricao'] . ' — R$ ' . number_format($i['valor_unitario'], 2, ',', '.'))->implode("\n");

            return ['reply' => "Catálogo ({$tipo}):\n{$lines}", 'mode' => 'local', 'actions' => $actions];
        }

        // Cadastrar cliente: "cadastrar cliente Nome CPF 123.456.789-00 telefone 11999999999"
        if (preg_match('/\b(cadastrar|criar|adicionar)\s+cliente\b/u', $text)) {
            if (! preg_match('/(\d{3}\.?\d{3}\.?\d{3}-?\d{2}|\d{11})/u', $message, $cpfMatch)) {
                return [
                    'reply' => 'Para cadastrar um cliente, informe assim:\n"Cadastrar cliente João Silva CPF 123.456.789-00 telefone 11999999999"',
                    'mode' => 'local',
                    'actions' => [],
                ];
            }

            $cpf = $cpfMatch[1];
            $nome = $message;
            $nome = preg_replace('/\b(cadastrar|criar|adicionar)\s+cliente\b/iu', '', $nome);
            $nome = preg_replace('/\bCPF\b[:\s]*' . preg_quote($cpf, '/') . '/iu', '', $nome);
            $telefone = null;
            if (preg_match('/\b(?:telefone|celular|fone)\s*[:\s]*([\d\s\(\)\-]+)/iu', $message, $telMatch)) {
                $telefone = trim($telMatch[1]);
                $nome = str_replace($telMatch[0], '', $nome);
            }
            $nome = trim(preg_replace('/\s+/', ' ', $nome), " \t\n\r\0\x0B-:,");

            if ($nome === '') {
                return [
                    'reply' => 'Faltou o nome do cliente. Ex.: "Cadastrar cliente Maria Souza CPF 529.982.247-25"',
                    'mode' => 'local',
                    'actions' => [],
                ];
            }

            $result = $this->tools->execute('cadastrar_cliente', [
                'nome' => $nome,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'celular' => $telefone,
            ]);
            $actions[] = ['tool' => 'cadastrar_cliente', 'ok' => (bool) ($result['ok'] ?? false)];

            if (! ($result['ok'] ?? false)) {
                return ['reply' => '❌ ' . ($result['erro'] ?? 'Falha ao cadastrar.'), 'mode' => 'local', 'actions' => $actions];
            }

            return [
                'reply' => "✅ Cliente cadastrado!\n• ID: {$result['cliente']['id']}\n• Nome: {$result['cliente']['nome']}\n• CPF: {$result['cliente']['cpf']}\n• Link: {$result['cliente']['url']}",
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        // Buscar cliente
        if (preg_match('/\b(buscar|procurar|encontrar)\s+cliente\b/u', $text)) {
            $termo = trim(preg_replace('/\b(buscar|procurar|encontrar)\s+cliente\b/iu', '', $message));
            if ($termo === '') {
                return ['reply' => 'Informe o nome ou CPF. Ex.: "Buscar cliente Silva"', 'mode' => 'local', 'actions' => []];
            }
            $result = $this->tools->execute('buscar_clientes', ['termo' => $termo]);
            $actions[] = ['tool' => 'buscar_clientes', 'ok' => true];
            if (($result['total'] ?? 0) === 0) {
                return ['reply' => 'Nenhum cliente encontrado.', 'mode' => 'local', 'actions' => $actions];
            }
            $lines = collect($result['clientes'])->map(fn ($c) => "• #{$c['id']} {$c['nome']} — CPF {$c['cpf']}")->implode("\n");

            return ['reply' => "Clientes encontrados:\n{$lines}", 'mode' => 'local', 'actions' => $actions];
        }

        // Listar ordens
        if (preg_match('/\b(ordens?|os)\b/u', $text) && preg_match('/\b(listar|mostrar|pendente|aberta|andamento|paga)\b/u', $text)) {
            $args = ['limite' => 8];
            if (str_contains($text, 'pendente')) {
                $args['status_pagamento'] = 'pendente';
            }
            if (str_contains($text, 'paga') || str_contains($text, 'pago')) {
                $args['status_pagamento'] = 'pago';
            }
            if (str_contains($text, 'aberta')) {
                $args['status'] = 'aberta';
            }
            if (str_contains($text, 'andamento')) {
                $args['status'] = 'em_andamento';
            }
            if (str_contains($text, 'conclu')) {
                $args['status'] = 'concluida';
            }

            $result = $this->tools->execute('listar_ordens', $args);
            $actions[] = ['tool' => 'listar_ordens', 'ok' => true];
            if (($result['total'] ?? 0) === 0) {
                return ['reply' => 'Nenhuma OS encontrada com esse filtro.', 'mode' => 'local', 'actions' => $actions];
            }
            $lines = collect($result['ordens'])->map(function ($o) {
                return sprintf(
                    '• OS %s — %s — %s — R$ %s (%s)',
                    $o['numero_ordem'],
                    $o['cliente'] ?? '—',
                    $o['status'],
                    number_format($o['valor_total'], 2, ',', '.'),
                    $o['status_pagamento']
                );
            })->implode("\n");

            return ['reply' => "Ordens de serviço:\n{$lines}", 'mode' => 'local', 'actions' => $actions];
        }

        // Cadastrar despesa
        if (preg_match('/\b(cadastrar|registrar|lan[cç]ar)\s+despesa\b/u', $text)) {
            if (! preg_match('/(\d+[.,]\d{2}|\d+)/u', $message, $valorMatch)) {
                return [
                    'reply' => 'Informe assim:\n"Cadastrar despesa Conta de luz 350,00 categoria impostos_taxas"',
                    'mode' => 'local',
                    'actions' => [],
                ];
            }
            $valor = (float) str_replace(',', '.', $valorMatch[1]);
            $categoria = 'contas_a_pagar';
            foreach (array_keys(\App\Models\Despesa::CATEGORIAS) as $cat) {
                if (str_contains($text, str_replace('_', ' ', $cat)) || str_contains($text, $cat)) {
                    $categoria = $cat;
                    break;
                }
            }
            if (str_contains($text, 'salario') || str_contains($text, 'salário')) {
                $categoria = 'salario_mecanicos';
            }
            if (str_contains($text, 'imposto') || str_contains($text, 'taxa')) {
                $categoria = 'impostos_taxas';
            }
            if (str_contains($text, 'preju') || str_contains($text, 'retrabalho')) {
                $categoria = 'prejuizos_retrabalhos';
            }

            $descricao = trim(preg_replace('/\b(cadastrar|registrar|lan[cç]ar)\s+despesa\b/iu', '', $message));
            $descricao = trim(preg_replace('/categoria\s+\w+/iu', '', $descricao));
            $descricao = trim(preg_replace('/' . preg_quote($valorMatch[1], '/') . '/', '', $descricao, 1));
            $descricao = trim($descricao) ?: 'Despesa registrada via Copiloto';

            $result = $this->tools->execute('cadastrar_despesa', [
                'descricao' => $descricao,
                'valor' => $valor,
                'categoria' => $categoria,
            ]);
            $actions[] = ['tool' => 'cadastrar_despesa', 'ok' => (bool) ($result['ok'] ?? false)];

            if (! ($result['ok'] ?? false)) {
                return ['reply' => '❌ ' . ($result['erro'] ?? 'Falha ao cadastrar despesa.'), 'mode' => 'local', 'actions' => $actions];
            }

            return [
                'reply' => "✅ Despesa registrada!\n• {$result['despesa']['descricao']}\n• R$ " . number_format($result['despesa']['valor'], 2, ',', '.') . "\n• {$result['despesa']['categoria']}",
                'mode' => 'local',
                'actions' => $actions,
            ];
        }

        return [
            'reply' => "Sou o Copiloto do Sistema Oficina.\n\n"
                . "📚 Tutoriais: diga \"lista de tutoriais\" ou \"tutorial clientes\", \"tutorial veículos\", \"tutorial peças\", \"tutorial ordens\"...\n\n"
                . "✍️ Cadastros pelo chat:\n"
                . "• Cadastrar cliente Nome CPF 123.456.789-00\n"
                . "• Cadastrar veiculo CPF ... marca X modelo Y placa ABC1D23\n"
                . "• Cadastrar peça Filtro valor 45,90\n"
                . "• Cadastrar serviço Alinhamento valor 120\n"
                . "• Abrir OS CPF ... placa ABC1D23\n"
                . "• Cadastrar despesa luz 200 impostos_taxas\n\n"
                . "📊 Consultas: \"resumo da oficina\", \"resumo financeiro\", \"listar ordens pendentes\", \"dúvidas frequentes\".",
            'mode' => 'local',
            'actions' => [],
        ];
    }
}
