<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'numero_ordem',
        'cliente_id',
        'veiculo_id',
        'data_entrada',
        'data_saida',
        'km_entrada',
        'status',
        'status_pagamento',
        'observacoes',
        'valor_total'
    ];

    protected $casts = [
        'data_entrada' => 'date',
        'data_saida' => 'date',
        'valor_total' => 'decimal:2'
    ];

    public function isPago(): bool
    {
        return $this->status_pagamento === 'pago';
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemOrdemServico::class);
    }

    public function checklist()
    {
        return $this->hasMany(Checklist::class);
    }

    public function statusAtualizacoes()
    {
        return $this->hasMany(StatusAtualizacao::class)->orderBy('data_atualizacao', 'desc');
    }

    public function atribuicoesServico()
    {
        return $this->hasMany(AtribuicaoServico::class, 'ordem_servico_id')->latest();
    }

    public function avaliacaoNps()
    {
        return $this->hasOne(AvaliacaoNps::class, 'ordem_servico_id');
    }

    /**
     * Etapa amigável para o portal do cliente.
     */
    public function getEtapaClienteAttribute(): string
    {
        return match ($this->status) {
            'aberta' => 'Na Recepção / Em Diagnóstico',
            'em_andamento' => 'Em Execução',
            'concluida' => 'Pronto para Retirada',
            'cancelada' => 'Cancelada',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function etapasPipeline(): array
    {
        $mapa = [
            'aberta' => 1,
            'em_andamento' => 3,
            'concluida' => 5,
            'cancelada' => 0,
        ];
        $atual = $mapa[$this->status] ?? 1;

        return [
            ['key' => 1, 'label' => 'Na Recepção', 'done' => $atual >= 1],
            ['key' => 2, 'label' => 'Em Diagnóstico', 'done' => $atual >= 2 || $this->status === 'em_andamento' || $this->status === 'concluida'],
            ['key' => 3, 'label' => 'Aguardando Peças / Em Execução', 'done' => $atual >= 3],
            ['key' => 4, 'label' => 'Em Execução', 'done' => $atual >= 3],
            ['key' => 5, 'label' => 'Pronto para Retirada', 'done' => $atual >= 5],
        ];
    }

    /**
     * Ordens com ao menos uma atribuição para o mecânico informado.
     */
    public function scopeAtribuidasAo($query, int $userId)
    {
        return $query->whereHas('atribuicoesServico', function ($q) use ($userId) {
            $q->where('mecanico_id', $userId);
        });
    }

    public function atribuidaAoMecanico(int $userId): bool
    {
        return $this->atribuicoesServico()->where('mecanico_id', $userId)->exists();
    }

    /**
     * Admin vê tudo; mecânico só o que foi atribuído a ele.
     */
    public function scopeVisivelPara($query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->atribuidasAo($user->id);
    }

    // Método para gerar número da ordem automaticamente
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ordemServico) {
            if (!$ordemServico->numero_ordem) {
                $ultimoNumero = static::query()
                    ->where('numero_ordem', 'like', date('Y') . '%')
                    ->orderByDesc('numero_ordem')
                    ->value('numero_ordem');

                $proximoNumero = $ultimoNumero ? ((int) substr($ultimoNumero, -6)) + 1 : 1;
                $ordemServico->numero_ordem = date('Y') . str_pad((string) $proximoNumero, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    // Método para calcular valor total
    public function calcularValorTotal()
    {
        $this->valor_total = $this->itens->sum('valor_total');
        $this->save();
        return $this->valor_total;
    }

    // Scope para filtrar por status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}