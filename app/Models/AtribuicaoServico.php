<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtribuicaoServico extends Model
{
    use HasFactory;

    protected $table = 'atribuicoes_servico';

    protected $fillable = [
        'ordem_servico_id',
        'servico_peca_id',
        'mecanico_id',
        'atribuido_por',
        'titulo',
        'descricao',
        'prazo',
        'status',
        'progresso',
        'observacao_mecanico',
        'motivo_nao_conclusao',
        'novo_prazo',
        'concluido_em',
    ];

    protected $casts = [
        'prazo' => 'date',
        'novo_prazo' => 'date',
        'concluido_em' => 'datetime',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function servicoPeca()
    {
        return $this->belongsTo(ServicoPeca::class, 'servico_peca_id');
    }

    public function mecanico()
    {
        return $this->belongsTo(User::class, 'mecanico_id');
    }

    public function atribuidoPor()
    {
        return $this->belongsTo(User::class, 'atribuido_por');
    }
}
