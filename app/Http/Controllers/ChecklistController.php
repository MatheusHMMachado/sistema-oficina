<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Checklist;
use App\Models\FotoChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
    public function index()
    {
        $ordens = OrdemServico::with(['cliente', 'veiculo'])
                             ->whereIn('status', ['aberta', 'em_andamento'])
                             ->get();
        
        return view('checklist.index', compact('ordens'));
    }

    public function show(OrdemServico $ordem)
    {
        $ordem->load(['cliente', 'veiculo', 'checklist.fotos']);
        $pontosVeiculo = Checklist::pontosVeiculo();
        $condicoes = Checklist::condicoes();
        
        return view('checklist.show', compact('ordem', 'pontosVeiculo', 'condicoes'));
    }

    public function store(Request $request, OrdemServico $ordem)
    {
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

        // Processar fotos
        if ($request->hasFile('fotos')) {
            $pastaDestino = 'checklist/' . $ordem->numero_ordem;
            
            foreach ($request->file('fotos') as $foto) {
                if ($foto->isValid()) {
                    // Gerar nome único para o arquivo
                    $nomeArquivo = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                    
                    // Salvar no storage/public/checklist/numero_ordem/
                    $caminhoSalvo = $foto->storeAs($pastaDestino, $nomeArquivo, 'public');
                    
                    // Salvar caminho relativo (sem 'public/') no banco
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
}