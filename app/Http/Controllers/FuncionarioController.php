<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FuncionarioController extends Controller
{
    /**
     * Lista todos os funcionários
     */
    public function index()
    {
        $funcionarios = User::orderBy('name')->paginate(15);
        $totalFuncionarios = User::count();
        $funcionariosAtivos = User::where('ativo', true)->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $funcionariosOnline = 0;

        return view('funcionarios.index', compact(
            'funcionarios',
            'totalFuncionarios',
            'funcionariosAtivos',
            'totalAdmins',
            'funcionariosOnline'
        ));
    }

    /**
     * Cria um novo funcionário
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,funcionario',
            'ativo' => 'nullable|in:0,1,true,false',
        ], [
            'password.confirmed' => 'A confirmação da senha não confere. Digite a mesma senha nos dois campos.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'role.required' => 'Selecione o cargo (Funcionário ou Administrador).',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'ativo' => $request->boolean('ativo'),
        ]);

        return redirect()->route('funcionarios.index')
                        ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    /**
     * Edição via modal na listagem
     */
    public function edit($id)
    {
        User::findOrFail($id);

        return redirect()->route('funcionarios.index');
    }

    /**
     * Retorna dados do funcionário em JSON (modal de edição)
     */
    public function showJson($id)
    {
        $funcionario = User::findOrFail($id);

        return response()->json([
            'id' => $funcionario->id,
            'name' => $funcionario->name,
            'email' => $funcionario->email,
            'role' => $funcionario->role,
            'ativo' => (bool) $funcionario->ativo,
        ]);
    }

    /**
     * Atualiza um funcionário
     */
    public function update(Request $request, $id)
    {
        $funcionario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $funcionario->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,funcionario',
            'ativo' => 'nullable|in:0,1,true,false',
        ], [
            'password.confirmed' => 'A confirmação da senha não confere. Digite a mesma senha nos dois campos.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'role.required' => 'Selecione o cargo (Funcionário ou Administrador).',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'ativo' => $request->boolean('ativo'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $funcionario->update($data);

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    /**
     * Alterna o status ativo/inativo do funcionário
     */
    public function toggleStatus($id)
    {
        $funcionario = User::findOrFail($id);
        
        // Não permitir desativar a si mesmo
        if ($funcionario->id === auth()->id()) {
            return back()->with('error', 'Você não pode desativar sua própria conta.');
        }

        $funcionario->update([
            'ativo' => !$funcionario->ativo,
        ]);

        $status = $funcionario->ativo ? 'ativado' : 'desativado';
        
        return back()->with('success', "Funcionário {$status} com sucesso!");
    }

    /**
     * Remove um funcionário
     */
    public function destroy($id)
    {
        $funcionario = User::findOrFail($id);
        
        // Não permitir excluir a si mesmo
        if ($funcionario->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $temAtribuicoes = \App\Models\AtribuicaoServico::where(function ($q) use ($funcionario) {
            $q->where('mecanico_id', $funcionario->id)
              ->orWhere('atribuido_por', $funcionario->id);
        })->exists();

        if ($temAtribuicoes) {
            return back()->with('error', 'Não é possível excluir: este usuário possui atribuições vinculadas. Desative a conta em vez de excluir.');
        }

        try {
            $funcionario->delete();
            
            return redirect()->route('funcionarios.index')
                           ->with('success', 'Funcionário excluído com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao excluir funcionário. Verifique se não há registros relacionados.');
        }
    }
}

