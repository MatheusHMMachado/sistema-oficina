<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'link',
        'lida',
        'relacionado_type',
        'relacionado_id',
    ];

    protected $casts = [
        'lida' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relacionado()
    {
        return $this->morphTo();
    }

    public function scopeNaoLidas($query)
    {
        return $query->where('lida', false);
    }
}
