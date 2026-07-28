<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtribuicaoServico extends Model
{
    use HasFactory;

    protected $table = 'atribuicoes_servico';

    public const STATUS = [
        'pendente' => 'Pendente',
        'em_andamento' => 'Em Andamento',
        'paralisado' => 'Paralisado',
        'excedido' => 'Excedido no Prazo',
        'concluido' => 'Concluído',
    ];

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
        'reagendamento_status',
        'alerta_prazo_em',
        'reagendamento_respondido_em',
        'concluido_em',
    ];

    protected $casts = [
        'prazo' => 'datetime',
        'novo_prazo' => 'datetime',
        'concluido_em' => 'datetime',
        'alerta_prazo_em' => 'datetime',
        'reagendamento_respondido_em' => 'datetime',
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

    public function isExcedido(): bool
    {
        return $this->status === 'excedido' || ($this->prazo && $this->prazo->isPast() && $this->status !== 'concluido');
    }

    public function temReagendamentoPendente(): bool
    {
        return $this->reagendamento_status === 'pendente';
    }
}
