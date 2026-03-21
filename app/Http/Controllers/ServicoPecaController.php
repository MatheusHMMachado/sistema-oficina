<?php

namespace App\Http\Controllers;

use App\Models\ServicoPeca;
use Illuminate\Http\Request;

class ServicoPecaController extends Controller
{
    public function __construct()
    {
        // Funcionário pode visualizar; alterações de catálogo são exclusivas do admin.
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = ServicoPeca::query();

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('search')) {
            $query->where('descricao', 'like', "%{$request->search}%");
        }

        $servicosPecas = $query->paginate(15);
        $tipos = ['servico' => 'Serviço', 'peca' => 'Peça'];

        return view('servicos-pecas.index', compact('servicosPecas', 'tipos'));
    }

    public function create()
    {
        return view('servicos-pecas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
            'tipo' => 'required|in:servico,peca',
        ]);

        ServicoPeca::create($request->all());

        return redirect()->route('servicos-pecas.index')
                         ->with('success', 'Item cadastrado com sucesso!');
    }

    public function show(ServicoPeca $servicoPeca)
    {
        return view('servicos-pecas.show', compact('servicoPeca'));
    }

    public function edit(ServicoPeca $servicoPeca)
    {
        return view('servicos-pecas.edit', compact('servicoPeca'));
    }

    public function update(Request $request, ServicoPeca $servicoPeca)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
            'tipo' => 'required|in:servico,peca',
            'ativo' => 'boolean',
        ]);

        $servicoPeca->update($request->all());

        return redirect()->route('servicos-pecas.index')
                         ->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(ServicoPeca $servicoPeca)
    {
        try {
            $servicoPeca->delete();
            return redirect()->route('servicos-pecas.index')
                             ->with('success', 'Item excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('servicos-pecas.index')
                             ->with('error', 'Não é possível excluir item que já foi utilizado!');
        }
    }
}