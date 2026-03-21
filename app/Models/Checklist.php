<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $table = 'checklists';

    protected $fillable = [
        'ordem_servico_id',
        'ponto_veiculo',
        'observacao',
        'condicao'
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function fotos()
    {
        return $this->hasMany(FotoChecklist::class);
    }

    // Array com os pontos do veículo
    public static function pontosVeiculo()
    {
        return [
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
    }

    // Array com condições
    public static function condicoes()
    {
        return [
            'otimo' => 'Ótimo',
            'bom' => 'Bom',
            'regular' => 'Regular',
            'ruim' => 'Ruim',
            'pessimo' => 'Péssimo'
        ];
    }
}