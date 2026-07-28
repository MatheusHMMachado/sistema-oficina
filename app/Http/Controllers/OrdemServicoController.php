<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\ServicoPeca;
use App\Models\ItemOrdemServico;
use App\Models\StatusAtualizacao;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServico::with(['cliente', 'veiculo'])
            ->visivelPara(auth()->user());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_ordem', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function ($qc) use ($search) {
                      $qc->where('nome', 'like', "%{$search}%");
                  })
                  ->orWhereHas('veiculo', function ($qv) use ($search) {
                      $qv->where('placa', 'like', "%{$search}%");
                  });
            });
        }

        $ordens = $query->latest()->paginate(15);
        $statusOptions = ['aberta', 'em_andamento', 'concluida', 'cancelada'];

        $baseCounts = OrdemServico::query()->visivelPara(auth()->user());
        $statusCounts = [
            'aberta' => (clone $baseCounts)->where('status', 'aberta')->count(),
            'em_andamento' => (clone $baseCounts)->where('status', 'em_andamento')->count(),
            'concluida' => (clone $baseCounts)->where('status', 'concluida')->count(),
            'cancelada' => (clone $baseCounts)->where('status', 'cancelada')->count(),
            'total' => (clone $baseCounts)->count(),
        ];

        return view('ordens.index', compact('ordens', 'statusOptions', 'statusCounts'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('ordens.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'veiculo_id' => [
                'required',
                'exists:veiculos,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $pertence = Veiculo::where('id', $value)
                        ->where('cliente_id', $request->cliente_id)
                        ->exists();
                    if (! $pertence) {
                        $fail('O veículo selecionado não pertence ao cliente informado.');
                    }
                },
            ],
            'data_entrada' => 'required|date',
            'km_entrada' => 'nullable|integer',
            'observacoes' => 'nullable|string',
        ]);

        try {
            $ordem = OrdemServico::create($request->only([
                'cliente_id', 'veiculo_id', 'data_entrada', 'km_entrada', 'observacoes',
            ]));
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível gerar o número da O.S. Tente novamente.');
        }

        return redirect()->route('ordens.edit', $ordem)
            ->with('success', 'Ordem de serviço criada! Adicione os serviços e peças.');
    }

    public function show(OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $ordem->load(['cliente', 'veiculo', 'itens.servicoPeca', 'checklist.fotos', 'statusAtualizacoes.usuario']);

        $pontosVeiculo = [
            'frente_esquerda' => 'Frente Esquerda',
            'frente_direita' => 'Frente Direita',
            'lateral_esquerda' => 'Lateral Esquerda',
            'lateral_direita' => 'Lateral Direita',
            'traseira_esquerda' => 'Traseira Esquerda',
            'traseira_direita' => 'Traseira Direita',
            'capot' => 'Capô',
            'teto' => 'Teto',
            'para_brisa' => 'Para-brisa',
            'vidro_traseiro' => 'Vidro Traseiro',
            'interior' => 'Interior',
            'motor' => 'Motor'
        ];

        return view('ordens.show', compact('ordem', 'pontosVeiculo'));
    }

    public function edit(OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $ordem->load(['cliente', 'veiculo', 'itens.servicoPeca']);

        $servicosPecas = ServicoPeca::where('ativo', true)->orderBy('descricao')->get();

        return view('ordens.edit', compact('ordem', 'servicosPecas'));
    }

    public function update(Request $request, OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $request->validate([
            'data_entrada' => 'required|date',
            'data_saida' => 'nullable|date',
            'km_entrada' => 'nullable|integer',
            'status' => 'required|in:aberta,em_andamento,concluida,cancelada',
            'observacoes' => 'nullable|string',
        ]);

        $statusAnterior = $ordem->status;
        $novoStatus = $request->status;

        try {
            // Segurança: baixa residual (itens antigos sem baixa)
            if ($novoStatus === 'em_andamento' && $statusAnterior !== 'em_andamento') {
                EstoqueService::baixarPorOrdem($ordem);
            }

            if ($novoStatus === 'cancelada' && $statusAnterior !== 'cancelada') {
                EstoqueService::restaurarPorOrdem($ordem);
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $ordem->update($request->only([
            'data_entrada', 'data_saida', 'km_entrada', 'status', 'observacoes',
        ]));

        return redirect()->route('ordens.show', $ordem)
                         ->with('success', 'Ordem de serviço atualizada com sucesso!');
    }

    public function destroy(OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        try {
            EstoqueService::restaurarPorOrdem($ordem);
            $ordem->delete();
            return redirect()->route('ordens.index')
                             ->with('success', 'Ordem de serviço excluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('ordens.index')
                             ->with('error', 'Erro ao excluir ordem de serviço!');
        }
    }

    // API para buscar veículos do cliente
    public function getVeiculosCliente(Cliente $cliente)
    {
        return response()->json($cliente->veiculos->map(function($veiculo) {
            return [
                'id' => $veiculo->id,
                'marca' => $veiculo->marca,
                'modelo' => $veiculo->modelo,
                'cor' => $veiculo->cor,
                'ano' => $veiculo->ano,
                'placa_formatada' => $veiculo->placa_formatada
            ];
        }));
    }

    // Adicionar item à ordem de serviço
    public function adicionarItem(Request $request, OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $request->validate([
            'servico_peca_id' => 'required|exists:servicos_pecas,id',
            'quantidade' => 'required|integer|min:1',
        ]);

        $servicoPeca = ServicoPeca::findOrFail($request->servico_peca_id);

        try {
            EstoqueService::validarDisponibilidade($servicoPeca, (int) $request->quantidade);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $item = ItemOrdemServico::create([
            'ordem_servico_id' => $ordem->id,
            'servico_peca_id' => $request->servico_peca_id,
            'quantidade' => $request->quantidade,
            'valor_unitario' => $servicoPeca->valor_unitario,
            'estoque_baixado' => false,
        ]);

        // Baixa automática imediata ao incluir peça no orçamento / O.S.
        if ($servicoPeca->tipo === 'peca') {
            try {
                EstoqueService::baixarItem($item);
            } catch (RuntimeException $e) {
                $item->delete();
                $ordem->calcularValorTotal();

                return back()->with('error', $e->getMessage());
            }
        }

        $ordem->calcularValorTotal();

        return back()->with('success', 'Item adicionado e estoque atualizado!');
    }

    // Remover item da ordem de serviço
    public function removerItem(ItemOrdemServico $item)
    {
        $ordem = $item->ordemServico;
        $this->garantirAcessoOrdem($ordem);

        try {
            EstoqueService::restaurarItem($item);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $item->delete();
        $ordem->calcularValorTotal();

        return back()->with('success', 'Item removido e estoque restaurado!');
    }

    // Atualizar status da ordem
    public function updateStatus(Request $request, OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        if (!$ordem->cliente) {
            return back()->with('error', 'Não é possível enviar feedback ao portal: cliente desta OS não foi encontrado.');
        }

        $request->validate([
            'status' => 'required|in:aberta,em_andamento,concluida,cancelada',
            'mensagem' => 'nullable|string|max:500',
            'observacoes' => 'nullable|string'
        ]);

        $statusAnterior = $ordem->status;
        $novoStatus = $request->status;

        try {
            if ($novoStatus === 'em_andamento' && $statusAnterior !== 'em_andamento') {
                EstoqueService::baixarPorOrdem($ordem);
            }
            if ($novoStatus === 'cancelada' && $statusAnterior !== 'cancelada') {
                EstoqueService::restaurarPorOrdem($ordem);
            }
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $ordem->update(['status' => $novoStatus]);

        StatusAtualizacao::create([
            'ordem_servico_id' => $ordem->id,
            'user_id' => Auth::id(),
            'status_anterior' => $statusAnterior,
            'status_novo' => $novoStatus,
            'mensagem' => $request->mensagem,
            'observacoes' => $request->observacoes,
            'data_atualizacao' => now()
        ]);

        return back()->with('success', 'Status atualizado com sucesso! O cliente poderá ver esta atualização no portal.');
    }

    // Adicionar atualização de status com feedback
    public function adicionarAtualizacaoStatus(Request $request, OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        if (!$ordem->cliente) {
            return back()->with('error', 'Não é possível enviar mensagem ao portal: cliente desta OS não foi encontrado.');
        }

        $request->validate([
            'mensagem' => 'required|string|max:500',
            'observacoes' => 'nullable|string'
        ]);

        StatusAtualizacao::create([
            'ordem_servico_id' => $ordem->id,
            'user_id' => Auth::id(),
            'status_anterior' => $ordem->status,
            'status_novo' => $ordem->status, // Mantém o mesmo status, apenas adiciona feedback
            'mensagem' => $request->mensagem,
            'observacoes' => $request->observacoes,
            'data_atualizacao' => now()
        ]);

        return back()->with('success', 'Atualização adicionada com sucesso! O cliente poderá ver esta informação no portal.');
    }

    // Gerar PDF da ordem de serviço
    public function gerarPdf(OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $ordem->load(['cliente', 'veiculo', 'itens.servicoPeca']);

        $pdf = Pdf::loadView('ordens.pdf', compact('ordem'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download("OS_{$ordem->numero_ordem}.pdf");
    }

    /**
     * Alias para gerarPdf - usado pela rota
     */
    public function pdf(OrdemServico $ordem)
    {
        return $this->gerarPdf($ordem);
    }

    /**
     * Notificar cliente sobre atualização da ordem de serviço
     */
    public function notificarCliente(Request $request, OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $request->validate([
            'mensagem' => 'nullable|string|max:500',
        ]);

        $cliente = $ordem->cliente;

        if (!$cliente->email) {
            return back()->with('error', 'Cliente não possui e-mail cadastrado.');
        }

        // Envio de e-mail ainda não está configurado no ambiente (SMTP).
        // Não fingir sucesso para não induzir a equipe a erro.
        return back()->with('error', 'O envio de e-mail ainda não está configurado neste servidor. Cadastre o SMTP em .env ou informe o cliente pelo WhatsApp/telefone.');
    }

    /**
     * Mecânico só acessa O.S. atribuídas a ele; admin tem acesso global.
     */
    private function garantirAcessoOrdem(OrdemServico $ordem): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if (! $ordem->atribuidaAoMecanico($user->id)) {
            abort(403, 'Você só pode acessar ordens de serviço atribuídas a você.');
        }
    }
}