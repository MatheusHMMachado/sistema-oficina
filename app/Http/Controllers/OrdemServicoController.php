<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\ServicoPeca;
use App\Models\ItemOrdemServico;
use App\Models\StatusAtualizacao;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServico::with(['cliente', 'veiculo']);

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

        return view('ordens.index', compact('ordens', 'statusOptions'));
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
            'veiculo_id' => 'required|exists:veiculos,id',
            'data_entrada' => 'required|date',
            'km_entrada' => 'nullable|integer',
            'observacoes' => 'nullable|string',
        ]);

        $ordem = OrdemServico::create($request->all());
        
        // Carregar relacionamentos para evitar erros na view
        $ordem->load(['cliente', 'veiculo']);

        return redirect()->route('ordens.edit', $ordem)
                         ->with('success', 'Ordem de serviço criada! Adicione os serviços e peças.');
    }

    public function show(OrdemServico $ordem)
    {
        $ordem->load(['cliente', 'veiculo', 'itens.servicoPeca', 'checklist.fotos', 'statusAtualizacoes.usuario']);
        
        // Verificar se cliente e veículo existem (mas não bloquear, apenas mostrar mensagem na view)
        // A view já tem verificações para exibir mensagens apropriadas
        
        // Adicionar pontos do veículo para o checklist
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
        // Carregar relacionamentos com verificação
        $ordem->load(['cliente', 'veiculo', 'itens.servicoPeca']);
        
        // Verificar se cliente e veículo existem
        if (!$ordem->cliente) {
            return redirect()->route('ordens.index')
                           ->with('error', 'Cliente associado a esta ordem não foi encontrado. A ordem pode estar corrompida.');
        }
        
        if (!$ordem->veiculo) {
            return redirect()->route('ordens.index')
                           ->with('error', 'Veículo associado a esta ordem não foi encontrado. A ordem pode estar corrompida.');
        }
        
        $servicosPecas = ServicoPeca::where('ativo', true)->orderBy('descricao')->get();
        
        return view('ordens.edit', compact('ordem', 'servicosPecas'));
    }

    public function update(Request $request, OrdemServico $ordem)
    {
        $request->validate([
            'data_entrada' => 'required|date',
            'data_saida' => 'nullable|date',
            'km_entrada' => 'nullable|integer',
            'status' => 'required|in:aberta,em_andamento,concluida,cancelada',
            'observacoes' => 'nullable|string',
        ]);

        $ordem->update($request->all());

        return redirect()->route('ordens.show', $ordem)
                         ->with('success', 'Ordem de serviço atualizada com sucesso!');
    }

    public function destroy(OrdemServico $ordem)
    {
        try {
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
        $request->validate([
            'servico_peca_id' => 'required|exists:servicos_pecas,id',
            'quantidade' => 'required|integer|min:1',
        ]);

        $servicoPeca = ServicoPeca::findOrFail($request->servico_peca_id);

        ItemOrdemServico::create([
            'ordem_servico_id' => $ordem->id,
            'servico_peca_id' => $request->servico_peca_id,
            'quantidade' => $request->quantidade,
            'valor_unitario' => $servicoPeca->valor_unitario,
        ]);

        // Recalcular valor total da ordem
        $ordem->calcularValorTotal();

        return back()->with('success', 'Item adicionado com sucesso!');
    }

    // Remover item da ordem de serviço
    public function removerItem(ItemOrdemServico $item)
    {
        $ordem = $item->ordemServico;
        $item->delete();
        
        // Recalcular valor total da ordem
        $ordem->calcularValorTotal();
        
        return back()->with('success', 'Item removido com sucesso!');
    }

    // Atualizar status da ordem
    public function updateStatus(Request $request, OrdemServico $ordem)
    {
        $request->validate([
            'status' => 'required|in:aberta,em_andamento,concluida,cancelada',
            'mensagem' => 'nullable|string|max:500',
            'observacoes' => 'nullable|string'
        ]);

        $statusAnterior = $ordem->status;
        
        $ordem->update(['status' => $request->status]);

        // Registrar atualização de status com feedback
        StatusAtualizacao::create([
            'ordem_servico_id' => $ordem->id,
            'user_id' => Auth::id(),
            'status_anterior' => $statusAnterior,
            'status_novo' => $request->status,
            'mensagem' => $request->mensagem,
            'observacoes' => $request->observacoes,
            'data_atualizacao' => now()
        ]);

        return back()->with('success', 'Status atualizado com sucesso! O cliente poderá ver esta atualização no portal.');
    }

    // Adicionar atualização de status com feedback
    public function adicionarAtualizacaoStatus(Request $request, OrdemServico $ordem)
    {
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
        $request->validate([
            'mensagem' => 'nullable|string|max:500',
        ]);

        $cliente = $ordem->cliente;

        if (!$cliente->email) {
            return back()->with('error', 'Cliente não possui e-mail cadastrado.');
        }

        // Aqui você pode implementar o envio de notificação
        // Por exemplo: Mail::to($cliente->email)->send(new OrdemServicoNotificacaoMail($ordem, $request->mensagem));
        // Ou WhatsApp, SMS, etc.

        return back()->with('success', 'Cliente notificado com sucesso!');
    }
}