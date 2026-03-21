<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FotoChecklist extends Model
{
    use HasFactory;

    protected $table = 'fotos_checklist';

    protected $fillable = [
        'checklist_id',
        'caminho_foto',
        'descricao'
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    // Accessor para URL da foto
    public function getUrlFotoAttribute()
    {
        // Se o caminho já começa com 'public/', remover
        $caminho = str_replace('public/', '', $this->caminho_foto);
        
        // Retornar URL usando Storage::url que automaticamente adiciona 'storage/'
        return Storage::url($caminho);
    }

    // Método para deletar foto do storage ao deletar o registro
    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function ($foto) {
            Storage::delete($foto->caminho_foto);
        });
    }
}