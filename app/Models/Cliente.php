<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'endereco',
        'telefone',
        'celular',
        'email',
        'senha_portal',
        'acesso_portal',
        'token_acesso',
        'ultimo_acesso',
        'notificar_email',
        'notificar_whatsapp',
    ];

    protected $casts = [
        'acesso_portal' => 'boolean',
        'notificar_email' => 'boolean',
        'notificar_whatsapp' => 'boolean',
        'ultimo_acesso' => 'datetime',
    ];

    /**
     * Salva o CPF sempre apenas com números (11 dígitos) para evitar falha na consulta do portal.
     */
    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/[^0-9]/', '', $value) ?: $value;
    }

    /**
     * Busca cliente por CPF aceitando entrada com ou sem formatação;
     * encontra tanto CPF salvo só com números quanto com pontos e traço.
     */
    public static function buscarPorCpf(string $cpf): ?self
    {
        $digits = preg_replace('/[^0-9]/', '', $cpf);
        $cliente = self::where('cpf', $digits)->first();
        if ($cliente) {
            return $cliente;
        }
        if (strlen($digits) === 11) {
            $formatted = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
            return self::where('cpf', $formatted)->first();
        }
        return null;
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

    /** Formata CPF para exibição (funciona com CPF salvo com ou sem formatação). */
    public function getCpfFormatadoAttribute()
    {
        $digits = preg_replace('/[^0-9]/', '', $this->attributes['cpf'] ?? '');
        return strlen($digits) === 11
            ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits)
            : ($this->attributes['cpf'] ?? '');
    }
}