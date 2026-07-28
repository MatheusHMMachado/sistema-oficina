<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicoPeca extends Model
{
    use HasFactory;

    protected $table = 'servicos_pecas';

    protected $fillable = [
        'descricao',
        'valor_unitario',
        'tipo',
        'ativo',
        'quantidade_estoque',
        'estoque_minimo',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'ativo' => 'boolean',
        'quantidade_estoque' => 'integer',
        'estoque_minimo' => 'integer',
    ];

    public function itensOrdemServico()
    {
        return $this->hasMany(ItemOrdemServico::class);
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function estoqueBaixo(): bool
    {
        return $this->tipo === 'peca'
            && (int) $this->estoque_minimo > 0
            && (int) $this->quantidade_estoque <= (int) $this->estoque_minimo;
    }

    public function estoqueCritico(): bool
    {
        return $this->tipo === 'peca' && (int) $this->quantidade_estoque <= 0;
    }
}
