<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    protected $fillable = [
        'cliente_id',
        'nome_contato',
        'cpf_contato',
        'telefone_contato',
        'email_contato',
        'veiculo_id',
        'ordem_servico_id',
        'veiculo_info',
        'placa_veiculo',
        'marca_veiculo',
        'modelo_veiculo',
        'ano_veiculo',
        'cor_veiculo',
        'km_veiculo',
        'chassi_veiculo',
        'eh_visitante',
        'servico',
        'data_agendada',
        'hora',
        'observacoes',
        'status',
        'resposta_admin',
        'respondido_por',
        'respondido_em',
    ];

    protected $casts = [
        'data_agendada' => 'date',
        'respondido_em' => 'datetime',
        'eh_visitante' => 'boolean',
    ];

    public const STATUS = [
        'pendente' => 'Pendente',
        'aprovado' => 'Aprovado',
        'recusado' => 'Recusado',
        'concluido' => 'Concluído',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function respondidoPor()
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    public function nomeExibicao(): string
    {
        return $this->cliente->nome ?? $this->nome_contato ?? 'Visitante';
    }

    public function veiculoExibicao(): string
    {
        if ($this->veiculo) {
            return $this->veiculo->placa_formatada . ' — ' . $this->veiculo->marca . ' ' . $this->veiculo->modelo;
        }

        if ($this->placa_veiculo || $this->marca_veiculo || $this->modelo_veiculo) {
            $placa = $this->placa_veiculo
                ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $this->placa_veiculo), 0, 3)
                    . '-' . substr(preg_replace('/[^A-Za-z0-9]/', '', $this->placa_veiculo), 3))
                : 'Sem placa';

            $desc = trim(($this->marca_veiculo ?? '') . ' ' . ($this->modelo_veiculo ?? ''));
            $extra = collect([
                $this->ano_veiculo,
                $this->cor_veiculo,
                $this->km_veiculo ? number_format($this->km_veiculo, 0, ',', '.') . ' km' : null,
            ])->filter()->implode(' · ');

            return trim($placa . ($desc ? " — {$desc}" : '') . ($extra ? " ({$extra})" : ''));
        }

        return $this->veiculo_info ?: 'Veículo não informado';
    }
}
