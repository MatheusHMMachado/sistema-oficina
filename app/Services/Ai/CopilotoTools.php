<?php

namespace App\Services\Ai;

use App\Models\Cliente;
use App\Models\Despesa;
use App\Models\OrdemServico;
use App\Models\ServicoPeca;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CopilotoTools
{
    public function definitions(): array
    {
        return [
            $this->tool('ajuda_sistema', 'Retorna tutorial completo (passo a passo), dúvidas frequentes e comandos de chat de um módulo do sistema.', [
                'type' => 'object',
                'properties' => [
                    'topico' => [
                        'type' => 'string',
                        'description' => 'Tema: geral, clientes, veiculos, servicos_pecas, ordens, checklist, financeiro, portal, atribuicoes, funcionarios, dashboard, copiloto, pdf, duvidas',
                    ],
                ],
                'required' => ['topico'],
            ]),
            $this->tool('listar_tutoriais', 'Lista todos os tutoriais disponíveis no Copiloto.', [
                'type' => 'object',
                'properties' => (object) [],
            ]),
            $this->tool('resumo_oficina', 'Retorna indicadores gerais: clientes, veículos, OS por status e faturamento do mês.', [
                'type' => 'object',
                'properties' => (object) [],
            ]),
            $this->tool('resumo_financeiro', 'Retorna valor bruto (pagos), despesas, líquido e contagem de serviços pagos/pendentes.', [
                'type' => 'object',
                'properties' => (object) [],
            ]),
            $this->tool('buscar_clientes', 'Busca clientes por nome, CPF ou telefone.', [
                'type' => 'object',
                'properties' => [
                    'termo' => ['type' => 'string', 'description' => 'Nome, CPF ou telefone'],
                    'limite' => ['type' => 'integer', 'description' => 'Máximo de resultados (padrão 8)'],
                ],
                'required' => ['termo'],
            ]),
            $this->tool('cadastrar_cliente', 'Cadastra um novo cliente no banco de dados.', [
                'type' => 'object',
                'properties' => [
                    'nome' => ['type' => 'string'],
                    'cpf' => ['type' => 'string', 'description' => 'CPF com ou sem máscara'],
                    'telefone' => ['type' => 'string'],
                    'celular' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'endereco' => ['type' => 'string'],
                    'rg' => ['type' => 'string'],
                ],
                'required' => ['nome', 'cpf'],
            ]),
            $this->tool('buscar_veiculos', 'Busca veículos por placa, marca ou modelo.', [
                'type' => 'object',
                'properties' => [
                    'termo' => ['type' => 'string'],
                    'limite' => ['type' => 'integer'],
                ],
                'required' => ['termo'],
            ]),
            $this->tool('cadastrar_veiculo', 'Cadastra veículo vinculado a um cliente (por CPF ou ID).', [
                'type' => 'object',
                'properties' => [
                    'cliente_id' => ['type' => 'integer'],
                    'cpf_cliente' => ['type' => 'string'],
                    'marca' => ['type' => 'string'],
                    'modelo' => ['type' => 'string'],
                    'placa' => ['type' => 'string'],
                    'cor' => ['type' => 'string'],
                    'ano' => ['type' => 'integer'],
                    'chassi' => ['type' => 'string'],
                    'km' => ['type' => 'integer'],
                ],
                'required' => ['marca', 'modelo', 'placa'],
            ]),
            $this->tool('cadastrar_servico_peca', 'Cadastra um serviço ou peça no catálogo (somente admin).', [
                'type' => 'object',
                'properties' => [
                    'descricao' => ['type' => 'string'],
                    'valor_unitario' => ['type' => 'number'],
                    'tipo' => ['type' => 'string', 'enum' => ['servico', 'peca']],
                ],
                'required' => ['descricao', 'valor_unitario', 'tipo'],
            ]),
            $this->tool('abrir_ordem_servico', 'Abre uma nova Ordem de Serviço vinculando cliente (CPF/ID) e veículo (placa/ID).', [
                'type' => 'object',
                'properties' => [
                    'cliente_id' => ['type' => 'integer'],
                    'cpf_cliente' => ['type' => 'string'],
                    'veiculo_id' => ['type' => 'integer'],
                    'placa' => ['type' => 'string'],
                    'km_entrada' => ['type' => 'integer'],
                    'observacoes' => ['type' => 'string'],
                    'data_entrada' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                ],
            ]),
            $this->tool('listar_ordens', 'Lista ordens de serviço filtrando por status e/ou pagamento.', [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'description' => 'aberta, em_andamento, concluida, cancelada ou todos',
                    ],
                    'status_pagamento' => [
                        'type' => 'string',
                        'description' => 'pago, pendente ou todos',
                    ],
                    'limite' => ['type' => 'integer'],
                ],
            ]),
            $this->tool('marcar_ordem_paga', 'Marca o status de pagamento de uma OS como pago ou pendente.', [
                'type' => 'object',
                'properties' => [
                    'numero_ordem' => ['type' => 'string'],
                    'ordem_id' => ['type' => 'integer'],
                    'status_pagamento' => ['type' => 'string', 'enum' => ['pago', 'pendente']],
                ],
                'required' => ['status_pagamento'],
            ]),
            $this->tool('cadastrar_despesa', 'Registra uma despesa no controle financeiro (somente admin).', [
                'type' => 'object',
                'properties' => [
                    'descricao' => ['type' => 'string'],
                    'valor' => ['type' => 'number'],
                    'categoria' => [
                        'type' => 'string',
                        'enum' => array_keys(Despesa::CATEGORIAS),
                        'description' => 'contas_a_pagar, salario_mecanicos, impostos_taxas, prejuizos_retrabalhos',
                    ],
                    'data_despesa' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                ],
                'required' => ['descricao', 'valor', 'categoria'],
            ]),
            $this->tool('listar_servicos_pecas', 'Lista serviços e peças cadastrados.', [
                'type' => 'object',
                'properties' => [
                    'tipo' => ['type' => 'string', 'description' => 'servico, peca ou todos'],
                    'termo' => ['type' => 'string'],
                    'limite' => ['type' => 'integer'],
                ],
            ]),
        ];
    }

    public function execute(string $name, array $arguments): array
    {
        return match ($name) {
            'ajuda_sistema' => $this->ajudaSistema($arguments['topico'] ?? 'geral'),
            'listar_tutoriais' => $this->listarTutoriais(),
            'resumo_oficina' => $this->resumoOficina(),
            'resumo_financeiro' => $this->resumoFinanceiro(),
            'buscar_clientes' => $this->buscarClientes($arguments),
            'cadastrar_cliente' => $this->cadastrarCliente($arguments),
            'buscar_veiculos' => $this->buscarVeiculos($arguments),
            'cadastrar_veiculo' => $this->cadastrarVeiculo($arguments),
            'cadastrar_servico_peca' => $this->cadastrarServicoPeca($arguments),
            'abrir_ordem_servico' => $this->abrirOrdemServico($arguments),
            'listar_ordens' => $this->listarOrdens($arguments),
            'marcar_ordem_paga' => $this->marcarOrdemPaga($arguments),
            'cadastrar_despesa' => $this->cadastrarDespesa($arguments),
            'listar_servicos_pecas' => $this->listarServicosPecas($arguments),
            default => ['ok' => false, 'erro' => "Ferramenta desconhecida: {$name}"],
        };
    }

    private function tool(string $name, string $description, array $parameters): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => $parameters,
            ],
        ];
    }

    private function ajudaSistema(string $topico): array
    {
        $dados = CopilotoTutoriais::obter($topico);
        $dados['guia'] = CopilotoTutoriais::formatarResposta($dados);

        return $dados;
    }

    private function listarTutoriais(): array
    {
        $temas = CopilotoTutoriais::listarTemas();
        $lista = [];
        foreach ($temas as $chave => $titulo) {
            $lista[] = [
                'topico' => $chave,
                'titulo' => $titulo,
                'comando' => "tutorial {$chave}",
            ];
        }

        return [
            'ok' => true,
            'total' => count($lista),
            'tutoriais' => $lista,
            'mensagem' => 'Digite "tutorial clientes", "tutorial veiculos", "tutorial pecas", "tutorial ordens", etc.',
        ];
    }

    private function resumoOficina(): array
    {
        return [
            'ok' => true,
            'total_clientes' => Cliente::count(),
            'total_veiculos' => Veiculo::count(),
            'ordens_abertas' => OrdemServico::where('status', 'aberta')->count(),
            'ordens_em_andamento' => OrdemServico::where('status', 'em_andamento')->count(),
            'ordens_concluidas' => OrdemServico::where('status', 'concluida')->count(),
            'faturamento_mes' => (float) OrdemServico::where('status', 'concluida')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('valor_total'),
        ];
    }

    private function resumoFinanceiro(): array
    {
        if (! $this->isAdmin()) {
            return ['ok' => false, 'erro' => 'Apenas administradores podem consultar o financeiro.'];
        }

        $valorBruto = (float) OrdemServico::where('status', '!=', 'cancelada')
            ->where('status_pagamento', 'pago')
            ->sum('valor_total');
        $totalDespesas = (float) Despesa::sum('valor');

        return [
            'ok' => true,
            'valor_bruto' => $valorBruto,
            'total_despesas' => $totalDespesas,
            'valor_liquido' => $valorBruto - $totalDespesas,
            'servicos_pagos' => OrdemServico::where('status', '!=', 'cancelada')->where('status_pagamento', 'pago')->count(),
            'servicos_pendentes' => OrdemServico::where('status', '!=', 'cancelada')->where('status_pagamento', 'pendente')->count(),
        ];
    }

    private function buscarClientes(array $args): array
    {
        $termo = trim((string) ($args['termo'] ?? ''));
        $limite = min((int) ($args['limite'] ?? 8), 20);

        if ($termo === '') {
            return ['ok' => false, 'erro' => 'Informe um termo de busca.'];
        }

        $cpfDigits = preg_replace('/\D+/', '', $termo);

        $itens = Cliente::query()
            ->where(function ($q) use ($termo, $cpfDigits) {
                $q->where('nome', 'ilike', "%{$termo}%")
                    ->orWhere('telefone', 'ilike', "%{$termo}%")
                    ->orWhere('celular', 'ilike', "%{$termo}%");
                if ($cpfDigits !== '') {
                    $q->orWhere('cpf', 'like', "%{$cpfDigits}%");
                }
            })
            ->orderBy('nome')
            ->limit($limite)
            ->get(['id', 'nome', 'cpf', 'telefone', 'celular', 'email']);

        return [
            'ok' => true,
            'total' => $itens->count(),
            'clientes' => $itens,
        ];
    }

    private function cadastrarCliente(array $args): array
    {
        $cpf = preg_replace('/\D+/', '', (string) ($args['cpf'] ?? ''));

        $validator = Validator::make([
            'nome' => $args['nome'] ?? null,
            'cpf' => $cpf,
            'rg' => $args['rg'] ?? null,
            'endereco' => $args['endereco'] ?? null,
            'telefone' => $args['telefone'] ?? null,
            'celular' => $args['celular'] ?? null,
            'email' => $args['email'] ?? null,
        ], [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:clientes,cpf',
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ], [
            'cpf.size' => 'CPF deve ter 11 dígitos.',
            'cpf.unique' => 'Já existe cliente com este CPF.',
        ]);

        if ($validator->fails()) {
            return ['ok' => false, 'erro' => $validator->errors()->first()];
        }

        $cliente = Cliente::create($validator->validated());

        return [
            'ok' => true,
            'mensagem' => 'Cliente cadastrado com sucesso.',
            'cliente' => [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'cpf' => $cliente->cpf,
                'url' => route('clientes.show', $cliente),
            ],
        ];
    }

    private function buscarVeiculos(array $args): array
    {
        $termo = trim((string) ($args['termo'] ?? ''));
        $limite = min((int) ($args['limite'] ?? 8), 20);

        if ($termo === '') {
            return ['ok' => false, 'erro' => 'Informe placa, marca ou modelo.'];
        }

        $placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $termo));

        $itens = Veiculo::with('cliente:id,nome')
            ->where(function ($q) use ($termo, $placa) {
                $q->where('marca', 'ilike', "%{$termo}%")
                    ->orWhere('modelo', 'ilike', "%{$termo}%")
                    ->orWhere('placa', 'ilike', "%{$placa}%");
            })
            ->orderByDesc('id')
            ->limit($limite)
            ->get();

        return [
            'ok' => true,
            'total' => $itens->count(),
            'veiculos' => $itens->map(fn ($v) => [
                'id' => $v->id,
                'placa' => $v->placa,
                'marca' => $v->marca,
                'modelo' => $v->modelo,
                'ano' => $v->ano,
                'cliente' => $v->cliente->nome ?? null,
            ]),
        ];
    }

    private function cadastrarVeiculo(array $args): array
    {
        $cliente = null;
        if (! empty($args['cliente_id'])) {
            $cliente = Cliente::find($args['cliente_id']);
        } elseif (! empty($args['cpf_cliente'])) {
            $cpf = preg_replace('/\D+/', '', $args['cpf_cliente']);
            $cliente = Cliente::where('cpf', $cpf)->first();
        }

        if (! $cliente) {
            return ['ok' => false, 'erro' => 'Informe cliente_id ou cpf_cliente de um cliente existente.'];
        }

        $placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($args['placa'] ?? '')));

        $validator = Validator::make([
            'cliente_id' => $cliente->id,
            'marca' => $args['marca'] ?? null,
            'modelo' => $args['modelo'] ?? null,
            'placa' => $placa,
            'cor' => $args['cor'] ?? 'Não informada',
            'ano' => $args['ano'] ?? (int) date('Y'),
            'chassi' => $args['chassi'] ?? null,
            'km' => $args['km'] ?? null,
        ], [
            'cliente_id' => 'required|exists:clientes,id',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'placa' => 'required|string|max:10|unique:veiculos,placa',
            'cor' => 'required|string|max:50',
            'ano' => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'chassi' => 'nullable|string|max:50',
            'km' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return ['ok' => false, 'erro' => $validator->errors()->first()];
        }

        $veiculo = Veiculo::create($validator->validated());

        return [
            'ok' => true,
            'mensagem' => 'Veículo cadastrado com sucesso.',
            'veiculo' => [
                'id' => $veiculo->id,
                'placa' => $veiculo->placa,
                'marca' => $veiculo->marca,
                'modelo' => $veiculo->modelo,
                'cliente' => $cliente->nome,
                'url' => route('veiculos.show', $veiculo),
            ],
        ];
    }

    private function cadastrarServicoPeca(array $args): array
    {
        if (! $this->isAdmin()) {
            return ['ok' => false, 'erro' => 'Apenas administradores podem cadastrar serviços/peças.'];
        }

        $tipo = $args['tipo'] ?? null;
        if ($tipo === 'serviço') {
            $tipo = 'servico';
        }
        if ($tipo === 'peça') {
            $tipo = 'peca';
        }

        $validator = Validator::make([
            'descricao' => $args['descricao'] ?? null,
            'valor_unitario' => $args['valor_unitario'] ?? null,
            'tipo' => $tipo,
            'ativo' => true,
        ], [
            'descricao' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
            'tipo' => 'required|in:servico,peca',
        ]);

        if ($validator->fails()) {
            return ['ok' => false, 'erro' => $validator->errors()->first()];
        }

        $item = ServicoPeca::create($validator->validated());

        return [
            'ok' => true,
            'mensagem' => ucfirst($item->tipo) . ' cadastrado(a) com sucesso.',
            'item' => [
                'id' => $item->id,
                'descricao' => $item->descricao,
                'tipo' => $item->tipo,
                'valor_unitario' => (float) $item->valor_unitario,
                'url' => route('servicos-pecas.edit', $item),
            ],
        ];
    }

    private function abrirOrdemServico(array $args): array
    {
        $cliente = null;
        if (! empty($args['cliente_id'])) {
            $cliente = Cliente::find($args['cliente_id']);
        } elseif (! empty($args['cpf_cliente'])) {
            $cpf = preg_replace('/\D+/', '', $args['cpf_cliente']);
            $cliente = Cliente::where('cpf', $cpf)->first();
        }

        $veiculo = null;
        if (! empty($args['veiculo_id'])) {
            $veiculo = Veiculo::find($args['veiculo_id']);
        } elseif (! empty($args['placa'])) {
            $placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $args['placa']));
            $veiculo = Veiculo::query()
                ->where(function ($q) use ($placa) {
                    $q->whereRaw("UPPER(REPLACE(placa, '-', '')) = ?", [$placa])
                        ->orWhere('placa', 'ilike', '%' . $placa . '%');
                })
                ->first();
        }

        if (! $cliente && $veiculo) {
            $cliente = $veiculo->cliente;
        }

        if (! $cliente) {
            return ['ok' => false, 'erro' => 'Cliente não encontrado. Informe cpf_cliente ou cliente_id.'];
        }

        if (! $veiculo) {
            return ['ok' => false, 'erro' => 'Veículo não encontrado. Informe placa ou veiculo_id.'];
        }

        if ((int) $veiculo->cliente_id !== (int) $cliente->id) {
            return ['ok' => false, 'erro' => 'Este veículo não pertence ao cliente informado.'];
        }

        $ordem = OrdemServico::create([
            'cliente_id' => $cliente->id,
            'veiculo_id' => $veiculo->id,
            'data_entrada' => $args['data_entrada'] ?? now()->toDateString(),
            'km_entrada' => $args['km_entrada'] ?? $veiculo->km,
            'status' => 'aberta',
            'status_pagamento' => 'pendente',
            'observacoes' => $args['observacoes'] ?? null,
            'valor_total' => 0,
        ]);

        return [
            'ok' => true,
            'mensagem' => 'Ordem de serviço aberta com sucesso. Agora adicione serviços/peças na tela da OS.',
            'ordem' => [
                'id' => $ordem->id,
                'numero_ordem' => $ordem->numero_ordem,
                'cliente' => $cliente->nome,
                'placa' => $veiculo->placa,
                'url' => route('ordens.edit', $ordem),
            ],
        ];
    }

    private function listarOrdens(array $args): array
    {
        $limite = min((int) ($args['limite'] ?? 10), 30);
        $query = OrdemServico::with('cliente:id,nome')
            ->where('status', '!=', 'cancelada')
            ->orderByDesc('created_at');

        $status = $args['status'] ?? 'todos';
        if ($status && $status !== 'todos') {
            $query->where('status', $status);
        }

        $pag = $args['status_pagamento'] ?? 'todos';
        if ($pag && $pag !== 'todos') {
            $query->where('status_pagamento', $pag);
        }

        $ordens = $query->limit($limite)->get();

        return [
            'ok' => true,
            'total' => $ordens->count(),
            'ordens' => $ordens->map(fn ($o) => [
                'id' => $o->id,
                'numero_ordem' => $o->numero_ordem,
                'cliente' => $o->cliente->nome ?? null,
                'status' => $o->status,
                'status_pagamento' => $o->status_pagamento ?? 'pendente',
                'valor_total' => (float) $o->valor_total,
                'url' => route('ordens.show', $o),
            ]),
        ];
    }

    private function marcarOrdemPaga(array $args): array
    {
        if (! $this->isAdmin()) {
            return ['ok' => false, 'erro' => 'Apenas administradores podem alterar pagamento.'];
        }

        $ordem = null;
        if (! empty($args['ordem_id'])) {
            $ordem = OrdemServico::find($args['ordem_id']);
        } elseif (! empty($args['numero_ordem'])) {
            $ordem = OrdemServico::where('numero_ordem', $args['numero_ordem'])->first();
        }

        if (! $ordem) {
            return ['ok' => false, 'erro' => 'Ordem não encontrada. Informe numero_ordem ou ordem_id.'];
        }

        $status = $args['status_pagamento'] ?? null;
        if (! in_array($status, ['pago', 'pendente'], true)) {
            return ['ok' => false, 'erro' => 'status_pagamento deve ser pago ou pendente.'];
        }

        $ordem->update(['status_pagamento' => $status]);

        return [
            'ok' => true,
            'mensagem' => "OS {$ordem->numero_ordem} marcada como {$status}.",
            'ordem' => [
                'id' => $ordem->id,
                'numero_ordem' => $ordem->numero_ordem,
                'status_pagamento' => $ordem->status_pagamento,
                'valor_total' => (float) $ordem->valor_total,
            ],
        ];
    }

    private function cadastrarDespesa(array $args): array
    {
        if (! $this->isAdmin()) {
            return ['ok' => false, 'erro' => 'Apenas administradores podem cadastrar despesas.'];
        }

        $validator = Validator::make($args, [
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'categoria' => 'required|in:' . implode(',', array_keys(Despesa::CATEGORIAS)),
            'data_despesa' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return ['ok' => false, 'erro' => $validator->errors()->first()];
        }

        $data = $validator->validated();
        $despesa = Despesa::create([
            ...$data,
            'data_despesa' => $data['data_despesa'] ?? now()->toDateString(),
            'user_id' => Auth::id(),
        ]);

        return [
            'ok' => true,
            'mensagem' => 'Despesa registrada. O saldo líquido foi atualizado.',
            'despesa' => [
                'id' => $despesa->id,
                'descricao' => $despesa->descricao,
                'valor' => (float) $despesa->valor,
                'categoria' => $despesa->categoria_label,
            ],
        ];
    }

    private function listarServicosPecas(array $args): array
    {
        $limite = min((int) ($args['limite'] ?? 15), 40);
        $query = ServicoPeca::query()->orderBy('descricao');

        $tipo = $args['tipo'] ?? 'todos';
        if ($tipo && $tipo !== 'todos') {
            $query->where('tipo', $tipo);
        }

        if (! empty($args['termo'])) {
            $query->where('descricao', 'ilike', '%' . $args['termo'] . '%');
        }

        $itens = $query->limit($limite)->get(['id', 'descricao', 'tipo', 'valor_unitario', 'ativo']);

        return [
            'ok' => true,
            'total' => $itens->count(),
            'itens' => $itens,
        ];
    }

    private function isAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }
}
