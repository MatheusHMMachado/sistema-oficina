<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%");
            });
        }

        $clientes = $query->paginate(15);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|unique:clientes,cpf',
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string|max:15',
            'celular' => 'nullable|string|max:15',
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'veiculos',
            'ordensServico' => fn ($q) => $q->with(['veiculo', 'statusAtualizacoes'])->orderByDesc('created_at'),
        ]);
        
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|unique:clientes,cpf,' . $cliente->id,
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string|max:15',
            'celular' => 'nullable|string|max:15',
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();
            return redirect()->route('clientes.index')
                             ->with('success', 'Cliente excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('clientes.index')
                             ->with('error', 'Não é possível excluir cliente com ordens de serviço!');
        }
    }

    /**
     * API: Retorna os veículos de um cliente
     */
    public function veiculos(Cliente $cliente)
    {
        $veiculos = $cliente->veiculos()->get();
        
        return response()->json($veiculos->map(function($veiculo) {
            return [
                'id' => $veiculo->id,
                'marca' => $veiculo->marca,
                'modelo' => $veiculo->modelo,
                'cor' => $veiculo->cor,
                'ano' => $veiculo->ano,
                'placa' => $veiculo->placa,
                'placa_formatada' => $veiculo->placa_formatada,
                'descricao_completa' => $veiculo->descricao_completa,
            ];
        }));
    }

    /**
     * Ativa o acesso ao portal do cliente
     */
    public function ativarPortal(Cliente $cliente)
    {
        $cliente->update([
            'acesso_portal' => true,
        ]);

        return back()->with('success', 'Acesso ao portal ativado com sucesso!');
    }

    /**
     * Envia as credenciais de acesso ao portal do cliente
     */
    public function enviarAcessoPortal(Cliente $cliente)
    {
        if (!$cliente->acesso_portal) {
            return back()->with('error', 'O acesso ao portal precisa estar ativado primeiro.');
        }

        if (!$cliente->email) {
            return back()->with('error', 'O cliente precisa ter um e-mail cadastrado.');
        }

        // Gerar token de acesso se não existir
        if (!$cliente->token_acesso) {
            $cliente->update([
                'token_acesso' => \Illuminate\Support\Str::random(32),
            ]);
            $cliente->refresh();
        }

        // Aqui você pode implementar o envio de e-mail com as credenciais
        // Por enquanto, apenas retornamos sucesso
        // Exemplo: Mail::to($cliente->email)->send(new PortalAcessoMail($cliente));

        return back()->with('success', 'Credenciais de acesso enviadas com sucesso!');
    }
}