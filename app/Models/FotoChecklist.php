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

    /**
     * URL pública da foto (symlink public/storage → storage/app/public).
     * Usa asset() para respeitar subpastas no XAMPP (ex.: .../sistema-oficina/public).
     */
    public function getUrlFotoAttribute(): string
    {
        $raw = (string) ($this->attributes['caminho_foto'] ?? '');
        if ($raw === '') {
            return '';
        }

        $relative = ltrim(str_replace('public/', '', $raw), '/');

        return $relative === '' ? '' : asset('storage/' . $relative);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($foto) {
            $raw = (string) ($foto->caminho_foto ?? '');
            $relative = ltrim(str_replace('public/', '', $raw), '/');
            if ($relative !== '') {
                Storage::disk('public')->delete($relative);
            }
        });
    }
}