<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    use HasFactory;

    public const CATEGORIAS = [
        'contas_a_pagar' => 'Contas a Pagar',
        'salario_mecanicos' => 'Salário de Mecânicos',
        'impostos_taxas' => 'Impostos / Taxas',
        'prejuizos_retrabalhos' => 'Prejuízos / Retrabalhos',
    ];

    protected $fillable = [
        'descricao',
        'valor',
        'categoria',
        'data_despesa',
        'user_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_despesa' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCategoriaLabelAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? $this->categoria;
    }
}
