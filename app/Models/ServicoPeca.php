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
        'ativo'
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'ativo' => 'boolean'
    ];

    public function itensOrdemServico()
    {
        return $this->hasMany(ItemOrdemServico::class);
    }

    // Scope para filtrar apenas ativos
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    // Scope para filtrar por tipo
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}