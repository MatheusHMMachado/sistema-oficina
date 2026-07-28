<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvaliacaoNps extends Model
{
    protected $table = 'avaliacoes_nps';

    protected $fillable = [
        'cliente_id',
        'ordem_servico_id',
        'nota',
        'comentario',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}
