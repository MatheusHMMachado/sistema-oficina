<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusAtualizacao extends Model
{
    use HasFactory;

    protected $table = 'status_atualizacoes';

    protected $fillable = [
        'ordem_servico_id',
        'user_id',
        'status_anterior',
        'status_novo',
        'mensagem',
        'observacoes',
        'data_atualizacao'
    ];

    protected $casts = [
        'data_atualizacao' => 'datetime'
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
