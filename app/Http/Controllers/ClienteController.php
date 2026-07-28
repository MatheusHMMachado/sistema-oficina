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
        $this->normalizarDadosCliente($request);

        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => [
                'required',
                'string',
                'size:11',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Cliente::buscarPorCpf((string) $value)) {
                        $fail('Este CPF já está cadastrado.');
                    }
                },
            ],
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clientes,email',
        ], [
            'cpf.size' => 'O CPF deve conter 11 dígitos.',
            'email.unique' => 'Este e-mail já está cadastrado.',
        ]);

        Cliente::create($request->only([
            'nome', 'cpf', 'rg', 'endereco', 'telefone', 'celular', 'email',
        ]));

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
        $this->normalizarDadosCliente($request);

        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => [
                'required',
                'string',
                'size:11',
                function (string $attribute, mixed $value, \Closure $fail) use ($cliente) {
                    $existente = Cliente::buscarPorCpf((string) $value);
                    if ($existente && $existente->id !== $cliente->id) {
                        $fail('Este CPF já está cadastrado.');
                    }
                },
            ],
            'rg' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id,
        ], [
            'cpf.size' => 'O CPF deve conter 11 dígitos.',
            'email.unique' => 'Este e-mail já está cadastrado.',
        ]);

        $cliente->update($request->only([
            'nome', 'cpf', 'rg', 'endereco', 'telefone', 'celular', 'email',
        ]));

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove máscara do CPF e normaliza e-mail vazio para null (unique no banco).
     */
    private function normalizarDadosCliente(Request $request): void
    {
        $request->merge([
            'cpf' => preg_replace('/[^0-9]/', '', (string) $request->input('cpf', '')),
            'email' => $request->filled('email') ? trim((string) $request->input('email')) : null,
        ]);
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

        // Envio de e-mail ainda não está configurado no ambiente (SMTP).
        return back()->with('error', 'O envio de e-mail ainda não está configurado neste servidor. Ative o SMTP ou compartilhe o link do portal manualmente com o cliente.');
    }
}