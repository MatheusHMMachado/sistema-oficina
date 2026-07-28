<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOrdemServico extends Model
{
    use HasFactory;

    protected $table = 'itens_ordem_servico';

    protected $fillable = [
        'ordem_servico_id',
        'servico_peca_id',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'estoque_baixado',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'estoque_baixado' => 'boolean',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function servicoPeca()
    {
        return $this->belongsTo(ServicoPeca::class);
    }

    // Método para calcular valor total do item automaticamente
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->valor_total = $item->quantidade * $item->valor_unitario;
        });

        static::saved(function ($item) {
            $item->ordemServico->calcularValorTotal();
        });
    }
}