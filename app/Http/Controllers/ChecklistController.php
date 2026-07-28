<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Checklist;
use App\Models\FotoChecklist;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index()
    {
        $ordens = OrdemServico::with(['cliente', 'veiculo'])
            ->visivelPara(auth()->user())
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->get();

        return view('checklist.index', compact('ordens'));
    }

    public function show(OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $ordem->load(['cliente', 'veiculo', 'checklist.fotos']);
        $pontosVeiculo = Checklist::pontosVeiculo();
        $condicoes = Checklist::condicoes();

        return view('checklist.show', compact('ordem', 'pontosVeiculo', 'condicoes'));
    }

    public function store(Request $request, OrdemServico $ordem)
    {
        $this->garantirAcessoOrdem($ordem);

        $request->validate([
            'ponto_veiculo' => 'required|string',
            'condicao' => 'required|string',
            'observacao' => 'nullable|string',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,jpeg|max:5120'
        ], [
            'fotos.*.image' => 'O arquivo deve ser uma imagem válida.',
            'fotos.*.mimes' => 'A imagem deve ser do tipo: jpeg, png ou jpg.',
            'fotos.*.max' => 'A imagem não pode ter mais de 5MB.'
        ]);

        $checklist = Checklist::updateOrCreate(
            [
                'ordem_servico_id' => $ordem->id,
                'ponto_veiculo' => $request->ponto_veiculo
            ],
            [
                'condicao' => $request->condicao,
                'observacao' => $request->observacao
            ]
        );

        if ($request->hasFile('fotos')) {
            $pastaDestino = 'checklist/' . $ordem->numero_ordem;

            foreach ($request->file('fotos') as $foto) {
                if ($foto->isValid()) {
                    $nomeArquivo = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                    $caminhoSalvo = $foto->storeAs($pastaDestino, $nomeArquivo, 'public');
                    $caminhoRelativo = str_replace('public/', '', $caminhoSalvo);

                    FotoChecklist::create([
                        'checklist_id' => $checklist->id,
                        'caminho_foto' => $caminhoRelativo,
                        'descricao' => $request->ponto_veiculo
                    ]);
                }
            }
        }

        return back()->with('success', 'Checklist atualizado com sucesso!');
    }

    public function destroy(Checklist $checklist)
    {
        $ordem = $checklist->ordemServico;
        if ($ordem) {
            $this->garantirAcessoOrdem($ordem);
        } elseif (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $checklist->load('fotos');
        foreach ($checklist->fotos as $foto) {
            $foto->delete(); // dispara remoção do arquivo no model
        }
        $checklist->delete();

        return back()->with('success', 'Item do checklist removido com sucesso.');
    }

    public function destroyFoto(FotoChecklist $foto)
    {
        $checklist = $foto->checklist;
        $ordem = $checklist?->ordemServico;
        if ($ordem) {
            $this->garantirAcessoOrdem($ordem);
        } elseif (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $foto->delete();

        return back()->with('success', 'Foto removida com sucesso.');
    }

    private function garantirAcessoOrdem(OrdemServico $ordem): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if (! $ordem->atribuidaAoMecanico($user->id)) {
            abort(403, 'Você só pode acessar checklists de ordens atribuídas a você.');
        }
    }
}
