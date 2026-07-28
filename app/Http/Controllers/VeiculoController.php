<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Cliente;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Veiculo::with('cliente');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('placa', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function ($qc) use ($search) {
                      $qc->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        $veiculos = $query->paginate(15);

        return view('veiculos.index', compact('veiculos'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('veiculos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $this->normalizarPlaca($request);

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'cor' => 'required|string|max:255',
            'ano' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'placa' => 'required|string|min:7|max:7|unique:veiculos,placa',
            'chassi' => 'nullable|string|max:255',
            'km' => 'nullable|integer|min:0',
        ], [
            'placa.unique' => 'Esta placa já está cadastrada.',
            'placa.min' => 'Informe uma placa válida (7 caracteres).',
            'placa.max' => 'Informe uma placa válida (7 caracteres).',
        ]);

        Veiculo::create($request->only([
            'cliente_id', 'marca', 'modelo', 'cor', 'ano', 'placa', 'chassi', 'km',
        ]));

        return redirect()->route('veiculos.index')
            ->with('success', 'Veículo cadastrado com sucesso!');
    }

    public function show(Veiculo $veiculo)
    {
        $veiculo->load([
            'cliente',
            'ordensServico' => fn ($q) => $q->with(['statusAtualizacoes'])->orderByDesc('created_at'),
        ]);

        return view('veiculos.show', compact('veiculo'));
    }

    public function edit(Veiculo $veiculo)
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('veiculos.edit', compact('veiculo', 'clientes'));
    }

    public function update(Request $request, Veiculo $veiculo)
    {
        $this->normalizarPlaca($request);

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'cor' => 'required|string|max:255',
            'ano' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'placa' => 'required|string|min:7|max:7|unique:veiculos,placa,' . $veiculo->id,
            'chassi' => 'nullable|string|max:255',
            'km' => 'nullable|integer|min:0',
        ], [
            'placa.unique' => 'Esta placa já está cadastrada.',
            'placa.min' => 'Informe uma placa válida (7 caracteres).',
            'placa.max' => 'Informe uma placa válida (7 caracteres).',
        ]);

        $veiculo->update($request->only([
            'cliente_id', 'marca', 'modelo', 'cor', 'ano', 'placa', 'chassi', 'km',
        ]));

        return redirect()->route('veiculos.index')
            ->with('success', 'Veículo atualizado com sucesso!');
    }

    /**
     * Remove hífen/espaços e padroniza a placa em maiúsculas (ex.: ABC-1D23 → ABC1D23).
     */
    private function normalizarPlaca(Request $request): void
    {
        $request->merge([
            'placa' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('placa', ''))),
        ]);
    }

    public function destroy(Veiculo $veiculo)
    {
        try {
            $veiculo->delete();
            return redirect()->route('veiculos.index')
                ->with('success', 'Veículo excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('veiculos.index')
                ->with('error', 'Não é possível excluir veículo com ordens de serviço!');
        }
    }
}