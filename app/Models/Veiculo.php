<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';

    protected $fillable = [
        'cliente_id',
        'marca',
        'modelo',
        'cor',
        'ano',
        'placa',
        'chassi',
        'km'
    ];

    public function setPlacaAttribute($value)
    {
        $this->attributes['placa'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

    // Accessor para formatar placa
    public function getPlacaFormatadaAttribute()
    {
        return strtoupper(substr($this->placa, 0, 3) . '-' . substr($this->placa, 3));
    }

    // Accessor para descrição completa do veículo
    public function getDescricaoCompletaAttribute()
    {
        return "{$this->marca} {$this->modelo} {$this->cor} {$this->ano}";
    }
}