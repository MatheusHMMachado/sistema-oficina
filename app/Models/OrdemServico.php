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
        'observacoes',
        'valor_total'
    ];

    protected $casts = [
        'data_entrada' => 'date',
        'data_saida' => 'date',
        'valor_total' => 'decimal:2'
    ];

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

    // Método para gerar número da ordem automaticamente
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ordemServico) {
            if (!$ordemServico->numero_ordem) {
                $ultimo = static::latest()->first();
                $proximoNumero = $ultimo ? intval(substr($ultimo->numero_ordem, -6)) + 1 : 1;
                $ordemServico->numero_ordem = date('Y') . str_pad($proximoNumero, 6, '0', STR_PAD_LEFT);
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