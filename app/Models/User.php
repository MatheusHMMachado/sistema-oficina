<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'ativo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ativo' => 'boolean',
        'ultimo_acesso' => 'datetime',
    ];

    public function atribuicoesComoMecanico()
    {
        return $this->hasMany(AtribuicaoServico::class, 'mecanico_id');
    }

    public function atribuicoesComoAdmin()
    {
        return $this->hasMany(AtribuicaoServico::class, 'atribuido_por');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->id === 1;
    }

    public function isMecanico(): bool
    {
        return ! $this->isAdmin();
    }
}
